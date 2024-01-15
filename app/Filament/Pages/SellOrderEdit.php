<?php

namespace App\Filament\Pages;

use App\Enums\Jomla;
use App\Livewire\Forms\SellForm;
use App\Livewire\Forms\SellTranForm;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;

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
    $this->sellForm->loadForm($this->sell_id);
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
      $this->sellTranForm->loadForm($this->sell_id, $this->selltranData);

      $chk=$this->sellTranForm->chkDataEdit($this->sellForm->place_id);
      if ($chk != 'ok') {
          Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
          return;
      }

      $this->sellTranForm->SetQuant();

      $res = Sell_tran::where('sell_id', $this->sell_id)->where('item_id', $this->sellTranForm->item_id)->first();
      if ($res){
        $this->incAll($this->sell_id,$this->sellTranForm->item_id,$this->sellForm->place_id,$res->q1,$res->q2);
        $res->delete();}
      Sell_tran::create($this->sellTranForm->all());

      $this->decAll($this->sell_id,$this->sellTranForm->item_id,$this->sellForm->place_id,$this->sellTranForm->q1,$this->sellTranForm->q2);

      $this->sellTranForm->reset();
      $this->form->fill($this->sellTranForm->toArray());
      $tot = Sell_tran::where('sell_id', $this->sell_id)->sum('sub_tot');
      $baky = $tot - Sell::find($this->sell_id)->pay;
      Sell::find($this->sell_id)->update([
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

              \Filament\Forms\Components\Actions\Action::make('الغاء الفاتورة')
                ->icon('heroicon-m-trash')
                ->button()
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                  $selltran=Sell_tran::where('sell_id',$this->sell_id)->get();
                    foreach ($selltran as $tran)
                        $this->incAll($this->sell_id,$tran->item_id,$this->sellForm->place_id,$tran->q1,$tran->q2);

                  Sell_tran::where('sell_id',$this->sell_id)->delete();
                  Sell::find($this->sell_id)->delete();
                  $this->is_filled=false;
                  $this->sell_id='';
                  $this->sellForm->reset();
                  $this->sellTranForm->reset();

                  $this->form->fill($this->sellForm->toArray());

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

      ->actions([
          \Filament\Tables\Actions\Action::make('delete')
              ->action(function (Sell_tran $record){
                  $this->incAll($this->sell_id,$record->item_id,$this->sellForm->place_id,$record->q1,$record->q2);
                  $record->delete();

                  $tot=Sell_tran::where('sell_id',$this->sell_id)->sum('sub_tot');
                  $baky=$tot-Sell::find($this->sell_id)->pay;
                  Sell::find($this->sell_id)->update([
                      'tot'=>$tot,
                      'baky'=>$baky,

                  ]);

                  $this->sellForm->loadForm($this->sell_id);
                  $this->form->fill($this->sellForm->toArray());
              })
              ->icon('heroicon-m-trash')
              ->iconButton()->color('danger')
              ->hiddenLabel()
              ->hidden(function (){
                  return Sell_tran::where('sell_id',$this->sell_id)->count()==1;
              })
              ->requiresConfirmation(),
      ])

      ->striped();
  }

  public function mount(){

    $this->has_two=Setting::find(Auth::user()->company)->has_two;

  }
}
