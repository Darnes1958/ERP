<?php

namespace App\Filament\market\Resources\SellOffers\Pages;

use App\Filament\market\Resources\SellOffers\SellOfferResource;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Price_type;
use App\Models\Sell_offer;
use App\Models\Sell_offer_tran;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SellOfferEdit extends Page implements HasTable,HasForms
{
    use InteractsWithTable,InteractsWithForms;
    use InteractsWithRecord;
    use Raseed;

    protected static string $resource = SellOfferResource::class;

    protected string $view = 'filament.market.resources.sell-offer-resource.pages.sell-offer-edit';

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
        $this->sell=Sell_offer::find($this->sell_id);

        $this->sellForm->fill($this->record->toArray());

        $this->sellTranForm->fill([]);
    }

    public function is_two(){
        if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {
            return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
        else return false;
    }

    public function sellForm(Schema $schema): Schema
    {
        return $schema
        ->model(Sell_offer::class)
        ->statePath('sellData')
        ->components([
            Section::make()
                ->schema([
                    TextInput::make('id')
                        ->hiddenLabel()
                        ->prefix('رقم فاتورة العرض')
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
                            $res=Sell_offer::find($this->sell_id);
                            $res->order_date=$state;
                            $res->save();
                            Notification::make()
                                ->title('تم تحزين التاريخ بنجاح')
                                ->success()
                                ->send();
                        })
                        ->columnSpan(2)

                        ->required(),
                    Select::make('customer_id')
                        ->relationship('Customer','name')
                        ->hiddenLabel()
                        ->searchable()
                        ->preload()
                        ->prefix('الزبون')
                        ->prefixIcon('heroicon-m-user')
                        ->prefixIconColor('info')
                        ->afterStateUpdated(function ($state){
                            $this->sell->customer_id=$state;
                            $this->sell->save();
                            Notification::make()
                                ->title('تم تحزين الزبون بنجاح')
                                ->success()
                                ->send();
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
                        ->minValue(0)
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
                            Notification::make()
                                ->title('تم تحزين الملاحظات بنجاح')
                                ->success()
                                ->send();
                        })
                        ->columnSpanFull(),

                ])
                ->columns(8)
                ->reactive()
        ]);
    }
    public function sellTranForm(Schema $schema): Schema
    {
        return $schema
            ->model(Sell_offer_tran::class)
            ->statePath('selltranData')
            ->components([
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
                            ->relationship('Item','name')
                   //        ->options(function (){
                   //            return Item::whereIn('id',Place_stock::where('place_id',$this->sellData['place_id'])
                   //                ->where('stock1','>',0)->pluck('item_id'))->pluck('name','id');
                   //        })

                            ->live()
                            ->required()
                            ->afterStateUpdated(function (){
                                $this->ChkItem();
                            })
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
                            ->readOnly(fn():bool => ! Auth::user()->hasRole('admin'))
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
                    ->columns(2),

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

        $res = Sell_offer_tran::where('sell_id', $this->sell_id)->where('item_id', $item_id)->first();
        if ($res){
            $res->delete();}

        $sub=($q1*$this->selltranData['price1'])+($q2*$this->selltranData['price2']);
        $this->selltran=Sell_offer_tran::create(collect($this->selltranData)
            ->put('sub_tot',$sub)
            ->put('sell_id',$this->sell_id)
            ->put('user_id',Auth::id())
            ->except('id')
            ->except('raseed_all')
            ->except('raseed_place')
            ->toArray());


        $this->sellTranForm->fill([]);
        $tot = Sell_offer_tran::where('sell_id', $this->sell_id)->sum('sub_tot');
        $this->sell->tot=$tot;
        $this->sell->differ=($this->sell->tot+$this->sell->cost)*$this->sell->rate/100;
        $this->sell->total=$tot+$this->sell->differ+$this->sell->cost;
        $this->sell->save();

        $this->sellForm->fill($this->sell->toArray());
        $this->dispatch('gotoitem', test: 'barcode_id');
    }
    public function updatePay()
    {
        $this->sell->update($this->sellForm->getState());
        $this->sell->total=$this->sell->tot+$this->sell->cost+$this->sell->differ;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());
      Notification::make()
        ->title('تم التعديل بنجاح')
        ->success()
        ->send();
    }
    public function updatePriceType(){
        $this->sell->price_type_id=$this->sellData['price_type_id'] ;
        if ($this->sell->price_type_id==2)
        {$this->sellData['rate'] = Price_type::find(2)->rate;
            $this->updateDiffer();
        }
        else {$this->sellData['rate'] = 0;$this->updateNonDiffer();}

          Notification::make()
            ->title('تم تحزين طريقة الدفع بنجاح')
            ->success()
            ->send();
    }
    public function updateNonDiffer(){
        $this->sell->rate=0;
        $this->sell->differ=0;
        $this->sell->total=$this->sell->tot+$this->sell->cost;
        $this->sell->save();
        $this->sellForm->fill($this->sell->toArray());

    }
    public function updateDiffer(){
        $this->sell->rate=$this->sellData['rate'];
        $this->sell->differ=($this->sell->tot+$this->sell->cost)*$this->sell->rate/100;
        $this->sell->total=$this->sell->tot+$this->sell->cost+$this->sell->differ;

        $this->sell->save();

        $this->sellForm->fill($this->sell->toArray());
      Notification::make()
        ->title('تم تحزين النسبة بنجاح')
        ->success()
        ->send();
    }




    public function table(Table $table):Table
    {
        return $table
            ->query(function ()  {
                $sell_tran=Sell_offer_tran::where('sell_id',$this->sell_id);

                return  $sell_tran;
            })
            ->columns([
                TextColumn::make('item_id')
                    ->label('رقم الصنف')
                    ->sortable(),
                TextColumn::make('Item.name')
                    ->label('اسم الصنف')
                    ->color('info')
                    ->sortable(),
                TextColumn::make('q1')
                    ->label('الكمية'),
                TextColumn::make('price1')
                    ->label('سعر البيع'),
                TextColumn::make('sub_tot')
                    ->label('المجموع')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),


            ])

            ->recordActions([
                Action::make('delete')
                    ->action(function (Sell_offer_tran $record){

                        $this->selltran= $record->delete();

                        $tot=Sell_offer_tran::where('sell_id',$this->sell_id)->sum('sub_tot');
                        $this->sell->differ=($tot+$this->sell->cost)*$this->sell->rate/100;
                        $total=$tot+$this->sell->cost+$this->sell->differ;

                        $this->sell->total=$total;
                        $this->sell->tot=$tot;
                        $this->sell->save();

                        $this->sellForm->fill($this->sell->toArray());
                        $this->sellTranForm->fill([]);

                    })
                    ->icon('heroicon-m-trash')
                    ->iconButton()->color('danger')
                    ->hiddenLabel()
                    ->hidden(function (Sell_offer_tran $record){
                        return Sell_offer_tran::where('sell_id',$this->sell_id)->count()==1;
                    })
                    ->requiresConfirmation(),
            ])

            ->striped();
    }
}
