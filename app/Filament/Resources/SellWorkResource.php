<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\SellWorkResource\Pages;
use App\Filament\Resources\SellWorkResource\RelationManagers;
use App\Models\Customer;
use App\Models\Sell_work;
use App\Models\SellWork;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SellWorkResource extends Resource
{
    protected static ?string $model = Sell_work::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel='فاتورة مبيعات';
    protected static ?string $navigationGroup='فواتير مبيعات';
    protected static ?int $navigationSort=1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('order_date')
                            ->extraAttributes([
                                'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'customer_id' })",
                            ])
                            ->id('order_date')
                            ->autofocus()
                            ->label('التاريخ')

                            ->columnSpan(2)
                            ->inlineLabel()
                            ->required(),
                        Select::make('customer_id')
                            ->label('الزبون')
                            ->options(Customer::where('id','!=',1)->pluck('name','id'))
                            ->searchable()
                            ->live()
                            ->required()
                            ->inlineLabel()
                            ->columnSpan(3)
                            ->extraAttributes([
                                'wire:change' => "\$dispatch('gotoitem', { test: 'place_id' })",
                                'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'place_id' })",
                            ])
                            ->id('customer_id'),
                        Select::make('place_id')
                            ->label('مكان التخزين')
                            ->relationship('Place','name')
                            ->live()
                            ->required()
                            ->inlineLabel()
                            ->columnSpan(2)

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
                                'wire:change' => "\$dispatch('gotoitem', { test: 'price_type_id' })",
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

                            ->extraAttributes([
                                'wire:change' => "\$dispatch('gotoitem', { test: 'pay' })",
                                'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'pay' })",
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
                                'wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'barcode_id' })",
                            ])

                            ->id('pay'),
                        TextInput::make('baky')
                            ->label('المتبقي')
                            ->columnSpan(2)
                            ->inlineLabel()
                            ->disabled()
                            ->default('0'),
                        Radio::make('single')
                            ->hiddenLabel()
                            ->inline()
                            ->inlineLabel(false)

                            ->visible(Setting::find(Auth::user()->company)->jomla)

                            ->options([
                                1 => 'قطاعي',
                                0 => 'جملة'
                            ]),

                    ])
                    ->columns(8)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (Sell_work $sell)  {
                $sell=Sell_work::where('id',Auth::id()) ;
                return  $sell;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الالي'),
                TextColumn::make('Customer.name')
                    ->label('اسم الزبون'),
                TextColumn::make('order_date')
                    ->label('التاريخ'),
                TextColumn::make('tot')
                    ->label('اجمالي الفاتورة'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->label('الباقي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('selltran')
                    ->label('إدخال أصناف للفاتورة')
                    ->icon('heroicon-m-plus')
                    ->color('success')
                    ->url(fn(): string =>  self::getUrl('createsell'))
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellWorks::route('/'),
            'createsell' => Pages\CreateSell::route('/createsell'),
            'create' => Pages\CreateSellWork::route('/create'),
            'edit' => Pages\EditSellWork::route('/{record}/edit'),
        ];
    }

}
