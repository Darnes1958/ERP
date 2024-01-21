<?php

namespace App\Livewire\Buy;

use App\Enums\PlaceType;
use App\Livewire\Forms\BuyForm;
use App\Livewire\Forms\BuyTranForm;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\Item;


use App\Models\Place_stock;
use App\Models\Price_buy;

use App\Models\Recsupp;
use App\Models\Setting;
use App\Models\Supplier;
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
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;



class InpBuy extends Component implements HasForms,HasTable,HasActions
{
  use InteractsWithForms,InteractsWithTable,InteractsWithActions;
  use Raseed;
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
    if ($this->buytranData['barcode_id']==null) return;
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
   $this->buyTranForm->qs1=$this->buyTranForm->q1;

   $res=Buy_tran_work::where('buy_id',$this->buy_id)
       ->where('item_id',$this->buyTranForm->item_id)->get();

   if ($res->count()>0)
       Buy_tran_work::where('buy_id',$this->buy_id)
           ->where('item_id',$this->buyTranForm->item_id)
           ->update($this->buyTranForm->all());
   else  Buy_tran_work::create($this->buyTranForm->all());

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
              ->afterStateUpdated(function ($state){
                $res=Buys_work::find($this->buy_id);
                $res->order_date=$state;
                $res->save();
              })
              ->columnSpan(2)
              ->inlineLabel()
              ->required(),
            Select::make('supplier_id')
              ->label('المورد')
              ->relationship('worksupplier','name')
              ->live()
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->afterStateUpdated(function ($state){
                $res=Buys_work::find($this->buy_id);
                $res->supplier_id=$state;
                $res->save();
              })
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
                ->live()
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->afterStateUpdated(function ($state){
                $res=Buys_work::find($this->buy_id);
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
                $res=Buys_work::find($this->buy_id);
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
                  $res=Buys_work::find($this->buy_id);
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


          ])
          ->columns(8)
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
            DatePicker::make('exp_date')
             ->label('تاريخ الصلاحية')
             ->inlineLabel()
                ->extraAttributes([
                    'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
                ])
             ->visible(Setting::find(Auth::user()->company)->has_exp) ,

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
          ]),
        Section::make()
          ->schema([
              \Filament\Forms\Components\Actions::make([
                  \Filament\Forms\Components\Actions\Action::make('تخزين')
                      ->icon('heroicon-m-plus')
                      ->button()
                      ->visible(function (){return Buy_tran_work::where('buy_id',$this->buy_id)->count()>0;})
                      ->color('success')
                      ->requiresConfirmation()

                      ->action(function () {
                          $buy=Buys_work::find($this->buy_id);
                          $buytran=Buy_tran_work::where('buy_id',$this->buy_id)->get();
                          if ($buytran->count()==0)
                            Notification::make()
                                  ->title('لم يتم ادخال اصناف بعد !! ')
                                  ->icon('heroicon-o-exclamation-triangle')
                                  ->iconColor('warning')
                                  ->send();
                          $this->buyForm->copyToSave($buy);
                          $id=Buy::create($this->buyForm->all());
                          foreach ($buytran as $item) {
                              $this->buyTranForm->copyToSave($id->id, $item);

                              Buy_tran::create($this->buyTranForm->all());
                              $this->incAllBuy($item->item_id,$this->buyForm->place_id,$item->q1,$item->q2);

                          }
                          if ($this->buyForm->pay !=0){
                              Recsupp::create([
                                  'receipt_date'=>$this->buyForm->order_date,
                                  'supplier_id'=>$this->buyForm->supplier_id,
                                  'buy_id'=>$id->id,
                                  'price_type_id'=>$this->buyForm->price_type_id,
                                  'rec_who'=>4,
                                  'imp_exp'=>1,
                                  'val'=>$this->buyForm->pay,
                                  'notes'=>'فاتورة مشتريات رقم '.strval($id->id),
                                  'user_id'=>Auth::id()
                              ]);
                          }
                          Buy_tran_work::where('buy_id',$this->buy_id)->delete();
                          $buy->tot=0;  $buy->pay=0; $buy->baky=0;  $buy->save();
                        $this->buyForm->fillForm($this->buy_id);
                        $this->buyTranForm->reset();
                        $this->buyFormBlade->fill($this->buyForm->toArray());
                        $this->dispatch('goto',  test: 'barcode_id' );

                      }),
                    \Filament\Forms\Components\Actions\Action::make('مسح')
                        ->icon('heroicon-m-trash')
                        ->button()
                        ->color('danger')
                        ->requiresConfirmation()
                        ->after(function (){
                          $this->dispatch('goto',  test: 'barcode_id' );
                        })
                        ->action(function () {
                            Buy_tran_work::where('buy_id',$this->buy_id)->delete();
                            Buys_work::find($this->buy_id)->update([
                              'tot'=>0,'pay'=>0,'baky'=>0,
                            ]);
                            $this->buyForm->fillForm($this->buy_id);
                            $this->buyTranForm->reset();
                          $this->buyFormBlade->fill($this->buyForm->toArray());
                          $this->buytranFormBlade->fill($this->buyTranForm->toArray());

                        })
              ])->extraAttributes(['class' => 'items-center justify-between']),

          ])

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
              ->label('الكمية')
              ->sortable(),
          TextColumn::make('price_input')
              ->label('سعر الشراء')
              ->sortable(),
          TextColumn::make('sub_input')
              ->label('المجموع')
              ->sortable(),
      ])
        ->actions([
            \Filament\Tables\Actions\Action::make('delete')
               ->action(function (Buy_tran_work $record){
                   $record->delete();
               })
               ->icon('heroicon-m-trash')
                ->iconButton()->color('danger')
                ->hiddenLabel()
               ->requiresConfirmation(),
            \Filament\Tables\Actions\Action::make('edit')
                ->action(function (Buy_tran_work $record){
                    $this->buytranFormBlade->fill($record->toArray());
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
