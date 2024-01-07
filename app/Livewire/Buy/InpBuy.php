<?php

namespace App\Livewire\Buy;

use App\Enums\PlaceType;
use App\Livewire\Forms\BuyForm;
use App\Livewire\Forms\BuyTranForm;
use App\Models\Barcode;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\Item;


use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;



class InpBuy extends Component implements HasForms,HasTable,HasActions
{
  use InteractsWithForms,InteractsWithTable,InteractsWithActions;

  public ?array $buyData = [];
  public ?array $buytranData = [];

  public $buy_id;

  protected function getForms(): array
  {
    return [
      'buyFormBlade',
      'buytranFormBlade',
    ];
  }

  public BuyForm $buyForm;
  public BuyTranForm $buyTranForm;

  public function ChkBarcode(){
    $res=Barcode::find($this->buytranData['barcode_id']);

    if (! $res)
      Notification::make()
        ->title('هذا الباركود غير مخزون ')
        ->icon('heroicon-o-check')
        ->iconColor('success')
        ->send();
   else $this->dispatch('goto', test: 'q1');
  }
  public function add_rec(){
   $this->buyTranForm->loadForm($this->buy_id,$this->buytranData);
   Buy_tran_work::create($this->buyTranForm->all());
   $this->buyTranForm->reset();
   $this->buytranFormBlade->fill($this->buyTranForm->toArray());
   $tot=Buy_tran_work::where('buy_id',$this->buy_id)->sum('sub_input');
   $baky=$tot-Buys_work::find($this->buy_id)->pay;
   Buys_work::find($this->buy_id)->update([
     'tot'=>$tot,
     'baky'=>$baky,

   ]);

    $this->buyForm->fillForm($this->buy_id);
    $this->buyFormBlade->fill($this->buyForm->toArray());


    $this->dispatch('goto', test: 'barcode_id');
  }

  public function buyFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            DatePicker::make('order_date')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'supplier_id' })",
              ])
              ->id('order_date')
              ->autofocus()
              ->label('التاريخ')
              ->columnSpan(2)
              ->inlineLabel()
              ->required(),
            Select::make('supplier_id')
              ->label('المورد')
              ->relationship('Supplier','name')
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->createOptionForm([
                Section::make('ادخال مورد جديد')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->unique()
                      ->label('الاسم'),
                    TextInput::make('address')
                      ->label('العنوان'),
                    TextInput::make('mdar')
                      ->label('مدار'),
                    TextInput::make('libyana')
                      ->label('لبيانا'),
                    Hidden::make('user_id')
                      ->default(Auth::id()),

                  ])
              ])
              ->editOptionForm([
                Section::make('تعديل بيانات مورد')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->unique()
                      ->label('الاسم'),
                    TextInput::make('address')
                      ->label('العنوان'),
                    TextInput::make('mdar')
                      ->label('مدار'),
                    TextInput::make('libyana')
                      ->label('لبيانا'),
                    Hidden::make('user_id')
                      ->default(Auth::id()),

                  ])->columns(2)
              ])
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'place_id' })",
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'place_id' })",
              ])
              ->id('supplier_id'),
            Select::make('place_id')
              ->label('مكان التخزين')
              ->relationship('Place','name')
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->createOptionForm([
                Section::make('ادخال مكان تخزين')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->unique()
                      ->label('الاسم'),
                    Radio::make('place_type')
                      ->inline()
                      ->options(PlaceType::class)
                  ])
              ])
              ->editOptionForm([
                Section::make('تعديل وحدات كبري')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->unique()
                      ->label('الاسم'),
                    Radio::make('place_type')
                      ->inline()
                      ->options(PlaceType::class)
                  ])->columns(2)
              ])
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'price_type_id' })",
                'wire:keydown..enter' => "\$dispatch('goto', { test: 'price_type_id' })",
              ])
              ->id('place_id'),
            Select::make('price_type_id')
              ->label('طريقة الدفع')
              ->columnSpan(2)
              ->inlineLabel()
              ->default(1)
              ->relationship('Price_type','name')
              ->required()
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'pay' })",
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'pay' })",
              ])
              ->id('price_type_id'),
            TextInput::make('tot')
              ->label('إجمالي الفاتورة')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled(),
            TextInput::make('pay')
              ->label('المدفوع')
              ->columnSpan(2)
              ->inlineLabel()
              ->default('0')
              ->extraAttributes([

                'wire:keydown.enter' => "\$dispatch('goto', { test: 'barcode_id' })",
              ])
              ->id('pay'),
            TextInput::make('baky')
              ->label('المتبقي')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled()
              ->default('0'),


          ])->columns(8),



      ])
      ->statePath('buyData')
      ->model(Buys_work::class);
  }

  public function buytranFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
                TextInput::make('barcode_id')
                  ->label('الباركود')
                  ->required()
                  ->exists()
                  ->live(onBlur: true)
                  ->afterStateUpdated(function (Set $set,$state) {
                    $res=Barcode::find($state);

                    if ($res) {
                      $rec=Item::find($res->item_id);
                      $set('item_id',$res->item_id) ;
                      $set('price_input', $rec->price_buy);
                      $set('name', $rec->name);
                    }
                  })
                  ->extraAttributes([
                    'wire:keydown.enter' => "ChkBarcode",
                  ])
                  ->id('barcode_id'),

                Select::make('item_id')
                  ->label('الصنف')
                  ->searchable()
                  ->preload()
                  ->relationship('Item','name')
                  ->inlineLabel()
                  ->live()
                  ->reactive()
                  ->required()

                  ->afterStateUpdated(function (Set $set,$state){

                    $res=Item::find($state);
                    $set('barcode_id',$res->barcode) ;
                    $set('price_input', $res->price_buy);
                  })
                  ->extraAttributes([
                    'wire:change' => "\$dispatch('goto', { test: 'q1' })",
                    'wire:keydown..enter' => "\$dispatch('goto', { test: 'q1' })",

                  ])
                  ->id('item_id'),
            TextInput::make('price_input')
              ->label('السعر')
              ->inlineLabel()
              ->numeric()
              ->live()
              ->required()
              ->id('price_input')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
              ]),

                TextInput::make('q1')
                  ->label('الكمية')
                  ->inlineLabel()
                  ->numeric()
                  ->required()
                  ->extraAttributes([
                    'wire:keydown.enter' => "add_rec",
                  ])
                  ->id('q1'),

            \Filament\Forms\Components\Actions::make([
              \Filament\Forms\Components\Actions\Action::make('اضافة')
                ->icon('heroicon-m-plus')
                ->button()
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                  return true;
                })


            ])->extraAttributes(['class' => 'items-center justify-between']),
          ]),

      ])
      ->statePath('buytranData')
      ->model(Buy_tran_work::class);
  }

  public function table(Table $table):Table
  {
    return $table
      ->query(function (Buy_tran_work $buy_tran)  {
        $buy_tran=Buy_tran_work::where('user_id',Auth::id()) ;
        return  $buy_tran;
      })
      ->columns([
        TextColumn::make('sort'),
        TextColumn::make('item_id'),
        TextColumn::make('barcode_id'),
        TextColumn::make('Item.name'),
        TextColumn::make('q1'),
        TextColumn::make('price_input'),
        TextColumn::make('sub_input'),
      ])

      ->striped();
  }

  public function mount(){
    $res=Buys_work::where('user_id',Auth::id())->first();
    if ($res){
      $this->buyForm->fillForm($res->id);
      $this->buy_id=$res->id;}
     else {
       $this->buyForm->mountForm();
      $res=Buys_work::create($this->buyForm->all());
      $this->buy_id=$res->id;
     }
    $this->buyFormBlade->fill($this->buyForm->toArray());
    $this->buytranFormBlade->fill($this->buyTranForm->toArray());
  }

  public function render()
    {
        return view('livewire.buy.inp-buy');
    }
}
