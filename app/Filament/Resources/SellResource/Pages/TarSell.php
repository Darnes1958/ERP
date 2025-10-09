<?php

namespace App\Filament\Resources\SellResource\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use App\Filament\Resources\SellResource;
use App\Livewire\Traits\Raseed;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use App\Models\Tar_sell;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TarSell extends Page implements HasTable
{

    protected static string $resource = SellResource::class;
    use InteractsWithRecord,InteractsWithTable;
    use Raseed;
    protected string $view = 'filament.resources.sell-resource.pages.tar-sell';
    protected ?string $heading='';

    public $sell;
    public $selltran;
    public $tarsellData;
    public $tarsell;
    public $sell_id;
    public $max=1;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->sell_id=$this->record->id;
        $this->sell=Sell::find($this->sell_id);

        $this->tarsellForm->fill([
          'sell_id'=>$this->record->id,
          'name'=>$this->record->Customer->name,
          'order_date'=>$this->record->order_date,
          'total'=>$this->record->total,
          'tar_date'=>now(),
          'q1'=>1
        ]);



    }
    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "tarsellForm" => $this->makeForm()
                ->model(Tar_sell::class)
                ->components($this->getTarsellFormSchema())
                ->statePath('tarsellData'),
        ]);
    }

    public function ChkItem(){


        $this->dispatch('gotoitem', test: 'q1');

    }
    protected function getTarsellFormSchema(): array
    {
        return [

            Section::make()
                ->schema([
                    TextInput::make('sell_id')
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
                  TextInput::make('total')
                    ->hiddenLabel()
                    ->prefix('اجمالي الفاتورة')
                    ->columnSpan(2)
                    ->disabled(),
                    Select::make('item_id')
                        ->options(Item::wherein('id',Sell_tran::
                            where('sell_id',$this->sell_id)
                          ->where('tar_sell_id',null)
                          ->select('item_id'))->pluck('name','id'))
                        ->hiddenLabel()
                        ->prefix('الصنف')
                        ->prefixIcon('heroicon-m-user')
                        ->prefixIconColor('info')
                        ->live()
                        ->afterStateUpdated(function ($state,Set $set){
                            $res=Sell_tran::where('sell_id',$this->sell_id)->where('item_id',$state)->first();
                            $this->max=$res->q1;
                            $set('p1',$res->price1);
                            $this->selltran=Sell_tran::where('sell_id',$this->record->id)->where('item_id',$state)->first();
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

                               $tar=Tar_sell::create(['sell_id'=>$this->record->id,'tar_date'=>$this->tarsellData['tar_date'],
                                                 'item_id'=>$this->tarsellData['item_id'],'q1'=>$this->tarsellData['q1'],'p1'=>$this->tarsellData['p1'],
                                                 'sub_tot'=>$this->tarsellData['q1']*$this->tarsellData['p1'],
                                                 'user_id'=>Auth::id(),
                               ]);

                               $this->incAll($this->sell_id,$this->tarsellData['item_id'],$this->record->place_id,$this->selltran->q1,$this->selltran->q2);
                               $this->selltran->q1-=$this->tarsellData['q1'];
                               $this->selltran->tar_sell_id=$tar->id;
                               //$this->selltran->sub_tot-=$tar->sub_tot;
                               $this->selltran->save();
                               $this->decAll($this->selltran->id,$this->sell_id,$this->selltran->item_id,
                                   $this->sell->place_id,$this->selltran->q1,$this->selltran->q2);

                               //$tot = Sell_tran::where('sell_id', $this->sell_id)->sum('sub_tot');
                               //$this->sell->tot=$tot;
                               //$this->sell->differ=($this->sell->tot+$this->sell->cost)*$this->sell->rate/100;
                               //$this->sell->total=$tot+$this->sell->differ+$this->sell->cost;
                               //$this->sell->baky=$this->sell->total-$this->sell->pay;
                               //$this->sell->save();

                                $this->tarsellForm->fill(['sell_id'=>$this->record->id,'tar_date'=>now(),'q1'=>1,'item_id'=>null]);
                                $this->resetTable();



                            }),
                    ])->extraAttributes(['class' => 'items-center justify-between']),

                    ])->columns(4),


                ];
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function (Sell_tran $sell_tran)  {
                $sell_tran=Sell_tran::where('sell_id',$this->sell_id);

                return  $sell_tran;
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
                    ->description(function (Sell_tran $record){
                       if ($record->tar_sell_id) return ' كمية مرجعة  ('.$record->Tar_sell->q1.') بتاريخ '.$record->Tar_sell->tar_date;
                        })
                    ->color(function(Sell_tran $record){
                      if ($record->tar_sell_id) return 'primary'; else return 'info';
                     })
                    ->sortable(),
                TextColumn::make('q1')
                    ->label('الكمية'),

                TextColumn::make('q2')
                    ->label('صغري')
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->formatStateUsing(function (string $state) {
                        if ($state=='0') return '';
                        return $state;
                    }),
                TextColumn::make('price1')
                    ->label('سعر البيع'),

                TextColumn::make('price2')
                    ->label('سعر الصغري')
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->formatStateUsing(function (string $state) {
                        if ($state=='0.0') return '';
                        return $state;
                    }),
            ])

            ->recordActions([
              Action::make('del_tar')
              ->visible(function (Sell_tran $record){
                return  $record->tar_sell_id;
              })
                ->icon('heroicon-m-trash')
                ->iconButton()->color('primary')
                ->requiresConfirmation()
               ->action(function (Sell_tran $record){

                  $this->selltran=Sell_tran::find($record->id)  ;
                  $this->tarsell=Tar_sell::find($this->selltran->Tar_sell->id);
                 $this->incAll($this->sell_id,$this->selltran->item_id,$this->sell->place_id,$this->selltran->q1,
                   $this->selltran->q2);
                 $this->selltran->q1+=$this->tarsell->q1;
                 $this->selltran->tar_sell_id=null;
                 $this->selltran->sub_tot+=$this->tarsell->sub_tot;
                 $this->selltran->save();
                 $this->decAll($this->selltran->id,$this->sell_id,$this->selltran->item_id,
                   $this->sell->place_id,$this->selltran->q1,$this->selltran->q2);

                 $tot = Sell_tran::where('sell_id', $this->sell_id)->sum('sub_tot');
                 $this->sell->tot=$tot;
                 $this->sell->differ=($this->sell->tot+$this->sell->cost)*$this->sell->rate/100;
                 $this->sell->total=$tot+$this->sell->differ+$this->sell->cost;
                 $this->sell->baky=$this->sell->total-$this->sell->pay;
                 $this->sell->save();

                 $this->tarsell->delete();

                 $this->tarsellForm->fill(['sell_id'=>$this->sell_id,'tar_date'=>now(),'q1'=>1,'item_id'=>null]);
                 $this->resetTable();


               }),
            ])

            ->striped();
    }
}
