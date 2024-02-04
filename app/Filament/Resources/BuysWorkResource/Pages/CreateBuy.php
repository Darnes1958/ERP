<?php

namespace App\Filament\Resources\BuysWorkResource\Pages;

use App\Enums\PlaceType;
use App\Enums\TwoUnit;
use App\Filament\Resources\BuysWorkResource;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\Item;
use App\Models\Price_buy;
use App\Models\Recsupp;
use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;


class CreateBuy extends Page implements HasTable
{
  use InteractsWithTable;
  use Raseed;
    protected static string $resource = BuysWorkResource::class;

    protected static string $view = 'filament.resources.buys-work-resource.pages.create-buy';
    protected ?string $heading="";

  public $buy;
  public $buytran;
  public $buyData;
  public $buytranData;
  public $buyStoreData;

  public $collapse=false;
  public function mount()
  {
    $this->buy = Buys_work::find(auth()->id());
    if (!$this->buy)
      $this->buy=Buys_work::create([
        'id'=>Auth::id(),'user_id'=>0,
      ]);
    $this->buyForm->fill($this->buy->toArray());

    $this->buyTranForm->fill([]);
    $this->buyStoreForm->fill([]);
  }
  protected function getForms(): array
  {

    return array_merge(parent::getForms(), [
      "buyForm" => $this->makeForm()
        ->model(Buys_work::class)
        ->schema($this->getBuyFormSchema())
        ->statePath('buyData'),
      "buyTranForm" => $this->makeForm()
        ->model(Buy_tran_work::class)
        ->schema($this->getBuyTranFormSchema())
        ->statePath('buytranData'),
      "buyStoreForm" => $this->makeForm()
        ->model(Recsupp::class)
        ->schema($this->getBuyStoreFormSchema())
        ->statePath('buyStoreData'),

    ]);
  }
  public function updateBuys()
  {
     $this->buy->update($this->buyForm->getState());
  }
  public function updatePay()
  {
    $this->buy->update($this->buyForm->getState());
    $this->buy->baky=$this->buy->tot-$this->buy->pay;
    $this->buy->save();
    $this->buyForm->fill($this->buy->toArray());
  }

  protected function getBuyStoreFormSchema(): array
  {
    return [
      Section::make()
        ->schema([
          Select::make('acc_id')
           ->relationship('Acc','name')
           ->label('المصرف')
           ->inlineLabel()
           ->visible(function (){
             $record=Buys_work::find(Auth::id());
             return $record->pay>0 && $record->price_type_id==2;
           }),
          \Filament\Forms\Components\Actions::make([
            Action::make('store')
              ->label('تخزين')
              ->icon('heroicon-m-plus')
              ->button()
              ->visible(function (){return Buy_tran_work::where('buy_id',Auth::id())->count()>0;})
              ->color('success')
              ->requiresConfirmation()
              ->action(function () {
                $buytran=Buy_tran_work::where('buy_id',Auth::id())->get();
                if ($this->buy->pay>0 && $this->buy->price_type_id!=1) {
                  if (!$this->buyStoreData['acc_id'])
                  {
                    Notification::make()->title('يجب اختيار المصرف ')->color('danger')->icon('heroicon-m-no-symbol')->send();return;
                  }
                  $acc=$this->buyStoreData['acc_id'];
                }
                else $acc='';


                unset($this->buy['id']);
                $id=Buy::create($this->buy->toArray());
                foreach ($buytran as $tran) {
                  $tran->buy_id=$id->id;
                  $tran->qs1=$tran->q1;
                  unset($tran['id']);
                  Buy_tran::create($tran->toArray());
                  $this->incAllBuy($tran->item_id,$this->buy->place_id,$tran->q1,$this->buy->price_type_id,$tran->price_input);

                }
                if ($this->buy->pay>0){

                  $recipt= Recsupp::create([
                    'receipt_date'=>$this->buy->order_date,
                    'supplier_id'=>$this->buy->supplier_id,
                    'buy_id'=>$id->id,
                    'price_type_id'=>$this->buy->price_type_id,
                    'rec_who'=>4,
                    'imp_exp'=>1,
                    'val'=>$this->buy->pay,
                    'acc_id'=>$acc,
                    'notes'=>'فاتورة مشتريات رقم '.strval($id->id),
                    'user_id'=>Auth::id()
                  ]);
                  Buy::find($id->id)->update(['receipt_id'=>$recipt->id]);
                }
                $this->buy=Buys_work::find(Auth::id());
                $this->buy->tot=0;  $this->buy->pay=0; $this->buy->baky=0;  $this->buy->save();
                $this->buyForm->fill($this->buy->toArray());
                $this->buytran= Buy_tran_work::where('buy_id', Auth::id())->delete();
                $this->buyTranForm->fill([]);
                $this->collapse=false;
              }),


            Action::make('مسح')
              ->icon('heroicon-m-trash')
              ->button()
              ->color('danger')
              ->requiresConfirmation()
              ->after(function ()  {
                //
              })
              ->action(function () {

                $this->buy->tot = 0;
                $this->buy->pay = 0;
                $this->buy->baky = 0;
                $this->buy->save();
                $this->buyForm->fill($this->buy->toArray());
                $this->buytran= Buy_tran_work::where('buy_id', Auth::id())->delete();
                $this->buyTranForm->fill([]);

              })
          ])->extraAttributes(['class' => 'items-center justify-between']),

        ])


    ];
  }


  protected function getBuyFormSchema(): array
  {
    return [
      Section::make()
        ->schema([
          DatePicker::make('order_date')
            ->id('order_date')
            ->autofocus()
            ->label('التاريخ')
            ->columnSpan(2)
            ->inlineLabel()
            ->extraAttributes(['x-on:change' => "\$wire.updateBuys"])
            ->required(),
          Select::make('supplier_id')
            ->label('المورد')
            ->extraAttributes(['x-on:change' => "\$wire.updateBuys"])
            ->relationship('Supplier','name')
            ->live()
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
            ->id('supplier_id'),
          Select::make('place_id')
            ->label('مكان التخزين')
            ->relationship('Place','name')
            ->live()
            ->required()
            ->inlineLabel()
            ->columnSpan(3)
            ->extraAttributes(['x-on:change' => "\$wire.updateBuys"])
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
            ->id('place_id')
            ->visible(Setting::find(Auth::user()->company)->many_place),
          Select::make('price_type_id')
            ->label('طريقة الدفع')
            ->columnSpan(2)
            ->inlineLabel()
            ->live()
            ->default(1)
            ->relationship('Price_type','name')
            ->required()
            ->extraAttributes(['x-on:change' => "\$wire.updateBuys"])
            ->id('price_type_id'),
          TextInput::make('tot')
            ->label('إجمالي الفاتورة')
            ->columnSpan(2)
            ->inlineLabel()
            ->readOnly(),
          TextInput::make('pay')
            ->label('المدفوع')
            ->columnSpan(2)
            ->extraAttributes(['x-on:change' => "\$wire.updatePay"])
            ->live(onBlur: true)
            ->inlineLabel()
            ->default('0')
            ->id('pay'),
          TextInput::make('baky')
            ->label('المتبقي')
            ->columnSpan(2)
            ->inlineLabel()
            ->readOnly()
            ->default('0'),
        ])
        ->columns(8)
        ->collapsible()
        ->collapsed(fn() :bool=>$this->collapse)
    ];
  }

  public function sub_tot(){
    $this->buytran->sub_input=$this->buytran->q1*$this->buytran->price_input;
    $this->buytran->save();
    $this->buyTranForm->fill([]);
  }
  public function tot(){
    $tot=Buy_tran_work::where('buy_id',Auth::id())->sum('sub_input');
    $baky=$tot-$this->buy->pay;
    $this->buy->tot=$tot;
    $this->buy->baky=$baky;
    $this->buy->save();
    $this->buyForm->fill($this->buy->toArray());
  }
  public function fill_item($item,$barcode){
    $price_buy=Price_buy::where('price_type_id',$this->buyData['price_type_id'])
      ->where('item_id',$item)->first();
    if ($price_buy) $price_input=$price_buy->price;
    else $price_input=Item::find($item)->price_buy;

    $this->buytran=Buy_tran_work::where('buy_id',Auth::id())
      ->where('item_id',$item)->first();

    if ($this->buytran)
      $this->buyTranForm->fill($this->buytran->toArray());
    else $this->buyTranForm->fill([
      'barcode_id'=>$barcode,'item_id'=>$item,'price_input'=>$price_input,'q1'=>'',
      'buy_id'=>Auth::id(),'user_id'=>Auth::id()]);
    if ($price_input==0)  $this->dispatch('gotoitem',  test: 'price_input' );
    else $this->dispatch('gotoitem',  test: 'q1' );
  }
  public function ChkBarcode($state){
      $this->collapse=true;
    if ($state==null) return;
    $res=Barcode::find($state);
    if (! $res)
      Notification::make()
        ->title('هذا الباركود غير مخزون ')
        ->icon('heroicon-o-check')
        ->iconColor('success')
        ->send();
    else {

      $this->fill_item($res->item_id,$state);
      $this->dispatch('goto', test: 'q1');
    }
  }
  public function ChkItem($state){
    $this->collapse=true;

    if ($state==null) return;
    $res=Item::find($state);
    if (!$res) return;
    $this->fill_item($state,$res->barcode);
  }
  public function add_rec(){
    $this->validate();
    $this->buytran=Buy_tran_work::where('item_id',$this->buytranData['item_id'])->first();
    if ($this->buytran)
      $this->buytran->update($this->buyTranForm->getState());
    else
    $this->buytran=Buy_tran_work::create(collect($this->buytranData)->except('id')->toArray());
    $this->sub_tot();
    $this->tot();

    $this->dispatch('gotoitem', test: 'barcode_id');
  }

  protected function getBuyTranFormSchema(): array
  {
    return [
      Section::make()
        ->schema([
          TextInput::make('barcode_id')
            ->label('الباركود')
            ->required()
            ->inlineLabel()
            ->exists(Barcode::class,column: 'id')
            ->live(onBlur: true)
            ->afterStateUpdated(function ($state){$this->ChkBarcode($state);})
           ->extraAttributes(['wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",])
            ->autocomplete(false)
            ->id('barcode_id'),
          Select::make('item_id')
            ->label('الصنف')
            ->searchable()
            ->preload()
            ->relationship('Item','name')
            ->live(onBlur: true)
            ->reactive()
            ->required()
            ->afterStateUpdated(function ($state){$this->ChkItem($state);})
            ->createOptionForm([
              Section::make('ادخال صنف')
                ->schema([
                  TextInput::make('id')
                    ->hidden(fn(string $operation)=>$operation=='create')
                    ->disabled()
                    ->label('الرقم الألي'),
                  TextInput::make('name')
                    ->label('اسم الصنف')
                    ->required()
                    ->live()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                      'unique' => ' :attribute مخزون مسبقا ',
                    ])
                    ->columnSpan(2),
                  TextInput::make('barcode')
                    ->label('الباركود')
                    ->required()
                    ->readOnly(!Setting::find(Auth::user()->company)->barcode)
                    ->live()
                    ->default(Barcode::max('id')+1)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                      'unique' => 'هذا الـ :attribute مخزون مسبقا',
                    ]),

                  Radio::make('two_unit')
                    ->label('مستوي الوحدات')
                    ->inline()
                    ->inlineLabel(false)
                    ->options(TwoUnit::class)
                    ->default(0)
                    ->required()
                    ->disabled(function ($operation,$state, Get $get){
                      return
                        $operation=='edit'
                        && $state
                        && Sell_tran::where('item_id',$get('id'))->where('q2','>',0)->exists();
                    })
                    ->visible(Setting::find(Auth::user()->company)->has_two),
                  Select::make('unita_id')
                    ->label('الوحدة')
                    ->relationship('Unita','name')
                    ->required()
                    ->columnSpan(2)
                    ->createOptionForm([
                      Section::make('ادخال وحدات كبري')
                        ->description('ادخال وحدة كبري (صندوق,دزينه,كيس .... الخ)')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])
                    ])
                    ->editOptionForm([
                      Section::make('تعديل وحدات كبري')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])->columns(2)
                    ]),
                  Select::make('unitb_id')
                    ->label('الوحدة الصغري')
                    ->relationship('Unitb','name')
                    ->required()
                    ->columnSpan(2)
                    ->createOptionForm([
                      Section::make('ادخال وحدات صغري')
                        ->description('ادخال وحدة صغري (قطعة,علبة .... الخ)')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])->columns(2)
                    ])
                    ->editOptionForm([
                      Section::make('تعديل وحدات صغري')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])->columns(2)
                    ])
                    ->hidden(fn(Get $get): bool => ! $get('two_unit')),
                  TextInput::make('count')
                    ->label('العدد')
                    ->required()
                    ->hidden(fn(Get $get): bool =>  ! $get('two_unit')),
                  TextInput::make('price_buy')
                    ->label('سعر الشراء')
                    ->required()
                    ->id('price_buy'),
                  TextInput::make('price1')
                    ->label('سعر البيع قطاعي')
                    ->required(),
                  TextInput::make('price2')
                    ->label('سعر الصغري قطاعي')
                    ->required()
                    ->hidden(fn(Get $get): bool => ! $get('two_unit')),
                  TextInput::make('pricej1')
                    ->label('سعر البيع جملة')
                    ->hidden(!Setting::find(Auth::user()->company)->jomla)
                    ->required(),
                  TextInput::make('pricej2')
                    ->label('سعر الصغري جملة')
                    ->required()
                    ->hidden(fn(Get $get): bool => ! $get('two_unit')),

                  Select::make('item_type_id')
                    ->label('التصنيف')
                    ->relationship('Item_type','name')
                    ->required()
                    ->columnSpan(2)
                    ->createOptionForm([
                      Section::make('ادخال تصنيف للأصناف')

                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])
                    ])
                    ->editOptionForm([
                      Section::make('تعديل تصنيف')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])->columns(2)
                    ]),
                  Select::make('company_id')
                    ->label('الشركة المصنعة')
                    ->relationship('Company','name')
                    ->default(1)
                    ->columnSpan(2)
                    ->createOptionForm([
                      Section::make('ادخال شركات مصنع')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])
                    ])
                    ->editOptionForm([
                      Section::make('تعديل شركات مصنعة')
                        ->schema([
                          TextInput::make('name')
                            ->required()
                            ->unique()
                            ->label('الاسم'),
                        ])
                    ]),
                  TextInput::make('user_id')
                   ->label('رقم المستخدم')
                   ->extraAttributes(['bg-blue-500'])
                  ->readOnly()
                  ->default(Auth::id()),
                ])
                ->columns(4)
            ])
            ->id('item_id'),
          DatePicker::make('exp_date')
            ->label('تاريخ الصلاحية')
            ->inlineLabel()
            ->extraAttributes([
              'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
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
              'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
            ]),
          TextInput::make('q1')
            ->label('الكمية')
            ->inlineLabel()
            ->numeric()
            ->required()

            ->extraAttributes( [
              'wire:keydown.enter' => 'add_rec'
            //  'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'barcode_id' })",
            ])
            ->id('q1'),
        ]),

    ];
  }

  public function table(Table $table):Table
  {
      return $table
        ->query(function (Buy_tran_work $buy_tran) {
          $buy_tran = Buy_tran_work::where('buy_id', Auth::id());
          return $buy_tran;
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
            ->numeric(
              decimalPlaces: 3,
              decimalSeparator: '.',
              thousandsSeparator: ',',
            )
            ->sortable(),
          TextColumn::make('sub_input')
            ->label('المجموع')
            ->numeric(
              decimalPlaces: 3,
              decimalSeparator: '.',
              thousandsSeparator: ',',
            )
            ->sortable(),
        ])
        ->actions([
          \Filament\Tables\Actions\Action::make('delete')
            ->action(function (Buy_tran_work $record) {
              $record->delete();
              $this->tot();
              $this->buyTranForm->fill([]);
            })
            ->icon('heroicon-m-trash')
            ->iconButton()->color('danger')
            ->hiddenLabel()
            ->requiresConfirmation(),
        ])
        ->emptyStateHeading('لم يتم ادخال اصناف')

        ->striped();
  }

}
