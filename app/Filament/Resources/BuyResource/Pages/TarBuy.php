<?php

namespace App\Filament\Resources\BuyResource\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\BuyResource;
use App\Livewire\Traits\Raseed;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Item;

use App\Models\Setting;
use App\Models\Tar_buy;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TarBuy extends Page implements HasTable
{
    protected static string $resource = BuyResource::class;

    protected string $view = 'filament.resources.buy-resource.pages.tar-buy';
  use InteractsWithRecord,InteractsWithTable;
  use Raseed;
  protected ?string $heading='';

  public $buy;
  public $buytran;
  public $tarbuyData;
  public $tarbuy;
  public $buy_id;
  public $max=1;

  public function mount(int | string $record): void
  {
    $this->record = $this->resolveRecord($record);

    $this->buy_id = $this->record->id;
    $this->buy = Buy::find($this->buy_id);

    $this->tarbuyForm->fill([
      'buy_id' => $this->record->id,
      'name' => $this->record->Supplier->name,
      'order_date' => $this->record->order_date,
      'tot' => $this->record->tot,
      'tar_date' => now(),
      'q1' => 1
    ]);
  }
    protected function getForms(): array
  {
    return array_merge(parent::getForms(), [
      "tarbuyForm" => $this->makeForm()
        ->model(Tar_buy::class)
        ->components($this->getTarbuyFormSchema())
        ->statePath('tarbuyData'),
    ]);
  }

    public function ChkItem(){
    $this->dispatch('gotoitem', test: 'q1');
  }

  protected function getTarbuyFormSchema(): array
  {
    return [

      Section::make()
        ->schema([
          TextInput::make('buy_id')
            ->hiddenLabel()
            ->prefix('رقم الفاتورة')
            ->columnSpan(2)
            ->disabled(),
          DatePicker::make('tar_date')
            ->hiddenLabel()
            ->prefix('التاريخ')
            ->autofocus()
            ->columnSpan(2)
            ->required(),
          TextInput::make('name')
            ->hiddenLabel()
            ->prefix('الزبون')
            ->columnSpan(4)
            ->disabled(),
          TextInput::make('order_date')
            ->hiddenLabel()
            ->prefix('تاريخ الفاتورة')
            ->columnSpan(2)
            ->disabled(),
          TextInput::make('tot')
            ->hiddenLabel()
            ->prefix('اجمالي الفاتورة')
            ->columnSpan(2)
            ->disabled(),
          Select::make('item_id')
            ->options(Item::wherein('id',Buy_tran::
            where('buy_id',$this->buy_id)
              ->where('tar_buy_id',null)
              ->select('item_id'))->pluck('name','id'))
            ->hiddenLabel()
            ->prefix('الصنف')
            ->prefixIcon('heroicon-m-user')
            ->prefixIconColor('info')
            ->live()
            ->afterStateUpdated(function ($state,Set $set){
              $res=Buy_tran::where('buy_id',$this->buy_id)->where('item_id',$state)->first();
              $this->max=$res->qs1;
              $set('p1',$res->price_input);
              $this->buytran=Buy_tran::where('buy_id',$this->record->id)->where('item_id',$state)->first();
            })
            ->extraAttributes([
              'wire:change' => "ChkItem",
              'wire:keydown.enter' => "ChkItem",
            ])
            ->columnSpan(4),
          TextInput::make('q1')
            ->hiddenLabel()
            ->readOnly(function (Get $get){return !$get('item_id');})
            ->prefix('الكمية')
            ->live()
            ->minValue(1)
            ->maxValue(function (){return $this->max;})
            ->afterStateUpdated(function ($state,Set $set){
              if ($state > $this->max) $set('q1',$this->max);

            })
            ->columnSpan(2)
            ->numeric()
            ->required()
            ->id('q1'),
          Hidden::make('p1'),
          Actions::make([
            Action::make('store')
              ->label('تخزين')
              ->icon('heroicon-m-plus')
              ->button()
              ->visible(function (Get $get){return $get('item_id') && $get('q1') && $get('q1')>0;})
              ->color('success')
              ->requiresConfirmation()
              ->action(function () {

                $tar=Tar_buy::create(['buy_id'=>$this->record->id,'tar_date'=>$this->tarbuyData['tar_date'],
                  'item_id'=>$this->tarbuyData['item_id'],'q1'=>$this->tarbuyData['q1'],'p1'=>$this->tarbuyData['p1'],
                  'sub_tot'=>$this->tarbuyData['q1']*$this->tarbuyData['p1'],
                  'user_id'=>Auth::id(),
                ]);
                $this->decAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1);

                $this->buytran->q1-=$this->tarbuyData['q1'];
                $this->buytran->qs1-=$this->tarbuyData['q1'];
                $this->buytran->tar_buy_id=$tar->id;
               // $this->buytran->sub_input-=$tar->sub_tot;
                $this->buytran->save();
                $this->incAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1
                  ,$this->buy->price_type_id,$this->buytran->price_input);


                //$tot = Buy_tran::where('buy_id', $this->buy_id)->sum('sub_input');
                //$this->buy->tot=$tot;
                //$this->buy->baky=$this->buy->tot-$this->buy->pay;
                //$this->buy->save();

                $this->tarbuyForm->fill(['buy_id'=>$this->buy->id,'tar_date'=>now(),'q1'=>1,'item_id'=>null]);
                $this->resetTable();

              }),
          ])->extraAttributes(['class' => 'items-center justify-between']),

        ])->columns(4),


    ];
  }

  public function table(Table $table):Table
  {
    return $table
      ->query(function (Buy_tran $buy_tran)  {
        $buy_tran=Buy_tran::where('buy_id',$this->buy_id);

        return  $buy_tran;
      })
      ->columns([
        TextColumn::make('item_id')
          ->label('رقم الصنف')
          ->sortable(),
        TextColumn::make('barcode_id')
          ->label('الباركود')
          ->sortable(),
        TextColumn::make('Item.name')
          ->label('اسم الصنف')
          ->description(function (Buy_tran $record){
            if ($record->tar_buy_id) return ' كمية مرجعة  ('.$record->Tar_buy->q1.') بتاريخ '.$record->Tar_buy->tar_date;
          })
          ->color(function(Buy_tran $record){
            if ($record->tar_buy_id) return 'primary'; else return 'info';
          })
          ->sortable(),
        TextColumn::make('q1')
          ->label('الكمية'),
        TextColumn::make('qs1')
          ->label('الكمية المتوفرة'),

        TextColumn::make('q2')
          ->label('صغري')
          ->visible(Setting::find(Auth::user()->company)->has_two)
          ->formatStateUsing(function (string $state) {
            if ($state=='0') return '';
            return $state;
          }),
        TextColumn::make('price_input')
          ->label('سعر البيع'),
      ])

      ->recordActions([
        Action::make('del_tar')
          ->visible(function (Buy_tran $record){
            return  $record->tar_buy_id;
          })
          ->icon('heroicon-m-trash')
          ->iconButton()->color('primary')
          ->requiresConfirmation()
          ->action(function (Buy_tran $record){

            $this->buytran=Buy_tran::find($record->id)  ;
            $this->tarbuy=Tar_buy::find($this->buytran->Tar_buy->id);
            $this->decAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1);
            $this->buytran->q1+=$this->tarbuy->q1;
            $this->buytran->qs1+=$this->tarbuy->q1;
            $this->buytran->tar_buy_id=null;
            $this->buytran->sub_input+=$this->tarbuy->sub_tot;
            $this->buytran->save();
            $this->incAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1
              ,$this->buy->price_type_id,$this->buytran->price_input);

            $tot = Buy_tran::where('buy_id', $this->buy_id)->sum('sub_input');
            $this->buy->tot=$tot;
            $this->buy->baky=$this->buy->tot-$this->buy->pay;
            $this->buy->save();

            $this->tarbuy->delete();

            $this->tarbuyForm->fill(['buy_id'=>$this->buy_id,'tar_date'=>now(),'q1'=>1,'item_id'=>null]);
            $this->resetTable();
          }),
      ])

      ->striped();
  }



}
