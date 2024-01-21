<?php

namespace App\Filament\Pages;

use App\Livewire\Forms\SellForm;
use App\Livewire\Forms\SellTranForm;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellDaily extends Page implements HasForms,HasTable,HasActions, HasInfolists
{
  use InteractsWithForms, InteractsWithTable, InteractsWithActions,InteractsWithInfolists;
  use Raseed;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static string $view = 'filament.pages.sell-daily';
  protected static ?string $navigationLabel='مبيعات يومية';
  protected static ?int $navigationSort=1;
  protected ?string $heading="";

  public ?array $selltranData = [];

  public $sell_id;

  public $showInfo = false;

  public $has_two;
  public $is_filled=false;
  public $show_q2=false;
  public $is_q2=false;
  public SellForm $sellForm;
  public SellTranForm $sellTranForm;

  public function LeftQ(){$this->is_q2=true;$this->show_q2=true;}
  public function RightQ(){$this->is_q2=false;}
  public function is_two(){

    if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {

      return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
    else return false;
  }


  public function chkQuant(){
    if ($this->is_two())
      $this->dispatch('goto', test: 'q2');
    else $this->add_rec();
  }

  public function itemFill($item,$barcode,$stock1){
    $this->sellTranForm->item_id=$item;
    $this->sellTranForm->barcode_id=$barcode;
    $rec=$this->retPrice($item,$this->sellForm->single,$this->sellForm->price_type_id);
    $this->sellTranForm->price1=$rec['price1'];
    $this->sellTranForm->price2=$rec['price2'];
    $this->sellTranForm->sub_tot = ($this->sellTranForm->q1*$this->sellTranForm->price1)+($this->sellTranForm->q2*$this->sellTranForm->price2);
    $this->sellTranForm->user_id = Auth::id();
  }
  public function ChkBarcode(){

    if ($this->selltranData['barcode_id']==null) return;
    $bar=$this->selltranData['barcode_id'];
    if ($bar<100) {
        if ($this->is_q2) $this->sellTranForm->q2 = $bar;
        else $this->sellTranForm->q1 = $bar;

        $this->form->fill($this->sellTranForm->toArray());
    } else {
        $res = Barcode::with('Item')->find($bar);
        if (!$res)
            Notification::make()
                ->title('هذا الباركود غير مخزون ')
                ->icon('heroicon-o-check')
                ->iconColor('success')
                ->send();
        else {
            $this->itemFill($res->item_id, $res->id, $res->item->stock1);
            $this->add_rec();
        }
    }
  }
  public function ChkItem($state){
    $res=Item::find($state);
    $this->itemFill($res->id,$res->barcode,$res->stock1);
    $this->dispatch('goto', test: 'q1');

  }
  public function add_rec()
  {
   //   $this->sellTranForm->loadForm($this->sell_id, $this->selltranData);

      $chk=$this->sellTranForm->chkData($this->sellForm->place_id);
      if ($chk != 'ok') {
          Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
          return;
      }
      $this->sellTranForm->SetQuant();
      $res = Sell_tran_work::where('sell_id', $this->sell_id)->where('item_id', $this->sellTranForm->item_id)->get();
      if ($res->count() > 0)
          Sell_tran_work::where('sell_id', $this->sell_id)->where('item_id', $this->sellTranForm->item_id)->update($this->sellTranForm->all());
      else  Sell_tran_work::create($this->sellTranForm->all());
      $this->sellTranForm->reset();
      $this->sellTranForm->sell_id=$this->sell_id;
      $this->sellTranForm->q1=1;
      $this->form->fill($this->sellTranForm->toArray());
      $tot = Sell_tran_work::where('sell_id', $this->sell_id)->sum('sub_tot');
      $baky = $tot - Sell_work::find($this->sell_id)->pay;
      Sell_work::find($this->sell_id)->update(['tot' => $tot,'baky' => $baky,]);
      $this->sellForm->tot=$tot;
      $this->sellForm->baky=$baky;

      $this->is_filled=true;
      $this->is_q2=false;
      $this->show_q2=false;
      $this->dispatch('goto', test: 'barcode_id');
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([

            TextInput::make('barcode_id')
              ->label('الباركود')
              ->autocomplete(false)
              ->columnSpan(2)
              ->required()
              ->inlineLabel()
              ->autofocus()
              ->live(onBlur: true)
              ->extraAttributes([
                'wire:keydown.enter' => "ChkBarcode",
                'wire:change' => "ChkBarcode",
                'wire:keydown.arrow-left' => "LeftQ",
                'wire:keydown.arrow-right' => "RightQ",

              ])
              ->id('barcode_id'),
            Select::make('item_id')
              ->label('الصنف')
              ->columnSpan(2)
              ->searchable()
              ->preload()
              ->relationship('Item','name')
              ->inlineLabel()
              ->live()
              ->required()
              ->extraAttributes([
                'wire:change' => "ChkItem(state)",
                'wire:keydown..enter' => "ChkItem(state)",
              ])
              ->id('item_id'),
            TextInput::make('raseed_all')
              ->hiddenLabel()
              ->prefix('الرصيد الكلي')
              ->disabled(),
            TextInput::make('raseed_place')
              ->hiddenLabel()
              ->prefix('رصيد المكان')
              ->disabled(),
            TextInput::make('price1')
              ->label('السعر')
              ->inlineLabel()
              ->numeric()
              ->live()
              ->required()
              ->id('price1')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
              ]),
            TextInput::make('price2')
              ->hiddenLabel()
              ->numeric()
              ->live()
              ->required()
              ->id('price2')
              ->visible(function (){
                return($this->is_two() || $this->show_q2);
              })
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
              ]),

            TextInput::make('q1')
              ->label('الكمية')
              ->inlineLabel()
              ->numeric()
              ->required()
              ->afterStateUpdated(function (Set $set,Get $get){
                if ($get('q2')==null) $set('q2',0);
                if ($get('q1')==null) $set('q1',0);

              })
              ->extraAttributes(['wire:keydown.enter' => "chkQuant",])
              ->id('q1'),
            TextInput::make('q2')
              ->hiddenLabel()
              ->numeric()
              ->required()
              ->afterStateUpdated(function (Set $set,Get $get){
                if ($get('q2')==null) $set('q2',0);
                if ($get('q1')==null) $set('q1',0);
              })

              ->visible(function (){
                return ($this->is_two() || $this->show_q2);
              })
              ->extraAttributes([
                'wire:keydown.enter' => "add_rec",
              ])
              ->id('q2'),
          ])->columns(2),
        Section::make()
            ->schema([
                \Filament\Forms\Components\Actions::make([
                    \Filament\Forms\Components\Actions\Action::make('تخزين')
                        ->icon('heroicon-m-plus')
                        ->button()
                        ->visible(function () {return Sell_tran_work::where('sell_id',$this->sell_id)->count()>0;})
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function () {
                            $sell=Sell_work::find($this->sell_id);
                            $selltran=Sell_tran_work::with('Item')->where('sell_id',$this->sell_id)->get();
                            if ($selltran->count()==0)
                                Notification::make()
                                    ->title('لم يتم ادخال اصناف بعد !! ')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->iconColor('warning')
                                    ->send();
                            $minus=false;
                            foreach ($selltran as $tran) {
                                $placeRaseed=$this->retRaseedPlace($tran->item_id,$this->sellForm->place_id);
                                if (
                                    $this->TwoToOne($tran->item->count,$tran->q1,$tran->q2) >
                                    $this->TwoToOne($tran->item->count,$placeRaseed['q1'],$placeRaseed['q2'])
                                ){
                                    Notification::make()
                                        ->title('الصنف : ('.$tran->item->name.') رصيده لا يسمح !!')
                                        ->icon('heroicon-o-exclamation-triangle')
                                        ->iconColor('warning')
                                        ->send();
                                    $minus=true;
                                    break;

                                }
                            }
                            if ($minus) return;



                            DB::connection(Auth()->user()->company)->beginTransaction();
                            try {
                                $Sell=Sell::create($this->sellForm->except('id'));
                                $this->sellForm->id=$Sell->id;
                                foreach ($selltran as $item) {
                                    $this->sellTranForm->copyToSave($this->sellForm->id, $item);
                                    $sell_tran_id=Sell_tran::create($this->sellTranForm->all());
                                    $this->sellTranForm->DoDecALl($this->sellForm->place_id,$sell_tran_id->id);
                                }
                                $this->sell_id=Auth::id();
                                $this->sellForm->id=$this->sell_id;

                                Sell_tran_work::where('sell_id',$this->sell_id)->delete();
                                $this->is_filled=false;
                                $sell->tot=0;  $sell->pay=0; $sell->baky=0;  $sell->save();

                                $this->sellTranForm->reset();
                                $this->sellTranForm->sell_id=$this->sell_id;
                                $this->sellTranForm->q1=1;

                                DB::connection(Auth()->user()->company)->commit();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('حدث خطأ !!')
                                    ->color('danger')
                                    ->icon('heroicon-o-x-circle')
                                    ->danger()
                                    ->send();
                                info($e);
                                DB::connection(Auth()->user()->company)->rollback();
                            }
                        }),
                    \Filament\Forms\Components\Actions\Action::make('مسح')
                        ->icon('heroicon-m-trash')
                        ->button()
                        ->color('danger')

                        ->action(function () {
                            Sell_tran_work::where('sell_id',$this->sell_id)->delete();
                            Sell_work::find($this->sell_id)->update(['tot'=>0,'pay'=>0,'baky'=>0,]);
                            $this->is_filled=false;
                            $this->sellForm->fillForm($this->sell_id);
                            $this->sellTranForm->reset();
                            $this->sellTranForm->q1=1;
                            $this->sellTranForm->sell_id=$this->sell_id;
                            $this->form->fill($this->sellTranForm->toArray());
                            $this->dispatch('goto',  test: 'barcode_id' );
                        })
                ])->extraAttributes(['class' => 'items-center justify-between']),

            ])

      ])
      ->statePath('selltranData')
      ->model(Sell_tran::class);
  }
  public function table(Table $table):Table
  {
    return $table
      ->query(function (Sell_tran_work $sell_tran)  {
        $sell_tran=Sell_tran_work::where('sell_id',$this->sell_id);

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
          ->color('info')
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
        TextColumn::make('sub_tot')
          ->label('المجموع'),

      ])

      ->actions([
          \Filament\Tables\Actions\Action::make('delete')
              ->action(function (Sell_tran_work $record){
                  $record->delete();
                  $this->is_q2=false;
                  $this->show_q2=false;
                  $tot = Sell_tran_work::where('sell_id', $this->sell_id)->sum('sub_tot');
                  $baky = $tot - Sell_work::find($this->sell_id)->pay;
                  Sell_work::find($this->sell_id)->update(['tot' => $tot,'baky' => $baky,]);
                  $this->sellForm->tot=$tot;
                  $this->sellForm->baky=$baky;
                  $this->sellTranForm->reset();
                  $this->sellTranForm->q1=1;
                  $this->sellTranForm->sell_id=$this->sell_id;
                  $this->form->fill($this->sellTranForm->toArray());

                  $this->is_filled=Sell_tran_work::where('sell_id',$this->sell_id)->count()>0;
                  $this->dispatch('goto',  test: 'barcode_id' );
              })
              ->icon('heroicon-m-trash')
              ->iconButton()->color('danger')
              ->hiddenLabel(),

          \Filament\Tables\Actions\Action::make('edit')
              ->action(function (Sell_tran_work $record){
                  $this->sellTranForm->loadForm($this->sell_id,$record);
                  $this->form->fill($record->toArray());
                  $this->is_q2=false;
                  $this->show_q2=false;
                  $this->dispatch('goto',  test: 'q1' );
              })
              ->icon('heroicon-m-pencil')
              ->iconButton()->color('info')
              ->hiddenLabel()
      ])

      ->striped();
  }

  public function mount(){
    $this->has_two=Setting::find(Auth::user()->company)->has_two;
    $res=Sell_work::where('user_id',Auth::id())
      ->whereDate('created_at', '=', date('Y-m-d'))->first();
    if ($res && Sell_work::find(Auth::id())->customer_id==1){
      $this->sellForm->fillForm($res->id);
      $this->sell_id=$res->id;
    }
    else {
      Sell_tran_work::where('sell_id',Auth::id())->delete();
      Sell_work::where('id',Auth::id())->delete();
      $this->sellForm->mountForm();

      $res=Sell_work::create($this->sellForm->all());
      $this->sell_id=$res->id;
    }
    $this->is_filled=Sell_tran_work::where('sell_id',$this->sell_id)->count()>0;
    $this->sellTranForm->sell_id=$this->sell_id;
    $this->sellTranForm->q1=1;
    $this->show_q2=false;
    $this->form->fill($this->sellTranForm->toArray());


  }

}
