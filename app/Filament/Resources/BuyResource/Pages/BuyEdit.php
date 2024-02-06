<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Filament\Resources\BuyResource;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Price_buy;
use App\Models\Recsupp;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class BuyEdit extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithRecord;
    use Raseed;
    protected static string $resource = BuyResource::class;

    protected static string $view = 'filament.resources.buy-resource.pages.buy-edit';

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
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'supplier_id' })",
                        ])
                        ->id('order_date')
                        ->autofocus()
                        ->label('التاريخ')
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
                        ->label('المورد')
                        ->inlineLabel()
                        ->columnSpan(3)
                        ->disabled(),
                    Select::make('place_id')
                        ->relationship('Place','name')
                        ->label('مكان التخزين')
                        ->columnSpan(3)
                        ->inlineLabel()
                        ->disabled()
                        ->visible(Setting::find(Auth::user()->company)->many_place),
                    Select::make('price_type_id')
                        ->relationship('Place','name')
                        ->label('طريقة الدفع')
                        ->columnSpan(2)
                        ->inlineLabel()
                        ->disabled(),
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
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'barcode_id' })",
                        ])
                        ->afterStateUpdated(function (Set $set,Get $get,$state){
                            if (!$state) $set('pay',0);

                            $set('baky',$get('tot')-$get('pay'));

                            $this->buy->pay=$get('pay');
                            $this->buy->baky=$get('baky');
                            $this->buy->save();
                            if ((!$state || $state<=0) &&  $this->buy->recipt_id)
                            {Recsupp::find($this->buy->recipt_id)->delete();
                                $this->buy->recipt_id='';
                                $this->buy->save();
                            }
                            else {
                                if ($this->buy->recipt_id)
                                    Recsupp::find($this->buy->recipt_id)->update(['val'=>$this->buy->pay]);
                                else {
                                    $recipt= Recsupp::create([
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
                                    $this->buy->recipt_id=$recipt->id;
                                    $this->buy->save();
                                }

                            }
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

        $this->buytran=Buy_tran::where('buy_id',Auth::id())
            ->where('item_id',$item)->first();
        if ($this->buytran)
            $this->buyTranForm->fill($this->buytran->toArray());
        else $this->buyTranForm->fill([
            'barcode_id'=>$barcode,'item_id'=>$item,'price_input'=>$price_input,'q1'=>'',
            'buy_id'=>$this->buy_id,'user_id'=>Auth::id(),'sub_input'=>'',]);
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
        if (!$this->buytranData['item_id']) {
            Notification::make()->title('يجب اختيار الصنف ')->icon('heroicon-o-check')->iconColor('success')->send();
            return;
        }
        if (!$this->buytranData['price_input'] || $this->buytranData['price_input']<=0) {
            Notification::make()->title('يجب ادخال السعر ')->icon('heroicon-o-check')->iconColor('success')->send();
            return;
        }
        if (!$this->buytranData['q1'] || $this->buytranData['q1']<=0) {
            Notification::make()->title('يجب ادخال الكمية ')->icon('heroicon-o-check')->iconColor('success')->send();
            return;
        }

        $this->buytran=Buy_tran::where('buy_id',$this->buy_id)
            ->where('item_id',$this->buytranData['item_id'])->first();
        if ($this->buytran) {
            $this->decAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1);
            $this->buytran->update($this->buyTranForm->getState());
        }
        else
            $this->buytran=Buy_tran::create(collect($this->buytranData)->except('id')->toArray());

        $this->incAllBuy($this->buytran->item_id,$this->buy->place_id,$this->buytran->q1
            ,$this->buy->price_type_id,$this->buytran->price_input);


        $this->buyTranForm->fill([]);
        $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
        $baky=$tot-$this->buy->pay;
        $this->buy->tot=$tot;
        $this->buy->baky=$baky;
        $this->buy->save();
        $this->buyForm->fill($this->buy->toArray());

        $this->dispatch('goto', test: 'barcode_id');
    }
    protected function getBuyTranFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('barcode_id')
                        ->label('الباركود')
                        ->required()
                        ->exists()
                        ->live(onBlur: true)
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
                        ->extraAttributes([
                            'wire:change' => "ChkItem",
                            'wire:keydown.enter' => "ChkItem",
                        ])
                        ->id('item_id'),
                    DatePicker::make('exp_date')
                        ->label('تاريخ الصلاحية')
                        ->inlineLabel()
                        ->extraAttributes([
                            'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'q1' })",
                        ])
                        ->visible(Setting::find(Auth::user()->company)->has_exp),

                    TextInput::make('price_input')
                        ->label('السعر')
                        ->inlineLabel()
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
                        ->label('الكمية')
                        ->inlineLabel()
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
                    TextInput::make('sum_input')
                        ->label('المجموع')
                        ->readOnly()
                        ->inlineLabel(),
                ])
                ->hidden(function (){
                    return $this->buy_id==null;
                }),
            Section::make()
                ->schema([
                    \Filament\Forms\Components\Actions::make([

                        \Filament\Forms\Components\Actions\Action::make('الغاء الفاتورة')
                            ->icon('heroicon-m-trash')
                            ->button()
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(function () {
                                $buytran=Buy_tran::where('buy_id',$this->buy_id)->get();
                                foreach ($buytran as $tran)
                                    $this->decAllBuy($tran->item_id,$this->buyForm->place_id,$tran->q1);

                                Recsupp::where('buy_id',$this->buy_id)->delete();
                                Buy_tran::where('buy_id',$this->buy_id)->delete();
                                Buy::find($this->buy_id)->delete();

                                $this->is_filled=false;
                                $this->buy_id='';
                                $this->buyForm->reset();
                                $this->buyTranForm->reset();


                                $this->buyFormBlade->fill($this->buyForm->toArray());

                            })
                    ])->extraAttributes(['class' => 'items-center justify-between']),

                ])
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
                        $record->delete();
                        $this->decAllBuy($record->item_id,$this->buy->place_id,$record->q1);
                        $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
                        $baky=$tot-Buy::find($this->buy_id)->pay;
                        Buy::find($this->buy_id)->update([
                            'tot'=>$tot,
                            'baky'=>$baky,

                        ]);
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
                        $this->buyTranFormBlade->fill($record->toArray());
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
