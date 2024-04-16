<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Enums\TwoUnit;
use App\Filament\Resources\BuyResource;
use App\Livewire\Traits\Raseed;
use App\Models\Acc;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Kazena;
use App\Models\Price_buy;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use function Symfony\Component\String\b;

class BuyEdit extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithRecord;
    use Raseed;
    protected static string $resource = BuyResource::class;

    protected static string $view = 'filament.resources.buy-resource.pages.buy-edit';

    protected ?string $heading="";

    public $buy;
    public $buytran;
    public $buyData;
    public $buytranData;
    public $collapse=false;

    public $buy_id;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->buy_id=$this->record->id;
        $this->buy=Buy::find($this->buy_id);


        $this->buyForm->fill($this->record->toArray());
        if ($this->buy->receipt_id!=null){
            $receipt=Recsupp::find($this->buy->receipt_id);

            if ($receipt->acc_id)
            $this->buyForm->fill(collect($this->record)->put('acc_id',$receipt->acc_id)->toArray());
            if ($receipt->kazena_id)
                $this->buyForm->fill(collect($this->record)->put('kazena_id',$receipt->kazena_id)->toArray());
        }

        $this->buyTranForm->fill([]);
    }
    protected function getForms(): array
    {

        return array_merge(parent::getForms(), [
            "buyForm" => $this->makeForm()
                ->model(Buy::class)
                ->schema($this->getBuyFormSchema())
                ->statePath('buyData'),
            "buyTranForm" => $this->makeForm()
                ->model(Buy_tran::class)
                ->schema($this->getBuyTranFormSchema())
                ->statePath('buytranData'),
        ]);
    }

    protected function getBuyFormSchema(): array
    {
        return [

            Section::make()
                ->schema([
                    DatePicker::make('order_date')
                        ->hiddenLabel()
                        ->prefix('التاريخ')
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'supplier_id' })",
                        ])
                        ->id('order_date')
                        ->autofocus()
                        ->afterStateUpdated(function ($state){
                            $res=Buy::find($this->buy_id);
                            $res->order_date=$state;
                            $res->save();
                        })
                        ->columnSpan(2)
                        ->inlineLabel()
                        ->required(),
                    Select::make('supplier_id')
                        ->relationship('Supplier','name')
                        ->hiddenLabel()

                        ->prefix('المورد')
                        ->prefixIcon('heroicon-m-user')
                        ->prefixIconColor('info')
                        ->afterStateUpdated(function ($state){
                            $this->buy->supplier_id=$state;
                            $this->buy->save();
                        })
                        ->columnSpan(3),

                    Select::make('place_id')
                        ->relationship('Place','name')
                        ->hiddenLabel()
                        ->prefix('مكان التخزين')
                        ->prefixIcon('heroicon-m-home-modern')
                        ->prefixIconColor('warning')
                        ->columnSpan(3)
                        ->inlineLabel()
                        ->disabled()
                        ->visible(Setting::find(Auth::user()->company)->many_place),
                    Select::make('price_type_id')
                        ->relationship('Price_type','name')
                        ->hiddenLabel()
                        ->live()
                        ->prefix('طريقة الدفع')
                        ->prefixIcon('heroicon-m-banknotes')
                        ->prefixIconColor('success')
                        ->afterStateUpdated(function ($state){
                            $this->buy->price_type_id=$state;
                            $this->buy->save();
                            $receipt=Recsupp::find($this->buy->receipt_id);
                            if ($receipt){
                                if ($state==1) $receipt->acc_id=null;
                                $receipt->price_type_id=$state;
                                $receipt->save();
                            }
                            if ($receipt){
                                if ($state!=1) $receipt->kazena_id=null;
                                $receipt->price_type_id=$state;
                                $receipt->save();
                            }
                        })
                        ->columnSpan(2),
                    Select::make('acc_id')
                        ->options(Acc::all()->pluck('name','id'))
                        ->hiddenLabel()
                        ->prefix('الحساب المصرفي')

                        ->prefixIcon('heroicon-m-currency-dollar')
                        ->prefixIconColor('warning')
                        ->columnSpan(2)
                        ->afterStateUpdated(function ($state,Get $get){
                           $receipt=Recsupp::find($this->buy->receipt_id);
                           if ($receipt){
                               $receipt->acc_id=$state;
                               $receipt->save();
                           }
                        })
                        ->dehydrated()
                        ->visible(fn(Get $get): bool =>( $get('pay')>0 && $get('price_type_id')!=1) ),
                    Select::make('kazena_id')
                        ->options(Kazena::all()->pluck('name','id'))
                        ->hiddenLabel()
                        ->prefix('حساب الخزينة')

                        ->prefixIcon('heroicon-m-currency-dollar')
                        ->prefixIconColor('warning')
                        ->columnSpan(2)
                        ->afterStateUpdated(function ($state,Get $get){
                            $receipt=Recsupp::find($this->buy->receipt_id);
                            if ($receipt){
                                $receipt->kazena_id=$state;
                                $receipt->save();
                            }
                        })
                        ->dehydrated()
                        ->visible(fn(Get $get): bool =>( $get('pay')>0 && $get('price_type_id')==1) ),
                    TextInput::make('tot')
                        ->hiddenLabel()
                        ->prefix('اجمالي الفاتورة')
                        ->columnSpan(2)
                        ->mask(RawJs::make('$money($input)'))
                        ->disabled(),
                    TextInput::make('pay')
                        ->hiddenLabel()
                        ->prefix('المدفوع')
                        ->prefixIcon('heroicon-m-hand-thumb-up')
                        ->prefixIconColor('blue')
                        ->columnSpan(2)
                        ->live(onBlur: true)
                        ->inlineLabel()
                        ->default('0')
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'barcode_id' })",
                        ])
                        ->afterStateUpdated(function (Set $set,Get $get,$state){
                            if (!$state) $set('pay',0);
                            $baky=$this->buy->tot-$state;
                            $set('baky', $baky);
                            $this->buy->pay=$state;
                            $this->buy->baky=$baky;
                            $this->buy->save();
                            $this->buyForm->fill($this->buy->toArray());

                            if (!$state || $state<=0)
                                Recsupp::where('buy_id',$this->buy_id,'rec_who'==5)->delete();
                            else {
                                $receipt=Recsupp::where('buy_id',$this->buy_id,'rec_who'==5)->first();
                                if ($receipt)
                                    $receipt->update(['val'=>$this->buy->pay]);
                                else {
                                    Receipt::create([
                                        'receipt_date'=>$this->buy->order_date,
                                        'supplier_id'=>$this->buy->supplier_id,
                                        'buy_id'=>$this->buy->id,
                                        'price_type_id'=>$this->buy->price_type_id,
                                        'rec_who'=>5,
                                        'imp_exp'=>1,
                                        'val'=>$this->buy->pay,
                                        'notes'=>'فاتورة مشتريات رقم '.strval($this->buy->id),
                                        'user_id'=>Auth::id()
                                    ]);

                                }

                            }

                        })
                        ->id('pay'),
                    TextInput::make('baky')
                        ->hiddenLabel()
                        ->prefix('المتبقي')
                        ->columnSpan(2)
                        ->mask(RawJs::make('$money($input)'))
                        ->disabled()
                        ->default('0'),
                    TextInput::make('notes')
                        ->hiddenLabel()
                        ->prefix('ملاحظات')
                        ->afterStateUpdated(function ($state){
                            $this->buy->notes=$state;
                            $this->buy->save();
                        })
                        ->columnSpanFull(),

                ])
                ->columns(8)
                ->collapsible()
                ->reactive()
                ->hidden(function (){
                    return $this->buy_id==null;
                })
        ];
    }

    public function fill_item($item,$barcode){
        $price_buy=Price_buy::where('price_type_id',$this->buyData['price_type_id'])
            ->where('item_id',$item)->first();
        if ($price_buy) $price_input=$price_buy->price;
        else $price_input=Item::find($item)->price_buy;

        $this->buytran=Buy_tran::where('buy_id',$this->buy_id)
            ->where('item_id',$item)->first();
        if ($this->buytran)
            $this->buyTranForm->fill($this->buytran->toArray());
        else $this->buyTranForm->fill([
            'barcode_id'=>$barcode,'item_id'=>$item,'price_input'=>$price_input,'q1'=>'',
            'buy_id'=>$this->buy_id,'user_id'=>Auth::id(),]);
        if ($price_input==0)  $this->dispatch('gotoitem',  test: 'price_input' );
        else $this->dispatch('gotoitem',  test: 'q1' );
    }

    public function ChkBarcode(){
        if ($this->buytranData['barcode_id']==null) return;
        $res=Barcode::find($this->buytranData['barcode_id']);

        if (! $res)
            Notification::make()
                ->title('هذا الباركود غير مخزون ')
                ->icon('heroicon-o-check')
                ->iconColor('success')
                ->send();
        else {
            $item=Item::find($res->item_id);
            $this->fill_item($item->id,$item->barcode);
            $this->dispatch('gotoitem', test: 'q1');
        }
    }
    public function ChkItem(){

        $item=Item::find($this->buytranData['item_id']);
        if (!$item) return;
        $this->fill_item($item->id,$item->barcode);

        $this->dispatch('gotoitem', test: 'q1');
    }
    public function add_rec(){
        $this->validate();

        $q1=$this->buytranData['q1'];
        $p1=$this->buytranData['price_input'];
        $sub=$q1*$p1;

        $this->buytran=Buy_tran::where('buy_id',$this->buy_id)
            ->where('item_id',$this->buytranData['item_id'])->first();
        if ($this->buytran) {
            $this->decAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1);
            $this->buytran->update($this->buyTranForm->getState());
            $this->buytran->update(['sub_input'=>$sub]);
        }
        else
            $this->buytran=Buy_tran::create(
              collect($this->buytranData)
                ->put('qs1',$q1)
                ->put('sub_input',$sub)
                ->except('id')->toArray());

        $this->incAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1
            ,$this->buy->price_type_id,$this->buytran->price_input);


        $this->buyTranForm->fill([]);
        $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
        $baky=$tot-$this->buy->pay;
        $this->buy->tot=$tot;
        $this->buy->baky=$baky;
        $this->buy->save();
        $this->buyForm->fill($this->buy->toArray());

        $this->dispatch('gotoitem', test: 'barcode_id');
    }
    protected function getBuyTranFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('barcode_id')
                      ->hiddenLabel()
                      ->prefix('الباركود')
                        ->required()
                        ->live(onBlur: true)
                      ->exists(Barcode::class,column: 'id')
                        ->extraAttributes([
                            'wire:keydown.enter' => "ChkBarcode",
                        ])
                        ->columnSpanFull()
                        ->id('barcode_id'),

                    Select::make('item_id')
                      ->hiddenLabel()
                      ->prefix('الصنف')
                        ->searchable()
                        ->preload()
                        ->relationship('Item','name')
                        ->live()
                        ->reactive()
                        ->required()
                      ->createOptionForm([
                        Section::make('ادخال صنف')
                          ->schema([
                            TextInput::make('id')
                              ->hidden(fn(string $operation)=>$operation=='create')
                              ->disabled()
                              ->label('الرقم الألي'),
                            TextInput::make('name')
                              ->label('اسم الصنف')
                              ->autocomplete(false)
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
                        ->extraAttributes([
                            'wire:change' => "ChkItem",
                            'wire:keydown.enter' => "ChkItem",
                        ])
                        ->columnSpanFull()
                        ->id('item_id'),
                    DatePicker::make('exp_date')
                        ->label('تاريخ الصلاحية')
                        ->inlineLabel()
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
                        ])
                        ->visible(Setting::find(Auth::user()->company)->has_exp),

                    TextInput::make('price_input')
                      ->hiddenLabel()
                      ->prefix('السعر')
                      ->prefixIcon('heroicon-m-currency-dollar')
                      ->prefixIconColor('info')
                        ->numeric()
                        ->live()
                        ->required()
                        ->id('price_input')
                        ->afterStateUpdated(function (Get $get,Set $set,$state){
                            if ($state && $get('q1'))
                                $set('sub_input',$state*$get('q1'));
                        })

                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
                        ]),

                    TextInput::make('q1')
                      ->hiddenLabel()
                      ->prefix('الكمية')
                      ->prefixIcon('heroicon-m-shopping-cart')
                      ->prefixIconColor('warning')
                        ->numeric()
                        ->required()
                        ->afterStateUpdated(function (Get $get,Set $set,$state){
                            if ($state && $get('price_input'))
                                $set('sub_input',$state*$get('price_input'));
                        })
                        ->extraAttributes([
                            'wire:keydown.enter' => "add_rec",
                        ])
                        ->id('q1'),

                ])
                ->columns(2)
                ->hidden(function (){
                    return $this->buy_id==null;
                }),

        ];
    }
    public function table(Table $table):Table
    {
        return $table
            ->query(function (Buy_tran $buy_tran)  {
                $buy_tran=Buy_tran::where('buy_id',$this->buy_id) ;
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
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('sub_input')
                    ->label('المجموع')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('delete')
                    ->action(function (Buy_tran $record){
                        $this->buytran= $record->delete();
                        $this->decAllBuy($record->item_id,$this->buy->place_id,$record->q1);
                        $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
                        $baky=$tot-$this->buy->pay;
                        $this->buy->tot=$tot;
                        $this->buy->baky=$baky;
                        $this->buy->save();
                        $this->buyForm->fill($this->buy->toArray());
                        $this->buyTranForm->fill([]);
                    })
                    ->icon('heroicon-m-trash')
                    ->iconButton()->color('danger')
                    ->hiddenLabel()
                    ->hidden(function (){
                        return Buy_tran::where('buy_id',$this->buy_id)->count()==1;
                    })
                    ->requiresConfirmation(),
                \Filament\Tables\Actions\Action::make('edit')
                    ->action(function (Buy_tran $record){
                        $this->buyTranForm->fill($record->toArray());
                        $this->dispatch('gotoitem',  test: 'q1' );
                    })
                    ->icon('heroicon-m-pencil')
                    ->iconButton()->color('info')
                    ->hiddenLabel()

            ])


            ->striped()
            ;
    }
}
