<?php

namespace App\Livewire\Sell;

use App\Enums\PlaceType;
use App\Livewire\Forms\SellForm;
use App\Livewire\Forms\SellTranForm;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;

use App\Models\Item;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InpSell extends Component implements HasForms,HasTable,HasActions
{
  use InteractsWithForms, InteractsWithTable, InteractsWithActions;
  use Raseed;
  public ?array $sellData = [];
  public ?array $selltranData = [];

  public $sell_id;
  public $sell_id2;

  public SellForm $sellForm;
  public SellTranForm $sellTranForm;


  protected function getForms(): array
  {
    return [
      'sellFormBlade',
      'selltranFormBlade',
    ];
  }
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

    public function ChkBarcode(){
        $res=Barcode::find($this->selltranData['barcode_id']);

        if (! $res)
            Notification::make()
                ->title('هذا الباركود غير مخزون ')
                ->icon('heroicon-o-check')
                ->iconColor('success')
                ->send();
        else $this->dispatch('goto', test: 'q1');
    }
    public function add_rec()
    {

        $this->sellTranForm->loadForm($this->sell_id, $this->sell_id2, $this->selltranData);

        $res = Sell_tran_work::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)
            ->where('item_id', $this->sellTranForm->item_id)->get();

        if ($res->count() > 0)
            Sell_tran_work::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)
                ->where('item_id', $this->sellTranForm->item_id)
                ->update($this->sellTranForm->all());
        else  Sell_tran_work::create($this->sellTranForm->all());

        $this->sellTranForm->reset();
        $this->selltranFormBlade->fill($this->sellTranForm->toArray());
        $tot = Sell_tran_work::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)->sum('sub_tot');
        $baky = $tot - Sell_work::find([$this->sell_id,$this->sell_id2])->pay;
        Sell_work::find([$this->sell_id,$this->sell_id2])->update([
            'tot' => $tot,
            'baky' => $baky,
        ]);
        $this->sellForm->tot=$tot;
        $this->sellForm->baky=$baky;
        $this->sellFormBlade->fill($this->sellForm->toArray());
    }

  public function sellFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            DatePicker::make('order_date')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'customer_id' })",
              ])
              ->id('order_date')
              ->autofocus()
              ->label('التاريخ')
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->order_date=$state;
                $res->save();
              })
              ->columnSpan(2)
              ->inlineLabel()
              ->required(),
            Select::make('customer_id')
              ->label('الزبون')
              ->relationship('Customer','name')
              ->live()
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->customer_id=$state;
                $res->save();
              })
              ->createOptionForm([
                Section::make('ادخال زبون جديد')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->label('الاسم'),
                    TextInput::make('address')
                      ->label('العنوان'),
                    TextInput::make('mdar')
                      ->label('مدار'),
                    TextInput::make('libyana')
                      ->label('لبيانا'),
                    Hidden::make('user_id'),
                  ])
              ])
              ->editOptionForm([
                Section::make('تعديل بيانات زبون')
                  ->schema([
                    TextInput::make('name')
                      ->required()
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
              ->id('customer_id'),
            Select::make('place_id')
              ->label('مكان التخزين')
              ->relationship('Place','name')
              ->live()
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->place_id=$state;
                $res->save();
              })
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
                Section::make('تعديل مكان تخزين')
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
              ->id('place_id')
              ->visible(Setting::find(Auth::user()->company)->many_place),
            Select::make('price_type_id')
              ->label('طريقة الدفع')
              ->columnSpan(2)
              ->inlineLabel()
              ->default(1)
              ->relationship('Price_type','name')
              ->required()
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->price_type_id=$state;
                $res->save();
              })
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
              ->live(onBlur: true)
              ->inlineLabel()
              ->default('0')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'barcode_id' })",
              ])
              ->afterStateUpdated(function (Set $set,Get $get,$state){
                if (!$state) $set('pay',0);
                $set('baky',$get('tot')-$get('pay'));
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->pay=$get('pay');
                $res->baky=$get('baky');
                $res->save();
              })
              ->id('pay'),
            TextInput::make('baky')
              ->label('المتبقي')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled()
              ->default('0'),
              Radio::make('single')
                  ->hiddenLabel()
                  ->inline()
                  ->columnSpan(2)
                  ->visible(Setting::find(Auth::user()->company)->two_price)
                  ->options([
                      1 => 'قطاعي',
                      0 => 'جملة'
                  ]),


          ])
          ->columns(8)
      ])
      ->statePath('sellData')
      ->model(Sell_work::class);
  }

  public function selltranFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            TextInput::make('barcode_id')
              ->label('الباركود')
              ->required()
              ->inlineLabel()
              ->exists()
              ->live(onBlur: true)
              ->afterStateUpdated(function (Set $set,$state) {
                $res=Barcode::find($state);
                if ($res) {
                  $rec=Item::find($res->item_id);
                  $this->two_unit=$rec->two_unit;
                  $this->price_input=$rec->price_input;

                  $set('item_id',$res->item_id) ;
                  $set('price1', $rec->price1);
                  $set('price2', $rec->price2);
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

              ->required()
              ->afterStateUpdated(function (Set $set,$state){
                $res=Item::find($state);
                $this->two_unit=$res->two_unit;
                $this->price_input=$res->price_input;
                $set('barcode_id',$res->barcode) ;
                $set('price1', $res->price1);
                $set('price2', $res->price2);
              })
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'q1' })",
                'wire:keydown..enter' => "\$dispatch('goto', { test: 'q1' })",
              ])
              ->id('item_id'),

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
              ->label('سعر الصغري')
              ->inlineLabel()
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
              ->extraAttributes(['wire:keydown.enter' => "chkQuant",])
              ->id('q1'),

            TextInput::make('q2')
              ->label('الكمية صغري')
              ->inlineLabel()
              ->numeric()
              ->required()
               ->visible(function (){
                    return $this->is_two();
                })
              ->extraAttributes([
                'wire:keydown.enter' => "add_rec",
              ])
              ->id('q2'),
          ]),
        Section::make()
          ->schema([
            \Filament\Forms\Components\Actions::make([
              \Filament\Forms\Components\Actions\Action::make('تخزين')
                ->icon('heroicon-m-plus')
                ->button()
                ->visible(Sell_tran_work::where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->count()>0)
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                  $sell=Sell_work::find([$this->sell_id,$this->sell_id2]);
                  $selltran=Sell_tran_work::where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->get();
                  if ($selltran->count()==0)
                    Notification::make()
                      ->title('لم يتم ادخال اصناف بعد !! ')
                      ->icon('heroicon-o-exclamation-triangle')
                      ->iconColor('warning')
                      ->send();
                  $this->sellForm->copyToSave($sell);
                  $id=Sell::create($this->sellForm->all());
                  foreach ($selltran as $item) {
                    $this->sellTranForm->copyToSave($id->id,$id->id2, $item);
                    $this->sellTranForm->place_id=$this->sellForm->place_id;
                    Sell_tran::create($this->sellTranForm->all());
                  }
                  Sell_tran_work::where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->delete();
                  $sell->tot=0;  $sell->pay=0; $sell->baky=0;  $sell->save();
                }),
              \Filament\Forms\Components\Actions\Action::make('مسح')
                ->icon('heroicon-m-trash')
                ->button()
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                  return true;
                })
            ])->extraAttributes(['class' => 'items-center justify-between']),

          ])

      ])
      ->statePath('selltranData')
      ->model(Sell_tran_work::class);
  }

  public function table(Table $table):Table
  {
    return $table
      ->query(function (Sell_tran_work $sell_tran)  {
        $sell_tran=Sell_tran_work::where('user_id',Auth::id())->where('sell_id',$this->sell_id)
          ->where('sell_id2',$this->sell_id2) ;
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
            $this->sellForm->fillForm($this->sell_id,$this->sell_id2);
            $this->sellFormBlade->fill($this->sellForm->toArray());
          })
          ->icon('heroicon-m-trash')
          ->iconButton()->color('danger')
          ->hiddenLabel()
          ->requiresConfirmation(),
        \Filament\Tables\Actions\Action::make('edit')
          ->action(function (Sell_tran_work $record){
            $this->selltranFormBlade->fill($record->toArray());
            $this->dispatch('goto',  test: 'q1' );
          })
          ->icon('heroicon-m-pencil')
          ->iconButton()->color('info')
          ->hiddenLabel()
      ])
      ->bulkActions([

        BulkAction::make('deleteAll')
          ->action(function (Collection $records){
            $records->each->delete();
          })
          ->icon('heroicon-m-trash')
          ->color('danger')
          ->Label('الغاء المحدد')
          ->requiresConfirmation(),

      ])

      ->striped();
  }

  public function mount(){
    $res=Sell_work::where('user_id',Auth::id())
      ->whereDate('created_at', '=', date('Y-m-d'))->first();
    if ($res){
      $this->sellForm->fillForm($res->id,$res->id2);
      $this->sell_id=$res->id;
      $this->sell_id2=$res->id2;}
    else {
      $this->sellForm->mountForm();
      $res=Sell_work::create($this->sellForm->all());
      $this->sell_id=$res->id;
      $this->sell_id2=$res->id2;
    }
    $this->sellFormBlade->fill($this->sellForm->toArray());
    $this->selltranFormBlade->fill($this->sellTranForm->toArray());
  }

  public function render()
    {
        return view('livewire.sell.inp-sell');
    }
}