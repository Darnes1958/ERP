<?php

namespace App\Filament\Pages;

use App\Enums\Jomla;
use App\Livewire\Forms\SellForm;
use App\Livewire\Forms\SellTranForm;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Price_type;
use App\Models\Sell;
use App\Models\Sell_tran;


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
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class SellOrderEdit extends Page implements HasForms,HasTable,HasActions, HasInfolists

{
  use InteractsWithForms, InteractsWithTable, InteractsWithActions,InteractsWithInfolists;
  use Raseed;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static string $view = 'filament.pages.sell-order-edit';
  protected ?string $heading="";


  public ?array $selltranData = [];

  public $sell_id;
  public $sell_id2=1;

  public $showInfo = false;
  public $Jomla='قطاعي';

  public $customer_id=1;

  public $has_two;
  public $is_filled='false';
  public SellForm $sellForm;
  public SellTranForm $sellTranForm;


  public function is_two(){
    if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {
      return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
    else return false;
  }

  public function chkOrder($state){
    $this->sell_id=$state;
    $this->sellForm->loadForm($this->sell_id,1);
    if ($this->sellForm->single==1) $this->Jomla='قطاعي'; else $this->Jomla='جملة';
    $this->customer_id=$this->sellForm->customer_id;
    $this->sellTranForm->sell_id=$this->sell_id;
    $this->showInfo=true;
    $this->dispatch('goto', test: 'barcode_id');
  }
  public function chkQuant(){
      if ($this->is_two())
          $this->dispatch('goto', test: 'q2');
      else $this->add_rec();
  }

  public function itemFill($item,$barcode,$stock1){
    $this->sellTranForm->item_id=$item;
    $this->sellTranForm->barcode_id=$barcode;
    $this->sellTranForm->place_id=$this->sellForm->place_id;

    $rec=$this->retPrice($item,$this->sellForm->single,$this->sellForm->price_type_id);

    $this->sellTranForm->price1=$rec['price1'];
    $this->sellTranForm->price2=$rec['price2'];

    $this->form->fill([
      'item_id'=>$item,
      'price1'=>$rec['price1'],
      'price2'=>$rec['price2'],
      'barcode_id'=>$barcode,
      'raseed_all'=>$stock1,
      'raseed_place'=>Place_stock::where('item_id',$item)
        ->where('place_id',$this->sellForm->place_id)->first()->stock1,

    ]);

  }
  public function ChkBarcode(){

    if ($this->selltranData['barcode_id']==null) return;
    $res=Barcode::with('Item')->find($this->selltranData['barcode_id']);

    if (! $res)
      Notification::make()
        ->title('هذا الباركود غير مخزون ')
        ->icon('heroicon-o-check')
        ->iconColor('success')
        ->send();
    else {
      $this->itemFill($res->item_id,$res->id,$res->item->stock1);
      $this->dispatch('goto', test: 'q1');
    }
  }
  public function ChkItem($state){
      $res=Item::find($state);
      $this->itemFill($res->id,$res->barcode,$res->stock1);
      $this->dispatch('goto', test: 'q1');

  }
  public function add_rec()
  {
      $this->sellTranForm->loadForm($this->sell_id, $this->sell_id2, $this->selltranData);
      $this->sellTranForm->place_id=$this->sellForm->place_id;

      $chk=$this->sellTranForm->chkData();
      if ($chk != 'ok') {
          Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
          return;
      }

      $this->sellTranForm->SetQuant();

      $res = Sell_tran::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)
          ->where('item_id', $this->sellTranForm->item_id)->get();

      if ($res->count() > 0)
          Sell_tran::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)
              ->where('item_id', $this->sellTranForm->item_id)
              ->update($this->sellTranForm->all());
      else  Sell_tran::create($this->sellTranForm->all());

      $this->sellTranForm->reset();
      $this->form->fill($this->sellTranForm->toArray());
      $tot = Sell_tran::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)->sum('sub_tot');
      $baky = $tot - Sell::find([$this->sell_id,$this->sell_id2])->pay;
      Sell::find([$this->sell_id,$this->sell_id2])->update([
          'tot' => $tot,
          'baky' => $baky,
      ]);
      $this->sellForm->tot=$tot;
      $this->sellForm->baky=$baky;
      //$this->sellFormBlade->fill($this->sellForm->toArray());
      $this->is_filled=true;
      $this->dispatch('goto', test: 'barcode_id');
  }


  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            Select::make('sell_id')
             ->label('رقم الفاتورة')
              ->options(DB::connection(Auth::user()->company)->table('sells')
                ->join('Customers','sells.customer_id','=','customers.id')
                ->selectRaw('\'الزبون : \'+customers.name+\'  اجمالي الفاتورة : \'+str(tot) as name,sells.id')
                ->latest('sells.created_at')->pluck('name','id'))
              ->searchable()
              ->live()
              ->preload()
              ->inlineLabel()

              ->extraAttributes([
                'wire:keydown.enter' => "chkOrder(state)",
                'wire:change' => "chkOrder(state)",
              ])
          ->columnSpan(2),
            TextInput::make('barcode_id')
              ->label('الباركود')
              ->columnSpan(2)
              ->required()
              ->inlineLabel()
              ->exists()
              ->live(onBlur: true)
              ->extraAttributes([
                'wire:keydown.enter' => "ChkBarcode",
                'wire:change' => "ChkBarcode",
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
                return $this->is_two();
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
                return $this->is_two();
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
                ->visible(function () {return Sell_tran::where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->count()>0;})
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                  $sell=Sell::find([$this->sell_id,$this->sell_id2]);
                  $selltran=Sell_tran::with('Item')->where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->get();
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

                  $this->sellForm->copyToSave($sell);
                  DB::connection(Auth()->user()->company)->beginTransaction();
                  try {

                    $this->sell_id=Sell::max('id')+1;
                    $this->sellForm->id=$this->sell_id;

                    $sell=Sell::create($this->sellForm->all());

                    foreach ($selltran as $item) {
                      $this->sellTranForm->copyToSave($this->sell_id,$this->sell_id2, $item);
                      $this->sellTranForm->place_id=$this->sellForm->place_id;

                      Sell_tran::create($this->sellTranForm->all());

                      $this->sellTranForm->DoDecALl();

                    }
                    $this->sell_id=Auth::id();
                    Sell_tran::where('sell_id',$this->sell_id)
                      ->where('sell_id2',$this->sell_id2)->delete();

                    $this->is_filled=false;

                    $sell->tot=0;  $sell->pay=0; $sell->baky=0;  $sell->save();
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
                ->requiresConfirmation()
                ->action(function () {
                  Sell_tran::where('sell_id',$this->sell_id)->delete();
                  Sell::find([$this->sell_id,$this->sell_id2])->update([
                    'tot'=>0,'pay'=>0,'baky'=>0,
                  ]);
                  $this->is_filled=false;
                  $this->sellForm->fillForm($this->sell_id,$this->sell_id2);
                  $this->sellTranForm->reset();
                  $this->sellFormBlade->fill($this->sellForm->toArray());
                  $this->selltranFormBlade->fill($this->sellTranForm->toArray());
                })
            ])->extraAttributes(['class' => 'items-center justify-between']),

          ])

      ])
      ->statePath('selltranData')
      ->model(Sell_tran::class);
  }

  public function Sellinfolist(Infolist $infolist): Infolist
    {
           return $infolist
               ->state([
                   'sell_id' => $this->sell_id,
                   'customer' =>  Customer::find($this->customer_id)->name,
                   'place' =>  Place::find($this->sellForm->place_id)->name,
                   'price' =>  Price_type::find($this->sellForm->price_type_id)->name,
                   'order_date' => $this->sellForm->order_date,
                   'tot' => $this->sellForm->tot,
                   'pay' => $this->sellForm->pay,
                   'baky' => $this->sellForm->baky,
                   'single' => $this->Jomla,
               ])
               ->schema([
                   \Filament\Infolists\Components\Section::make()
                   ->schema([
                       TextEntry::make('customer')
                       ->label('اسم الزبون')
                       ->color('info')
                       ->columnSpan(2),
                       TextEntry::make('order_date')
                           ->label('تاريخ الفاتورة'),
                       TextEntry::make('sell_id')
                           ->color('info')
                           ->label('رقم الفاتورة'),

                       TextEntry::make('price')
                           ->label('طريقة الدفع')

                           ->badge(),
                       TextEntry::make('place')
                           ->label('نقطة البيع'),

                       TextEntry::make('tot')
                           ->label('الاجمالي'),
                       TextEntry::make('pay')
                           ->label('المدفوع'),
                       TextEntry::make('baky')
                           ->label('الباقي'),
                       TextEntry::make('single')
                           ->badge()
                           ->label('البيع'),


                   ])->columns(6)
                     ->visible($this->showInfo)


               ]);
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


      ->striped();
  }

  public function mount(){

    $this->has_two=Setting::find(Auth::user()->company)->has_two;

  }
}
