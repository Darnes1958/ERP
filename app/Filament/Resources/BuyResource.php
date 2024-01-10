<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\BuyResource\Pages;
use App\Filament\Resources\BuyResource\RelationManagers;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Set;
use Filament\Forms\Get;

class BuyResource extends Resource
{
    protected static ?string $model = Buy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralModelLabel='استفسار عن فاتورة';
    protected static ?string $navigationGroup='فواتير شراء';
    protected static ?int $navigationSort=3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Section::make()
              ->schema([
                DatePicker::make('order_date')
                  ->label('التاريخ')
                  ->default(now())
                  ->required(),
                Select::make('supplier_id')
                  ->label('المورد')
                  ->relationship('Supplier','name')
                  ->required()
                  ->columnSpan(2)
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
                      ])->columns(2)
                  ]),
                Select::make('place_id')
                  ->label('مكان التخزين')
                  ->relationship('Place','name')
                  ->required()
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
                    Section::make('تعديل وحدات كبري')
                      ->schema([
                        TextInput::make('name')
                          ->required()
                          ->unique()
                          ->label('الاسم'),
                        Radio::make('place_type')
                          ->inline()
                          ->options(PlaceType::class)
                      ])->columns(2)
                  ]),
                Select::make('price_type_id')
                  ->label('طريقة الدفع')
                  ->default(1)
                  ->relationship('Price_type','name')
                  ->required(),
                TextInput::make('tot')
                  ->label('إجمالي الفاتورة')
                  ->disabled(),
                TextInput::make('pay')
                  ->label('المدفوع')
                  ->default('0'),
                TextInput::make('baky')
                  ->label('المتبقي')
                  ->disabled()
                  ->default('0'),

              ])->columns(5),

              Section::make()
               ->schema([
                 Repeater::make('Buy_tran')
                  ->label('الاصناف')
                  ->relationship()
                  ->schema([
                    TextInput::make('barcode_id')
                     ->label('الباركود')
                     ->required()
                      ->live(onBlur: true)
                     ->afterStateUpdated(function (Set $set,$state){
                       $set('item_id',Barcode::where('barcode',$state)->first()->item_id);
                     }),
                    Select::make('item_id')
                     ->label('الصنف')
                     ->options(Item::all()->pluck('name','id'))
                     ->inlineLabel()
                     ->live()

                     ->required()
                      ->afterStateUpdated(function (Set $set,$state){
                        $set('barcode_id',Item::find($state)->barcode);
                      }),
                    TextInput::make('q1')
                    ->label('الكمية')
                    ->inlineLabel()
                    ->required(),
                    TextInput::make('p1')
                      ->label('السعر')
                    ->inlineLabel()
                    ->live()
                      ->afterStateUpdated(function (Set $set,Get $get){
                        $set('sub_input',$get('q1')*$get('p1'));
                      })
                    ->required(),
                    TextInput::make('sub_input')
                      ->label('المجموع')
                      ->inlineLabel()
                  ])
                 ->defaultItems(1)
                 ->columns(4)
                 ->orderColumn('sort')
               ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('id'),
               TextColumn::make('Supplier.name'),
               TextColumn::make('tot'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBuys::route('/'),
            'create' => Pages\CreateBuy::route('/create'),
            'edit' => Pages\EditBuy::route('/{record}/edit'),
        ];
    }
}
