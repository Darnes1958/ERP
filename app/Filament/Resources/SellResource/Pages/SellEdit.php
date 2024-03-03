<?php

namespace App\Filament\Resources\SellResource\Pages;

use App\Filament\Resources\SellResource;
use App\Models\Acc;

use App\Models\Barcode;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Price_type;
use App\Models\Receipt;

use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SellEdit extends Page implements HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable;
    use \Filament\Resources\Pages\Concerns\InteractsWithRecord;
    use \App\Livewire\Traits\Raseed;

    protected static string $resource = SellResource::class;

    protected static string $view = 'filament.resources.sell-resource.pages.sell-edit';

    protected ?string $heading='';

    public $sell;
    public $selltran;
    public $sellData;
    public $selltranData;
    public $collapse=false;

    public $sell_id;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->sell_id=$this->record->id;
        $this->sell=Sell::find($this->sell_id);

        $this->sellForm->fill($this->record->toArray());
        if ($this->sell->pay!=0){
            $receipt=Receipt::where('sell_id',$this->sell->id)->first();

            if ($receipt->acc_id)
                $this->sellForm->fill(collect($this->record)->put('acc_id',$receipt->acc_id)->toArray());
        }

        $this->sellTranForm->fill([]);
    }

    public function is_two(){
        if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {
            return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
        else return false;
    }

    protected function getForms(): array
    {

        return array_merge(parent::getForms(), [
            "sellForm" => $this->makeForm()
                ->model(Sell::class)
                ->schema($this->getSellFormSchema())
                ->statePath('sellData'),
            "sellTranForm" => $this->makeForm()
                ->model(Sell_tran::class)
                ->schema($this->getSellTranFormSchema())
                ->statePath('selltranData'),
        ]);
    }

    public function chkQuant(){
        if ($this->is_two())
            $this->dispatch('gotoitem', test: 'q2');
        else $this->add_rec();
    }

    public function itemFill($item,$barcode,$stock1){

        $rec=$this->retPrice($item,$this->sell->single,$this->sell->price_type_id);
        $p1=$rec['price1'];
        $p2=$rec['price2'];
        if ($p1==0) $p1=null;
        if ($p2==0) $p2=0;
        $this->sellTranForm->fill([
            'item_id'=>$item,
            'price1'=>$p1,
            'price2'=>$p2,
            'barcode_id'=>$barcode,
            'raseed_all'=>$stock1,
            'raseed_place'=>Place_stock::where('item_id',$item)
                ->where('place_id',$this->sell->place_id)->first()->stock1,

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
            $this->dispatch('gotoitem', test: 'q1');
        }
    }
    public function ChkItem(){
        $res=Item::find($this->selltranData['item_id']);

        $this->itemFill($res->id,$res->barcode,$res->stock1);
        if ($res->price1==0) $this->dispatch('gotoitem', test: 'price1');
        else  $this->dispatch('gotoitem', test: 'q1');

    }
    public function chkDataEdit($place_id,$item_id,$q1,$q2){
        $res=Sell_tran::where('sell_id',$this->sell_id)->where('item_id',$item_id)->first();
        if ($res) {
            $q1 -= $res->q1;
            $q2 -= $res->q2;
        }

          if (!$this->chkRaseed($item_id,$place_id,$q1,$q2)) return 'الرصيد لا يسمح !!';

        return 'ok';
    }

    public function add_rec()
    {
        $this->validate();
        $place_id=$this->sell->place_id;
        $item_id=$this->selltranData['item_id'];
        $q1=$this->selltranData['q1'];
        $q2=$this->selltranData['q2'];

        $chk=$this->chkDataEdit($place_id,$item_id,$q1,$q2);
        if ($chk != 'ok') {
            Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
            return;
        }

        $quant=$this->retSetQuant($item_id,$q1,$q2);

        $res = Sell_tran::where('sell_id', $this->sell_id)->where('item_id', $item_id)->first();
        if ($res){
            $this->incAll($this->sell_id,$item_id,$place_id,$res->q1,$res->q2);
            $res->delete();}

        $sub=($q1*$this->selltranData['price1'])+($q2*$this->selltranData['price2']);
        $this->selltran=Sell_tran::create(collect($this->selltranData)
            ->put('sub_tot',$sub)
            ->put('sell_id',$this->sell_id)
            ->put('user_id',Auth::id())
            ->except('id')
            ->except('raseed_all')
            ->except('raseed_place')
            ->toArray());

        $this->decAll($this->selltran->id,$this->sell_id,$item_id,$place_id,$q1,$q2);


        $this->sellTranForm->fill([]);
        $tot = Sell_tran::where('sell_id', $this->sell_id)->sum('sub_tot');
        $this->sell->tot=$tot;
        $this->sell->differ=($this->sell->tot+$this->sell->cost)*$this->sell->rate/100;
        $this->sell->total=$tot+$this->sell->differ+$this->sell->cost;
        $this->sell->baky=$this->sell->total-$this->sell->pay;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
      if ($this->sell->pay!=0){
        $receipt=Receipt::where('sell_id',$this->sell->id)->first();

        if ($receipt->acc_id)
          $this->sellForm->fill(collect($this->sell)->put('acc_id',$receipt->acc_id)->toArray());
      }
        $this->dispatch('gotoitem', test: 'barcode_id');
    }

    public function updatePay()
    {
        $this->sell->update($this->sellForm->getState());
        $this->sell->total=$this->sell->tot+$this->sell->cost+$this->sell->differ;
        $this->sell->baky=$this->sell->total-$this->sell->pay;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
      if ($this->sell->pay!=0){
        $receipt=Receipt::where('sell_id',$this->sell->id)->first();

        if ($receipt->acc_id)
          $this->sellForm->fill(collect($this->record)->put('acc_id',$receipt->acc_id)->toArray());
      }
    }

    public function updatePriceType(){
        $this->sell->price_type_id=$this->sellData['price_type_id'] ;
        if ($this->sell->price_type_id==2)
        {$this->sellData['rate'] = Price_type::find(2)->rate;
            $this->updateDiffer();
        }
        else {$this->sellData['rate'] = 0;$this->updateNonDiffer();}

        $receipt=Receipt::where('sell_id',$this->sell->id)->first();
        if ($receipt){
            if ($this->sell->price_type_id!=2) $receipt->acc_id=null;
            else $receipt->acc_id=$this->sellData['acc_id'];
            $receipt->price_type_id=$this->sell->price_type_id;
            $receipt->save();
        }
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
    }


    protected function getSellFormSchema(): array
    {
        return [

            Section::make()
                ->schema([
                    TextInput::make('id')
                        ->hiddenLabel()
                        ->prefix('رقم الفاتورة')
                        ->columnSpan(2)
                        ->disabled(),

                    DatePicker::make('order_date')
                        ->hiddenLabel()
                        ->prefix('التاريخ')
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'customer_id' })",
                        ])
                        ->id('order_date')
                        ->autofocus()
                        ->afterStateUpdated(function ($state){
                            $res=Sell::find($this->sell_id);
                            $res->order_date=$state;
                            $res->save();
                        })
                        ->columnSpan(2)

                        ->required(),
                    Select::make('customer_id')
                        ->relationship('Customer','name')
                        ->hiddenLabel()

                        ->prefix('الزبون')
                        ->prefixIcon('heroicon-m-user')
                        ->prefixIconColor('info')
                        ->afterStateUpdated(function ($state){
                            $this->sell->customer_id=$state;
                            $this->sell->save();
                        })
                        ->columnSpan(4),

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
                        ->extraAttributes(['x-on:change' => "\$wire.updatePriceType"])
                        ->columnSpan(2),
                    Select::make('acc_id')
                        ->options(Acc::all()->pluck('name','id'))
                        ->hiddenLabel()
                        ->prefix('الحساب المصرفي')

                        ->prefixIcon('heroicon-m-currency-dollar')
                        ->prefixIconColor('warning')
                        ->columnSpan(2)
                        ->afterStateUpdated(function ($state,Get $get){
                            $receipt=Receipt::where('sell_id',$this->sell->id)->first();
                            if ($receipt){
                                $receipt->acc_id=$state;
                                $receipt->save();
                            }
                        })
                        ->dehydrated()
                        ->visible(fn(Get $get): bool =>( $get('pay')>0 && $get('price_type_id')==2) ),
                    TextInput::make('tot')
                        ->hiddenLabel()
                        ->prefix('اجمالي الفاتورة')
                        ->columnSpan(2)
                        ->mask(RawJs::make('$money($input)'))
                        ->disabled(),
                    TextInput::make('rate')
                        ->hiddenLabel()
                        ->prefix('النسبة')
                        ->prefixIcon('heroicon-m-chart-pie')
                        ->prefixIconColor('danger')
                        ->extraAttributes(['x-on:change' => "\$wire.updateDiffer"])
                        ->visible(fn()=>$this->sell->price_type_id==2)
                        ->columnSpan(2)
                        ->numeric()
                        ->minValue(function (){
                            if ($this->sell->price_type_id==2) return 1;
                            else return 0;
                        })
                        ->maxValue(100),
                    TextInput::make('differ')
                        ->hiddenLabel()
                        ->prefix('فرق عملة')
                        ->prefixIcon('heroicon-m-document-plus')
                        ->prefixIconColor('success')
                        ->visible(fn()=>$this->sell->price_type_id==2)
                        ->columnSpan(2)
                        ->readOnly(),
                    TextInput::make('cost')
                        ->hiddenLabel()
                        ->prefix('تكاليف إضافية')
                        ->extraAttributes(['x-on:change' => "\$wire.updatePay"])
                        ->columnSpan(2)
                        ->numeric()
                        ->gte(0),
                    TextInput::make('pay')
                        ->hiddenLabel()
                        ->prefix('المدفوع')
                        ->prefixIcon('heroicon-m-hand-thumb-up')
                        ->prefixIconColor('blue')
                        ->columnSpan(2)
                        ->live(onBlur: true)

                        ->default('0')
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'barcode_id' })",
                        ])
                        ->afterStateUpdated(function (Set $set,Get $get,$state){
                            if (!$state) $set('pay',0);
                            $baky=$this->sell->total-$state;
                            $set('baky', $baky);
                            $this->sell->pay=$state;
                            $this->sell->baky=$baky;
                            $this->sell->save();
                            $this->sellForm->fill($this->sell->toArray());

                            if (!$state || $state<=0)
                             Receipt::where('sell_id',$this->sell_id,'rec_who'==6)->delete();
                            else {
                                $receipt=Receipt::where('sell_id',$this->sell_id,'rec_who'==6)->first();
                                if ($receipt)
                                    $receipt->update(['val'=>$this->sell->pay]);
                                else {
                                     $receipt=Receipt::create([
                                        'receipt_date'=>$this->sell->order_date,
                                        'customer_id'=>$this->sell->customer_id,
                                        'sell_id'=>$this->sell->id,
                                        'price_type_id'=>$this->sell->price_type_id,
                                        'acc_id'=>$this->sellData['acc_id'],
                                        'rec_who'=>6,
                                        'imp_exp'=>0,
                                        'val'=>$this->sell->pay,
                                        'notes'=>'فاتورة مبيعات رقم '.strval($this->sell->id),
                                        'user_id'=>Auth::id()
                                    ]);

                                }
                                if ($receipt->acc_id)
                                 $this->sellForm->fill(collect($this->sell)->put('acc_id',$receipt->acc_id)->toArray());

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
                    TextInput::make('total')
                        ->hiddenLabel()
                        ->prefix('الإجمالي النهائي')
                        ->columnSpan(2)
                        ->readOnly()
                        ->default('0'),
                    TextInput::make('notes')
                        ->hiddenLabel()
                        ->prefix('ملاحظات')
                        ->afterStateUpdated(function ($state){
                            $this->sell->notes=$state;
                            $this->sell->save();
                        })
                        ->columnSpanFull(),

                ])
                ->columns(8)
                ->collapsible()
                ->reactive()
                ->hidden(function (){
                    return $this->sell_id==null;
                })
        ];
    }

    protected function getSellTranFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('barcode_id')
                        ->hiddenLabel()
                        ->prefix('الباركود')
                        ->required()
                        ->exists(Barcode::class,'id')
                        ->live(onBlur: true)
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
                        ->options(Item::where('stock1','>',0)->pluck('name','id'))
                        ->relationship('Item','name')
                        ->live()
                        ->reactive()
                        ->required()
                        ->extraAttributes([
                            'wire:change' => "ChkItem",
                            'wire:keydown.enter' => "ChkItem",
                        ])
                        ->columnSpanFull()
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
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
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
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
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

                ])
                ->columns(2)
                ->hidden(function (){
                    return $this->sell_id==null;
                }),

        ];
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
            ])

            ->actions([
                \Filament\Tables\Actions\Action::make('delete')
                    ->action(function (Sell_tran $record){
                        $this->incAll($this->sell_id,$record->item_id,$this->sell->place_id,$record->q1,$record->q2);
                        $this->selltran= $record->delete();

                        $tot=Sell_tran::where('sell_id',$this->sell_id)->sum('sub_tot');
                        $baky=$tot-$this->sell->pay;
                        $this->sell->tot=$tot;
                        $this->sell->baky=$baky;
                        $this->sell->save();
                        $this->sellForm->fill($this->sell->toArray());
                        $this->sellTranForm->fill([]);

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
}
