<?php

namespace App\Filament\market\Pages;

use App\Enums\PlaceType;
use App\Enums\TwoUnit;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Item;
use App\Models\Kazena;
use App\Models\OurCompany;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Price_sell;
use App\Models\Price_type;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class QueckSell extends Page implements HasSchemas,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    use Raseed;


    protected string $view = 'filament.market.pages.inp-sell';

    protected static ?string $navigationLabel='فاتورة مبيعات سريعة';
    protected static string | \UnitEnum | null $navigationGroup='فواتير مبيعات';
    protected static ?int $navigationSort=1;
    protected ?string $heading='';
    public static function shouldRegisterNavigation(): bool
    {

        return Auth::user()->can('ادخال مبيعات');
    }
    public $sell;
    public $selltran;
    public $sellData;
    public $selltranData;
    public $sellStoreData;
    public $id_to_print='';
    public $barcode_id;
    public $item_id;
    public function mount()
    {

        $this->sell = Sell_work::find(auth()->id());
        if (!$this->sell) {
                if (Auth::user()->place_id) $place_id=Auth::user()->place_id;
                else $place_id=Place::query()->first()->id;
                $this->sell=Sell_work::create([
                    'id'=>Auth::id(),'user_id'=>Auth::id(),'place_id'=>$place_id,'customer_id'=>1,'price_type_id'=>1,]);
        }
        if ($this->sell->customer_id!=1)
        {
            $this->sell->customer_id=1;
            $this->sell->save();
        }

        $kaz=Kazena::where('user_id',auth()->id())->first();
        $kaz_id=null;
        if ($kaz && $this->sell->price_type_id==1)  $kaz_id=$kaz->id;

        $this->sell->order_date=date('Y-m-d');

        $this->sellForm->fill($this->sell->toArray());

        $this->sellTranForm->fill(['q1'=>1,]);
        $this->sellStoreForm->fill(['print'=>true,'kazena_id'=>$kaz_id]);
    }
    public function PrintOrder($id){

        $RepDate=date('Y-m-d');
        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        $res=Sell::find($id);
        $orderdetail=Sell_tran::where('sell_id',$id)->get();


        $html = view('PDF.rep-order-sell',
            ['res'=>$res,'cus'=>$cus,'RepDate'=>$RepDate,'orderdetail'=>$orderdetail])->toArabicHTML();


        $pdf = PDF::loadHTML($html)->output();
        $headers = array(
            "Content-type" => "application/pdf",
        );

        return response()->streamDownload(
            function () use($pdf) {
                echo $pdf; // Echo download contents directly...
            },
            "invoice.pdf",
            $headers
        );

    }
    public function updateSells()
    {
        $this->sell->update($this->sellForm->getState());


        Notification::make()
            ->title('تم تحزين البيانات بنجاح')
            ->success()
            ->send();
    }
    public function updatePriceType(Set $set){
        $this->sell->price_type_id=$this->sellData['price_type_id'] ;
        if ($this->sell->price_type_id==2)
        {
            $this->sellData['rate'] = Price_type::find(2)->rate;

            $this->updateDiffer();
        }
        else {$this->sellData['rate'] = 0;$this->updateNonDiffer();
        }


        if ($this->sell->price_type_id==1)
        {

            $this->sellStoreForm->fill([
                'print'=>true,
                'kazena_id'=>Kazena::where('user_id',Auth::id())->first()->id,
                'acc_id'=>null
            ]);
        }
        if ($this->sell->price_type_id==2)
        {

            $this->sellStoreForm->fill([
                'print'=>true,
                'kazena_id'=>null,

            ]);
        }

        Notification::make()
            ->title('تم تحزين طريقة الدفع بنجاح')
            ->success()
            ->send();

    }
    public function updateNonDiffer(){
        $this->sell->rate=0;
        $this->sell->differ=0;
        $this->sell->total=$this->sell->tot+$this->sell->cost;
        $this->sell->baky=$this->sell->total-$this->sell->pay;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());

    }
    public function updateDiffer(){
        $this->sell->rate=$this->sellData['rate'];
        $this->sell->differ=($this->sell->tot+$this->sell->cost)*$this->sell->rate/100;
        $this->sell->total=$this->sell->tot+$this->sell->cost+$this->sell->differ;
        $this->sell->baky=$this->sell->total-$this->sell->pay;

        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
        Notification::make()
            ->title('تم تحزين البيانات بنجاح')
            ->success()
            ->send();
    }
    public function sellForm(Schema $schema): Schema
    {
        return $schema
            ->model(Sell_work::class)
            ->statePath('sellData')
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('order_date')
                            ->id('order_date')
                            ->hiddenLabel()
                            ->prefix('التاريخ')
                            ->columnSpan(2)
                            ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                            ->required(),
                        Select::make('price_type_id')
                            ->hiddenLabel()
                            ->prefix('طريقة الدفع')
                            ->columnSpan(2)
                            ->live()
                            ->relationship('Price_type','name')
                            ->required()
                            ->extraAttributes(['x-on:change' => "\$wire.updatePriceType"])
                            ->id('price_type_id'),

                        Select::make('place_id')
                            ->hiddenLabel()
                            ->prefix('نقطة البيع')
                            ->relationship('Place','name')
                            ->disabled(function (){
                                return Sell_tran_work::where('sell_id',$this->sell->id)->exists();
                            })
                            ->live()
                            ->required()
                            ->columnSpan(4)
                            ->extraAttributes(['x-on:change' => "\$wire.updateSells"])
                            ->id('place_id')
                            ->visible(Setting::find(Auth::user()->company)->many_place ),

                        TextInput::make('total')
                            ->hiddenLabel()
                            ->prefix('الإجمالي النهائي')
                            ->columnSpan(2)
                            ->readOnly()
                            ->default('0'),

                    ])
                    ->columns(10)
            ]);
    }
    public function sellTranForm(Schema $schema): Schema
    {
        return $schema
            ->model(Sell_tran_work::class)
            ->statePath('selltranData')
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('barcode_id')
                            ->hiddenLabel()
                            ->prefix('الباركود')
                            ->columnSpan(2)
                            ->required()
                            ->exists(Barcode::class,column: 'id')
                            ->live()
                            ->extraInputAttributes(['wire:keydown.enter' => 'ChkBarcode($event.target.value)',])
                            ->autocomplete(false)
                            ->autofocus()
                            ->id('barcode_id'),
                        Select::make('item_id')
                            ->hiddenLabel()
                            ->prefix('الصنف')
                            ->columnSpan(2)
                            ->searchable()
                            ->preload()
                            ->relationship('Item','name',
                                modifyQueryUsing: fn (Builder $query) =>
                                $query->whereIn('id',Place_stock::where('place_id',$this->sellData['place_id'])
                                    ->where('stock1','>',0)->pluck('item_id'))
                            )
                            ->live(onBlur: true)
                            ->required()
                            ->afterStateUpdated(function ($state){$this->ChkItem($state);})
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
                            //->readOnly(fn():bool => ! Auth::user()->hasRole('admin'))
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
                            ->gte(0)
                            ->afterStateUpdated(function (Set $set,Get $get,$state){
                                if ($get('item_id')==null) return;
                                if ($get('q1')==null) $set('q1',0);
                                $set('sub_tot',($get('q1')*$get('price1') ));
                            })
                            ->extraAttributes(['wire:keydown.enter' => "add_rec",])
                            ->id('q1'),
                    ])
                    ->columns(2),
            ]);
    }
    public function sellStoreForm(Schema $schema): Schema
    {
        return $schema
            ->model(Receipt::class)
            ->statePath('sellStoreData')
            ->components([
                Section::make()
                    ->schema([
                        Select::make('acc_id')
                            ->relationship('Acc','name')
                            ->label('المصرف')
                            ->inlineLabel()
                            ->visible(function (){
                                return $this->sell->tot>0 && $this->sell->price_type_id==2;
                            }),
                        Select::make('kazena_id')
                            ->preload()
                            ->searchable()
                            ->relationship('Kazena','name')
                            ->label('الخزينة')
                            ->inlineLabel()
                            ->disabled(function (){
                                return Kazena::where('user_id',Auth::id())->exists();
                            })
                            ->visible(function (){
                                return  $this->sell->price_type_id==1;
                            }),
                        Actions::make([
                            Action::make('store')
                                ->label('تخزين')
                                ->icon('heroicon-m-plus')
                                ->button()
                                ->visible(function (){return Sell_tran_work::where('sell_id',Auth::id())->count()>0;})
                                ->color('success')
                                ->requiresConfirmation()
                                ->action(function () {

                                    if ($this->sell->order_date==null)
                                    {
                                        Notification::make()
                                            ->title('يجب ادخال التاريخ')
                                            ->icon('heroicon-o-exclamation-triangle')
                                            ->iconColor('warning')
                                            ->send();
                                        return false;
                                    }
                                    if ($this->sell->price_type_id==null)
                                    {
                                        Notification::make()
                                            ->title('يجب ادخال طريقة الدفع')
                                            ->icon('heroicon-o-exclamation-triangle')
                                            ->iconColor('warning')
                                            ->send();
                                        return false;
                                    }
                                    $selltran=Sell_tran_work::with('Item')->where('sell_id',Auth::id())->get();
                                    $minus=false;
                                    foreach ($selltran as $tran) {
                                        $placeRaseed=Place_stock::where('item_id',$tran->item_id)
                                            ->where('place_id',$this->sell->place_id)->first();

                                        if ($tran->q1 > $placeRaseed->stock1                                        ){
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

                                    if ($this->sell->pay>0 && $this->sell->price_type_id==2) {
                                        if (!$this->sellStoreData['acc_id'])
                                        {
                                            Notification::make()->title('يجب اختيار المصرف ')->color('danger')->icon('heroicon-m-no-symbol')->send();return;
                                        }
                                        $acc=$this->sellStoreData['acc_id'];
                                    }
                                    else $acc=null;
                                    if ($this->sell->pay>0 && $this->sell->price_type_id==1) {
                                        if (!$this->sellStoreData['kazena_id'])
                                        {
                                            Notification::make()->title('يجب اختيار الخزينة ')->color('danger')->icon('heroicon-m-no-symbol')->send();return;
                                        }
                                        $kaz=$this->sellStoreData['kazena_id'];
                                    }
                                    else $kaz=null;
                                    unset($this->sell['id'],$this->sell['created_at'],$this->sell['updated_at']);
                                    $id=Sell::create($this->sell->toArray());

                                    $selltran=Sell_tran_work::where('sell_id',Auth::id())->get();
                                    foreach ($selltran as $tran) {
                                        $tran->sell_id=$id->id;
                                        unset($tran['id'],$tran['created_at'],$tran['updated_at']);
                                        $tran_id=Sell_tran::create($tran->toArray());

                                        $this->decAll($tran_id->id,$id->id,$tran->item_id,$id->place_id,$tran->q1,0);
                                        if (! Price_sell::where('item_id',$tran->item_id)->where('price_type_id',$this->sell->price_type_id)->first())
                                            Price_sell::create(['item_id'=>$tran->item_id,'price_type_id'=>$this->sell->price_type_id
                                                ,'price1'=>$tran->price1,'price2'=>$tran->price2,'pricej1'=>$tran->price1,'pricej2'=>$tran->price2,]);
                                        // $this->setPriceSell($tran->item_id,$this->sell->price_type_id,$this->sell->single,$tran->price1,$tran->price2);
                                    }
                                    if ($this->sell->pay>0)

                                        Receipt::create([
                                            'receipt_date'=>$this->sell->order_date,
                                            'customer_id'=>$this->sell->customer_id,
                                            'sell_id'=>$id->id,
                                            'price_type_id'=>$this->sell->price_type_id,
                                            'rec_who'=>6,
                                            'imp_exp'=>0,
                                            'val'=>$this->sell->pay,
                                            'kazena_id'=>$kaz,
                                            'acc_id'=>$acc,
                                            'place_id'=>$this->sell->place_id,
                                            'notes'=>'فاتورة مبيعات رقم '.strval($id->id),
                                            'user_id'=>Auth::id()
                                        ]);

                                    $this->sell=Sell_work::find(Auth::id());
                                    $this->sell->tot=0;  $this->sell->pay=0; $this->sell->baky=0;$this->sell->total=0;
                                    $this->sell->differ=0;$this->sell->cost=0;
                               //     $this->sell->customer_id=null;
                                //    $this->sell->order_date=null;
                             //       $this->sell->price_type_id=null;
                                    $this->sell->notes=null;
                                    $this->sell->save();
                                    $this->sellForm->fill($this->sell->toArray());
                                    $this->selltran= Sell_tran_work::where('sell_id', Auth::id())->delete();
                                    $this->sellTranForm->fill([]);
                                    $this->id_to_print=$id->id;
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



                                }),
                            Action::make('print')
                                ->icon('heroicon-o-printer')
                                ->hidden($this->id_to_print=='')
                                ->iconButton()

                                ->color('blue')
                                ->url(fn (): string => route('pdfsell', ['id' => $this->id_to_print]))

                        ])->extraAttributes(['class' => 'items-center justify-between']),

                    ])
            ]);
    }
    public function sub_tot(){
        $this->selltran->sub_tot=$this->selltran->q1*$this->selltran->price1;
        $this->selltran->save();
        $this->sellTranForm->fill(['q1'=>1]);
    }
    public function tot(){
        $tot=Sell_tran_work::where('sell_id',Auth::id())->sum('sub_tot');
        $this->sell->tot=$tot;
        $this->sell->differ=($tot+$this->sell->cost)*$this->sell->rate/100;
        $total=$tot+$this->sell->differ;
        $this->sell->total=$total;
        $this->sell->baky=0;
        $this->sell->pay=$total;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
    }
    public function retPrice($Item,$price_type){

        $Price_type=Price_type::find($price_type);

        if ($Price_type->inc_dec->value==0) return $Item->price1;
        if ($Price_type->inc_dec->value==1) {
            if ($Price_type->val != 0) return $Item->price1 + $Price_type->val;
            else return $Item->price1+(($Price_type->rate*$Item->price1)/100);
        }
        if ($Price_type->inc_dec->value==2)
        {
            if ($Price_type->val!=0) return $Item->price1-$Price_type->val;
            else return $Item->price1-(($Price_type->rate*$Item->price1)/100);
        }

    }

    public function fill_item($item_id,$barcode){
        $item=Item::find($item_id);
        $price=$this->retPrice($item,$this->sellData['price_type_id']);

        $stock=Place_stock::where('item_id',$item_id)
            ->where('place_id',$this->sellData['place_id'])->first();
        if ($stock) $placestock=$stock->stock1;else $placestock=0;

        $q=$this->selltranData['q1'];

        $this->selltran=Sell_tran_work::where('sell_id',Auth::id())
            ->where('item_id',$item_id)->first();

        if ($this->selltran) {
            $q+=$this->selltran->q1;
            $price=$this->selltran->price1;
        }

        $this->chkData();

         $this->sellTranForm->fill([
            'barcode_id'=>$barcode,'item_id'=>$item_id,
            'price1'=>$price,'q1'=>$q,
            'sub_tot'=>0,
            'raseed_all'=>$item->stock1,
            'raseed_place'=>$placestock,
            'sell_id'=>Auth::id(),
            'user_id'=>Auth::id()]);


    }

    public function ChkBarcode($bar){
        if ($bar==null) return;
        $res=Barcode::find($bar);
        if (! $res)
            Notification::make()
                ->title('هذا الباركود غير مخزون ')
                ->icon('heroicon-o-check')
                ->iconColor('success')
                ->send();
        else {
            $this->barcode_id=$bar;
            $item=$res->Item;
            $this->item_id=$item->id;
            $this->chkData();
        }
    }
    public function ChkItem($state){
        $this->item_id=null;
        if ($state==null) return ;
        $res=Item::find($state);
        if (!$res) return ;
        $this->item_id=$state;
        $this->barcode_id=$res->barcode;
        $this->chkData();
    }
    public function chkData(){
        if (! $this->item_id)
        {
            $this->dispatch('gotoitem','barcode_id');
            return;
        }

        $item=Item::find($this->item_id);

        if (!$this->sellData['price_type_id']) {
            Notification::make()->title('يجب اختيار طريقة دفع')->danger()->send();
            return;
        }

        $place_id=$this->sellData['place_id'];
        $q1=$this->selltranData['q1'];
        if  ($q1==null || $q1<=0)
        {
            Notification::make()->title('يجب ادخال الكمية')->danger()->send();
            $this->dispatch('focus-next',next: 'q1');
            return ;
        }

        $this->selltran=Sell_tran_work::where('sell_id',Auth::id())
            ->where('item_id',$this->item_id)->first();
        if ($this->selltran) $q1+=$this->selltran->q1;

        $res=Place_stock::where('item_id',$this->item_id)
            ->where('place_id',$place_id)->first();
        if (!$res)
        {
            Notification::make()->title(' الصنف غير مخزون في نقطة البيع هذه !!')->danger()->send();
            return ;
        }
        $placestock=$res->stock1;
        if ($placestock<$q1)
        {
            Notification::make()->title('الرصيد لا يسمح !!')->danger()->send();
            return ;
        }

        $price=$this->retPrice($item,$this->sellData['price_type_id']);
        $this->sellTranForm->fill([
                'barcode_id'=>$this->barcode_id,'item_id'=>$item->id,
                'price1'=>$price,'q1'=>$q1,
                'sub_tot'=>0,
                'raseed_all'=>$item->stock1,
                'raseed_place'=>$placestock,
                'sell_id'=>Auth::id(),
                'user_id'=>Auth::id()
            ]
        );
        if ($this->selltran)
            $this->selltran->update($this->sellTranForm->getState());
        else
            $this->selltran=Sell_tran_work::create(collect($this->selltranData)->except(['id','raseed_place','raseed_all'])->toArray());

        $this->sub_tot();
        $this->tot();
        $this->item_id=null;
        $this->barcode_id=null;

        $this->dispatch('focus-next', first: 'barcode', next:'item_id');

    }
    public function add_rec(){

        $this->validate();

        $place_id=$this->sellData['place_id'];

        $chk=$this->chkData($place_id);
        if ($chk != 'ok') {
            Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
            return;
        }

        if ($this->selltran)
            $this->selltran->update($this->sellTranForm->getState());
        else
            $this->selltran=Sell_tran_work::create(collect($this->selltranData)->except(['id','raseed_place','raseed_all'])->toArray());

        $this->sub_tot();
        $this->tot();

        $this->dispatch('gotoitem', test: 'barcode_id');
    }
    public function is_two(){
        if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {
            return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
        else return false;
    }
    public function table(Table $table):Table
    {
        return $table
            ->query(function () {
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
                    ->numeric()
                    ->action(
                        Action::make('updateQuantity')
                            ->fillForm(fn($record)=>['q1'=>$record->q1])
                            ->schema([
                                   Text::make(fn($record) =>new HtmlString(

                                       '<br><strong class="text-indigo-700">الرصيد : </strong> '.Place_stock::query()
                                            ->where('place_id',$this->sellData['place_id'])
                                            ->where('item_id',$record->item_id)->first()->stock1
                                       )
                                   ),
                                   TextInput::make('q1')
                                       ->label('الكمية الجديدة')
                                       ->required()
                                       ->minValue(1),

                            ])
                            ->modalWidth(Width::Medium)

                            ->modalHeading('')
                            ->action(function ($record,array $data){
                                if ($data['q1']<=0)
                                {
                                    Notification::make()->title('يجب ادخال الكمية')->danger()->send();
                                    return;
                                }

                                if ($data['q1']>Place_stock::query()
                                        ->where('item_id',$record->item_id)
                                        ->where('place_id',$this->sellData['place_id'])
                                        ->first()->stock1)
                                {
                                    Notification::make()->title('الرصيد لا يسمح !!')->danger()->send();
                                    return ;
                                }

                                $record->q1=$data['q1'];
                                $record->sub_tot=$data['q1']*$record->price1;
                                $record->save();
                                $this->tot();
                                $this->dispatch('gotoitem', test: 'barcode_id');

                            })
                    )
                    ->label('الكمية'),
                TextColumn::make('price1')
                    ->label('سعر البيع')
                    ->numeric(),
                TextColumn::make('sub_tot')
                    ->label('المجموع')
                    ->summarize(Sum::make()->label(''))
                    ->numeric(),
            ])
            ->recordActions([
                Action::make('delete')
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
