<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\BuysWorkResource\Pages;
use App\Filament\Resources\BuysWorkResource\RelationManagers;
use App\Models\Buys_work;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BuysWorkResource extends Resource
{
    protected static ?string $model = Buys_work::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

protected static ?string $navigationLabel='فاتورة مشتريات';
protected static ?string $navigationGroup='فواتير شراء';
protected static ?int $navigationSort=1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('order_date')
                            ->id('order_date')
                            ->autofocus()
                            ->label('التاريخ')
                            ->columnSpan(2)
                            ->inlineLabel()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('المورد')
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
                                'wire:keydown.enter' => "\$dispatch('goto', { test: 'barcode_id' })",
                            ])

                            ->id('pay'),
                        TextInput::make('baky')
                            ->label('المتبقي')

                            ->columnSpan(2)
                            ->inlineLabel()
                            ->disabled()
                            ->default('0'),
                      TextInput::make('notes')
                        ->label('ملاحظات')
                        ->columnSpan(8),

                    ])
                    ->columns(8)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
          ->query(function (Buys_work $buy)  {
            $buy=Buys_work::where('id',Auth::id()) ;
            return  $buy;
          })
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الالي'),
                TextColumn::make('Supplier.name')
                    ->label('اسم المورد'),
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
                Tables\Actions\Action::make('buytran')
                ->label('إدخال أصناف للفاتورة')
                ->icon('heroicon-m-plus')
                ->color('success')
                ->url(fn(): string =>  self::getUrl('createbuy'))

            ])

           ;
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

            'create' => Pages\CreateBuysWork::route('/create'),
            'createbuy' => Pages\CreateBuy::route('/createbuy'),

            'edit' => Pages\EditBuysWork::route('/{record}/edit'),
            'index' => Pages\ListBuysWorks::route('/'),
        ];
    }


}
