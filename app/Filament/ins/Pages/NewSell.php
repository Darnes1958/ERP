<?php

namespace App\Filament\ins\Pages;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use App\Livewire\Traits\Raseed;
use App\Models\Item;
use App\Models\Main;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Price_sell;
use App\Models\Sell;
use App\Models\Sell_tran;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class NewSell extends Page
{
    use Raseed;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.new-sell';
    protected ?string $heading='';
    protected static bool $shouldRegisterNavigation=false;
    public $sellData;

    public $sell_id;

    public $Sell;

    public function mount(): void
    {

        $this->sellForm->fill(['order_date'=>now(),'place_id'=>Place::first()->id]);

    }
    protected function getForms(): array
    {
        return array_merge(parent::getForms(),[

            'sellForm'=> $this->makeForm()
                ->model(Sell::class)
                ->components($this->getSellFormSchema())
                ->statePath('sellData'),
        ]);
    }
    protected function getSellFormSchema(): array
    {
        return [
            Grid::make()
             ->schema([
                 Section::make()
                     ->schema([

                         Select::make('customer_id')
                             ->prefix('الزبون')
                             ->hiddenLabel()
                             ->relationship('Customer', 'name')
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
                             ->searchable()
                             ->preload()
                             ->live()
                             ->required()
                             ->columnSpan(2)
                             ->extraAttributes([
                                 'wire:change' => "\$dispatch('gotoitem', { test: 'place_id' })",
                                 'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'place_id' })",
                             ])
                             ->id('customer_id'),
                         DatePicker::make('order_date')
                             ->extraAttributes([
                                 'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'customer_id' })",
                             ])
                             ->default(function (){
                                 return now();
                             })
                             ->id('order_date')
                             ->autofocus()
                             ->live()
                             ->hiddenLabel()
                             ->prefix('التاريخ')
                             ->columnSpan(1)
                             ->required(),
                         Select::make('place_id')
                             ->prefix('نقطة البيع')
                             ->hiddenLabel()
                             ->relationship('Place','name')
                             ->live()
                             ->required()
                             ->columnSpan(2)
                             ->extraAttributes([
                                 'wire:change' => "\$dispatch('gotoitem', { test: 'price_type_id' })",
                                 'wire:keydown..enter' => "\$dispatch('goto', { test: 'price_type_id' })",
                             ])
                             ->id('place_id'),

                         TextInput::make('tot')
                             ->prefix('إجمالي الفاتورة')
                             ->hiddenLabel()
                             ->columnSpan(1)
                             ->readOnly(),
                         Hidden::make('price_type_id'),
                         Hidden::make('total'),
                         Hidden::make('single'),
                         Hidden::make('baky'),
                         Hidden::make('user_id'),
                         TableRepeater::make('Sell_tran')
                             ->hiddenLabel()
                             ->required()
                             ->addActionLabel('اضافة صنف')
                             ->relationship()
                             ->headers([
                                 Header::make('الصنف')
                                     ->width('40%'),
                                 Header::make('الكمية')
                                     ->width('12%'),
                                 Header::make('السعر')
                                     ->width('17%'),
                                 Header::make('الرصيد')
                                     ->width('12%'),
                                 Header::make('الاجمالي')
                                     ->width('17%'),

                             ])
                             ->schema([
                                 Select::make('item_id')
                                     ->required()
                                     ->preload()
                                     ->searchable()

                                     ->relationship('Item','name',
                                         modifyQueryUsing: fn ($query,Get $get) =>
                                         $query->whereIn('id',
                                             Place_stock::where('place_id', $get('../../place_id'))
                                                 ->where('stock1','>',0)
                                                 ->pluck('item_id'))
                                     )
                                     //->options(Item::all()->pluck('name','id'))
                                     ->disableOptionWhen(function ($value, $state, Get $get) {
                                         return collect($get('../*.item_id'))
                                             ->reject(fn($id) => $id == $state)
                                             ->filter()
                                             ->contains($value);
                                     })
                                     ->afterStateUpdated(function ($state,Set $set,Get $get){
                                         $p=Price_sell::where('item_id',$state)
                                             ->where('price_type_id',3)->first();
                                         if ($p) $price=$p->price1; else
                                             $price=Price_sell::where('item_id',$state)
                                                 ->where('price_type_id',1)->first()->price1;
                                          $set('price1',$price);
                                         $set('stock1',Place_stock::where('place_id',$get('../../place_id'))
                                             ->where('item_id',$state)->first()->stock1);
                                         $set('q1',1);
                                         $set('sub_tot',1*$price);
                                         $set('barcode_id',Item::find($state)->barcode);
                                         $total=0;
                                         foreach ($get('../../Sell_tran') as $item){
                                             if ($item['q1'] && $item['price1'] && $item['q1']>0 && $item['price1']>0 ) {

                                                 $total +=round($item['q1'] * $item['price1'],3);
                                             }
                                         }
                                         $set('../../tot',$total);
                                         $set('../../total',$total);
                                         $set('../../baky',$total);

                                     })
                                 ,

                                 TextInput::make('q1')
                                     ->numeric()
                                     ->minValue(1)
                                     ->live(onBlur: true)
                                     ->extraInputAttributes(['tabindex' => 1])
                                     ->afterStateUpdated(function ($state,Set $set,Get $get,$old,$operation){
                                         if ($state<1) {$set('q1',null);$set('sub_tot',null); return;}
                                         if ($state > $get('stock1')) {
                                             $set('q1',null);
                                             Notification::make()
                                                 ->title('الرصيد لا يسمح')
                                                 ->color('danger')
                                                 ->send();
                                             return;
                                         }
                                         if ($get('price1') && $get('q1')) $set('sub_tot',$get('price1')*$get('q1'));
                                     })
                                     ->required(),
                                 TextInput::make('price1')
                                     ->numeric()
                                     ->live(debounce: 500)
                                     ->minValue(1)
                                     ->afterStateUpdated(function (Set $set,Get $get,$state){
                                         if ($state<1) {$set('p1',null);$set('sub_tot',null); return;}
                                         if ($get('price1') && $get('q1')) $set('sub_tot',$get('price1')*$get('q1'));
                                     })
                                     ->required() ,
                                 TextInput::make('stock1')

                                     ->readOnly()
                                     ->dehydrated(false),
                                 TextInput::make('sub_tot')
                                     ->readOnly(),
                                 Hidden::make('sell_id'),
                                 Hidden::make('barcode_id'),
                                 Hidden::make('user_id')->default(Auth::id()),

                             ])
                             ->defaultItems(0)
                             ->addable(function ($state,Get $get){
                                 $flag=true;
                                 if (!$get('place_id')) return false;
                                 if ($state)
                                     foreach ($state as $item) {
                                         if (!$item['item_id'] || !$item['q1'] || !$item['price1']
                                             || $item['q1']==0 || $item['price1']==0) {$flag=false; break;}
                                     }
                                 return $flag;
                             })
                             ->afterStateUpdated(function ($state,Set $set,Get $get){
                                 $total=0;
                                 foreach ($state as $item){
                                     if ($item['q1'] && $item['price1'] && $item['q1']>0 && $item['price1']>0 ) {

                                         $total +=round($item['q1'] * $item['price1'],3);
                                     }
                                 }
                                 $set('tot',$total);
                                 $set('total',$total);
                                 $set('baky',$total);

                             })
                             ->live()
                             ->columnSpan('full'),
                         Actions::make([
                             Action::make('store')
                                 ->label('تخزين')
                                 ->color('success')
                                 ->action(function (Set $set,$livewire){
                                     $this->sellForm->validate();
                                     $set('price_type_id',3);
                                     $set('single',1);
                                     $set('user_id',Auth::id());
                                     $sell=Sell::create( collect($this->sellData)->except(['Sell_tran'])->toArray());
                                     foreach ($this->sellData['Sell_tran'] as $item){

                                         $item['sell_id']=$sell->id;
                                         $tran_id=Sell_tran::create(collect($item)->except(['stock1'])->toArray());
                                         $this->decAll($tran_id->id,$sell->id,$item['item_id'],$sell->place_id,$item['q1'],0);
                                         $this->setPriceSell($item['item_id'],$sell->price_type_id,$sell->single,$item['price1'],0);
                                     }
                                     $set('Sell_tran',null);
                                     $set('place_id',null);
                                     $set('tot',null);
                                     $set('customer_id',null);
                                     $this->Sell=Sell::find($sell->id);
                                     $this->sell_id=$sell->id;

                                        $this->redirect(url(
                                            route('filament.admin.pages.new-cont', ['sell_id'=>$this->sell_id])));

                                 })
                                 ,
                             Action::make('cancel')
                                 ->label('عودة')
                                 ->color('info')
                                 ->url(fn (): string =>
                                 route('filament.admin.pages.new-cont')),


                         ])->columnSpan('full')
                     ])
                     ->columns(3)
                     ->columnSpan(2)
             ])->columns(3)




        ];
    }
}
