<?php

namespace App\Filament\Resources\SellWorkResource\Pages;

use App\Enums\PlaceType;
use App\Enums\TwoUnit;
use App\Filament\Resources\SellWorkResource;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;

use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Price_sell;
use App\Models\Price_type;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CreateSell extends Page
    implements HasTable
{
    use InteractsWithTable;
    use Raseed;

    protected static string $resource = SellWorkResource::class;

    protected static string $view = 'filament.resources.sell-work-resource.pages.create-sell';
    protected ?string $heading="";

    public $sell;
    public $selltran;
    public $sellData;
    public $selltranData;
    public $sellStoreData;

    public $collapse=false;

    public function mount()
    {
        $this->sell = Sell_work::find(auth()->id());
        if (!$this->sell)
            $this->sell=Sell_work::create([
                'id'=>Auth::id(),'user_id'=>0,
            ]);
        $this->sellForm->fill($this->sell->toArray());

        $this->sellTranForm->fill([]);
        $this->sellStoreForm->fill([]);
    }
    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "sellForm" => $this->makeForm()
                ->model(Sell_work::class)
                ->schema($this->getsellFormSchema())
                ->statePath('sellData'),
            "sellTranForm" => $this->makeForm()
                ->model(Sell_tran_work::class)
                ->schema($this->getsellTranFormSchema())
                ->statePath('selltranData'),
            "sellStoreForm" => $this->makeForm()
                ->model(Receipt::class)
                ->schema($this->getsellStoreFormSchema())
                ->statePath('sellStoreData'),

        ]);
    }
    public function updateSells()
    {
        $this->sell->update($this->sellForm->getState());
    }
    public function updatePay()
    {
        $this->sell->update($this->sellForm->getState());
        $this->sell->baky=$this->sell->tot-$this->sell->pay;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
    }

    protected function getSellFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    DatePicker::make('order_date')
                        ->id('order_date')
                        ->autofocus()
                        ->hiddenLabel()
                        ->prefix('التاريخ')
                        ->columnSpan(2)

                        ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                        ->required(),
                    Select::make('customer_id')
                        ->hiddenLabel()
                        ->prefix('الزبون')
                        ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                        ->relationship('Customer','name')
                        ->live()
                        ->required()

                        ->columnSpan(3)
                        ->createOptionForm([
                            Section::make('ادخال زبون جديد')
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
                        ->id('customer_id'),
                    Select::make('place_id')
                        ->hiddenLabel()
                        ->prefix('مكان التخزين')
                        ->relationship('Place','name')
                        ->live()
                        ->required()

                        ->columnSpan(3)
                        ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
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
                        ->hiddenLabel()
                        ->prefix('طريقة الدفع')
                        ->columnSpan(2)
                        ->live()
                        ->default(1)
                        ->relationship('Price_type','name')
                        ->required()
                        ->extraAttributes(['x-on:change' => "\$wire.updatesells"])
                        ->id('price_type_id'),
                    TextInput::make('tot')
                        ->hiddenLabel()
                        ->prefix('اجمالي الفاتورة')
                        ->columnSpan(2)
                        ->readOnly(),
                    TextInput::make('pay')
                        ->hiddenLabel()
                        ->prefix('المدفوع')
                        ->columnSpan(2)
                        ->extraAttributes(['x-on:change' => "\$wire.updatePay"])
                        ->live(onBlur: true)
                        ->default('0')
                        ->id('pay'),
                    TextInput::make('baky')
                        ->hiddenLabel()
                        ->prefix('المتبقي')
                        ->columnSpan(2)
                        ->readOnly()
                        ->default('0'),
                ])
                ->columns(8)
                ->collapsible()
                ->collapsed(fn() :bool=>$this->collapse)
        ];
    }

    public function sub_tot(){
        $this->selltran->sub_tot=$this->selltran->q1*$this->selltran->price1;
        $this->selltran->save();
        $this->sellTranForm->fill([]);
    }
    public function tot(){
        $tot=Sell_tran_work::where('sell_id',Auth::id())->sum('sub_tot');
        $baky=$tot-$this->sell->pay;
        $this->sell->tot=$tot;
        $this->sell->baky=$baky;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
    }
    public function retPrice($item,$single,$price_type){

        $Item=Item::find($item);
        $Price_type=Price_type::find($price_type);

        if ($Price_type->inc_dec->value==0)
        {

            $rec=Price_sell::where('item_id',$item)->where('price_type_id',$price_type)->first();

            if ($rec) {
                if ($single) return ['price1'=>$rec->price1,'price2'=>$rec->price2];
                else return ['price1'=>$rec->pricej1,'price2'=>$rec->pricej2];
            } else {
                if ($single) return  ['price1'=>$Item->price1,'price2'=>$Item->price2];
                else return  ['price1'=>$Item->pricej1,'price2'=>$Item->pricej2];
            }
        }
        if ($Price_type->inc_dec->value==1)
        {
            if ($Price_type->val!=0) {
                if ($single) return [
                    'price1'=>$Item->price1+$Price_type->val,
                    'price2'=>$Item->price2+$Price_type->val];
                else return [
                    'price1'=>$Item->pricej1+$Price_type->val,
                    'price2'=>$Item->pricej2+$Price_type->val,];
            } else {
                if ($single) return  [
                    'price1'=>$Item->price1+(($Price_type->rate*$Item->price1)/100),
                    'price2'=>$Item->price2+(($Price_type->rate*$Item->price2)/100),];
                else return  [
                    'price1'=>$Item->pricej1+(($Price_type->rate*$Item->pricej1)/100),
                    'price2'=>$Item->pricej2+(($Price_type->rate*$Item->pricje2)/100),];
            }
        }
        if ($Price_type->inc_dec->value==2)
        {
            if ($Price_type->val!=0) {
                if ($single) return [
                    'price1'=>$Item->price1-$Price_type->val,
                    'price2'=>$Item->price2-$Price_type->val];
                else return [
                    'price1'=>$Item->pricej1-$Price_type->val,
                    'price2'=>$Item->pricej2-$Price_type->val,];
            } else {
                if ($single) return  [
                    'price1'=>$Item->price1-(($Price_type->rate*$Item->price1)/100),
                    'price2'=>$Item->price2-(($Price_type->rate*$Item->price2)/100),];
                else return  [
                    'price1'=>$Item->pricej1-(($Price_type->rate*$Item->pricej1)/100),
                    'price2'=>$Item->pricej2-(($Price_type->rate*$Item->pricje2)/100),];
            }
        }

    }
    public function fill_item($item_id,$barcode){


        $item=Item::find($item_id);

        $rec=$this->retPrice($item_id,$item->single,$this->sellData['price_type_id']);
        if ($rec['price1']==0)$rec['price1']='';

        $stock=Place_stock::where('item_id',$item_id)
            ->where('place_id',$this->sellData['place_id'])->first();
        if ($stock) $placestock=$stock->stock1;else $placestock=0;

        $this->selltran=Sell_tran_work::where('sell_id',Auth::id())
            ->where('item_id',$item_id)->first();

        if ($this->selltran)
            $this->sellTranForm->fill($this->selltran->toArray());
        else $this->sellTranForm->fill([
            'barcode_id'=>$barcode,'item_id'=>$item,
            'price1'=>$rec['price1'],'price2'=>$rec['price2'],'q1'=>'','q2'=>'',
            'raseed_all'=>$item->stock1,
            'raseed_place'=>$placestock,
            'sell_id'=>Auth::id(),'user_id'=>Auth::id()]);
        if ($rec['price1']=='')  $this->dispatch('gotoitem',  test: 'price1' );
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
            $this->dispatch('gotoitem', test: 'q1');
        }
    }
    public function ChkItem($state){
        $this->collapse=true;

        if ($state==null) return;
        $res=Item::find($state);
        if (!$res) return;
        $this->fill_item($state,$res->barcode);
    }
    public function chkQuant(){
        if ($this->is_two())
            $this->dispatch('goto', test: 'q2');
        else $this->add_rec();
    }
    public function chkData(){
        if (! $this->selltranData['item_id']) return 'يجب ادخال الصنف';
        $item_id=$this->selltranData['item_id'];
        $has_two=Setting::find(Auth::user()->company)->has_two && Item::find($item_id)->two_unit;
        if (!$has_two && $this->selltranData['q1']<=0) return 'يجب ادخال الكمية';
        if ($has_two &&  $this->selltranData['q2']<=0 && $this->selltranData['q1']<=0) return 'يجب ادخال الكمية';

        if ($this->retRaseedTwo($item_id,$this->sellData['place_id'])<=0) return 'الرصيد لا يسمح !!';
        return 'ok';
    }

    public function add_rec(){
        $this->validate();
        $place_id=$this->sellData['place_id'];
        $chk=$this->chkData($place_id);
        if ($chk != 'ok') {
            Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
            return;
        }

        $this->selltran=Sell_tran_work::where('item_id',$this->selltranData['item_id'])->first();
        if ($this->selltran)
            $this->selltran->update($this->sellTranForm->getState());
        else
            $this->selltran=Sell_tran_work::create(collect($this->selltranData)->except('id')->toArray());
        $this->sub_tot();
        $this->tot();

        $this->dispatch('gotoitem', test: 'barcode_id');
    }

    public function is_two(){
        if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {
            return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
        else return false;
    }

    protected function getsellTranFormSchema(): array
    {
        return [
            Section::make()
                ->schema([

                    TextInput::make('barcode_id')
                        ->hiddenLabel()
                        ->prefix('الباركود')
                        ->columnSpan(2)
                        ->required()
                        ->exists(Barcode::class,column: 'id')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state){$this->ChkBarcode($state);})
                        ->extraAttributes(['wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",])
                        ->autocomplete(false)
                        ->id('barcode_id'),

                    Select::make('item_id')
                        ->hiddenLabel()
                        ->prefix('الصنف')

                        ->columnSpan(2)
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
                                    TextInput::make('price_sell')
                                        ->label('سعر الشراء')
                                        ->required()
                                        ->id('price_sell'),
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
                    TextInput::make('raseed_all')
                        ->hiddenLabel()
                        ->prefix('الرصيد الكلي')
                        ->disabled(),
                    TextInput::make('raseed_place')
                        ->hiddenLabel()
                        ->prefix('رصيد المكان')
                        ->disabled(),


                    TextInput::make('price1')
                        ->hiddenLabel()
                        ->prefix('السعر')
                        ->prefixIcon('heroicon-m-currency-dollar')
                        ->prefixIconColor('info')
                        ->numeric()
                        ->live()
                        ->required()
                        ->gt(0)
                        ->id('price1')
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
                        ]),
                    TextInput::make('price2')
                        ->hiddenLabel()
                        ->numeric()
                        ->live()
                        ->required()
                        ->gt(0)
                        ->id('price2')
                        ->visible(function (){
                            return $this->is_two();
                        })
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
                        ]),
                    TextInput::make('q1')
                        ->hiddenLabel()
                        ->prefix('الكمية')
                        ->prefixIcon('heroicon-m-shopping-cart')
                        ->prefixIconColor('warning')

                        ->numeric()
                        ->required()
                        ->gte(0)
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
                        ->gte(0)
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

        ];
    }

    protected function getSellStoreFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Select::make('acc_id')
                        ->relationship('Acc','name')
                        ->label('المصرف')
                        ->inlineLabel()
                        ->visible(function (){
                            $record=Sell_work::find(Auth::id());
                            return $record->pay>0 && $record->price_type_id==2;
                        }),
                    \Filament\Forms\Components\Actions::make([
                        Action::make('store')
                            ->label('تخزين')
                            ->icon('heroicon-m-plus')
                            ->button()
                            ->visible(function (){return Sell_tran_work::where('sell_id',Auth::id())->count()>0;})
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(function () {
                                $selltran=Sell_tran_work::where('sell_id',Auth::id())->get();
                                if ($this->sell->pay>0 && $this->sell->price_type_id!=1) {
                                    if (!$this->sellStoreData['acc_id'])
                                    {
                                        Notification::make()->title('يجب اختيار المصرف ')->color('danger')->icon('heroicon-m-no-symbol')->send();return;
                                    }
                                    $acc=$this->sellStoreData['acc_id'];
                                }
                                else $acc='';


                                unset($this->sell['id']);
                                $id=Sell::create($this->sell->toArray());
                                foreach ($selltran as $tran) {
                                    $tran->sell_id=$id->id;
                                    $tran->qs1=$tran->q1;
                                    unset($tran['id']);
                                    Sell_tran::create($tran->toArray());
                                    $this->incAllsell($tran->item_id,$this->sell->place_id,$tran->q1,$this->sell->price_type_id,$tran->price_input);

                                }
                                if ($this->sell->pay>0){

                                    $recipt= Receipt::create([
                                        'receipt_date'=>$this->sell->order_date,
                                        'supplier_id'=>$this->sell->supplier_id,
                                        'sell_id'=>$id->id,
                                        'price_type_id'=>$this->sell->price_type_id,
                                        'rec_who'=>4,
                                        'imp_exp'=>1,
                                        'val'=>$this->sell->pay,
                                        'acc_id'=>$acc,
                                        'notes'=>'فاتورة مشتريات رقم '.strval($id->id),
                                        'user_id'=>Auth::id()
                                    ]);
                                    Sell::find($id->id)->update(['receipt_id'=>$recipt->id]);
                                }
                                $this->sell=Sell_work::find(Auth::id());
                                $this->sell->tot=0;  $this->sell->pay=0; $this->sell->baky=0;  $this->sell->save();
                                $this->sellForm->fill($this->sell->toArray());
                                $this->selltran= Sell_tran_work::where('sell_id', Auth::id())->delete();
                                $this->sellTranForm->fill([]);
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

                                $this->sell->tot = 0;
                                $this->sell->pay = 0;
                                $this->sell->baky = 0;
                                $this->sell->save();
                                $this->sellForm->fill($this->sell->toArray());
                                $this->selltran= Sell_tran_work::where('sell_id', Auth::id())->delete();
                                $this->sellTranForm->fill([]);

                            })
                    ])->extraAttributes(['class' => 'items-center justify-between']),

                ])


        ];
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function (Sell_tran_work $sell_tran) {
                $sell_tran = Sell_tran_work::where('sell_id', Auth::id());
                return $sell_tran;
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
                TextColumn::make('q2')
                    ->label('صغري')
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->formatStateUsing(function (string $state) {
                        if ($state=='0') return '';
                        return $state;
                    }),
                TextColumn::make('price1')
                    ->label('سعر البيع')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),

                TextColumn::make('price2')
                    ->label('سعر الصغري')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->formatStateUsing(function (string $state) {
                        if ($state=='0.0') return '';
                        return $state;
                    }),
                TextColumn::make('price1')
                    ->label('سعر الشراء')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('sub_tot')
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
                    ->action(function (Sell_tran_work $record) {
                        $record->delete();
                        $this->tot();
                        $this->sellTranForm->fill([]);
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
