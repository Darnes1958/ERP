<?php

namespace App\Filament\Resources\BuysWorkResource\Pages;

use App\Enums\TwoUnit;
use App\Filament\Resources\BuysWorkResource;

use App\Models\Buy_tran_work;

use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Forms\Components\Card;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Forms\Get;
use Filament\Resources\Pages\Page;

use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;


class NewBuy extends Page implements HasForms,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    protected static string $resource = BuysWorkResource::class;


    protected static string $view = 'filament.resources.buys-work-resource.pages.new-buy';

    public $buyTran=[];
    public $item_id='';
    public $q1='';
    public $price_input='';
    public $buy_id='';
    public $price_type_id;

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
                //
            ])


            ->striped();
    }
    public function mount(): void{
        $this->form->fill();
    }
    public function getFormSchema(): array
    {
        return [
            Card::make()
             ->schema([
                 Select::make('item_id')
                     ->autofocus()
                     ->label('الصنف')
                     ->searchable()
                     ->preload()
                     ->createOptionForm([
                         Section::make('ادخال شركات مصنع')
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
                                     ->extraAttributes([

                                         'x-on:keydown.enter' => "\$focus.previous()",
                                     ])
                                     ->columnSpan(2),
                                 TextInput::make('barcode')
                                     ->label('الباركود')
                                     ->required()

                                     ->disabled(fn(string $operation)=>$operation=='edit')
                                     ->live()
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
//                ->extraAttributes([

                                     //                'x-on:keydown.enter' => "\$focus.previous().",
                                     //            ])
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
                                  ->default(Auth::id())
                                  ->readOnly(),

                             ])
                             ->columns(4)

        ])
                     ->relationship('Item','name')

                     ->live(onBlur: true)
                     ->reactive()
                     ->required()
                     ->extraAttributes(
                         [
                             'x-on:keydown.enter' => "\$focus.focus(q1)",
                             'x-on:change' => "\$focus.focus(q1)",
                         ]
                     )

                     ->id('item_id'),
                 TextInput::make('price_input')
                 ->id('price_input'),
                 TextInput::make('q1')
                     ->extraAttributes(
                         [
                             'x-on:keydown.enter' => "alert(\$focus.focusable(item_id))",

                         ]
                     )
                     ->id('q1'),

             ])->model(Buy_tran_work::class)
        ];
    }
}
