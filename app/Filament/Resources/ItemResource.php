<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use App\Models\Setting;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $pluralModelLabel='أصناف';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                 ->label('اسم الصنف')
                 ->required()
                ->unique(ignoreRecord: true)
                  ->validationMessages([
                    'unique' => ' :attribute مخزون مسبقا ',
                  ])
                ->columnSpan(2),
                TextInput::make('barcode')
                    ->label('الباركود')
                    ->required()
                    ->live()
                    ->unique(ignoreRecord: true)
                  ->validationMessages([
                    'unique' => 'هذا الـ :attribute مخزون مسبقا',
                  ]),

                Radio::make('two_unit')
                    ->label('مستوي الوحدات')
                    ->inline()
                    ->options([
                        false => 'أحادي',
                        true => 'ثنائي',
                    ])
                    ->default(false)
                    ->required()
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
                ->required(),

                TextInput::make('price1')
                    ->label('السعر قطاعي')
                    ->required(),
                TextInput::make('price2')
                    ->label('سعر الصغري قطاعي')
                    ->required()
                    ->hidden(fn(Get $get): bool => ! $get('two_unit')),
              TextInput::make('pricej1')
                ->label('السعر جملة')
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
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                 ->label('الرقم الألي')
                 ->sortable()
                 ->searchable(),
                TextColumn::make('barcode')
                    ->label('الباركود')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('اسم الصنف')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Unita.name')
                    ->label('الوحدة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Unitb.name')
                    ->label('الوحدة الصغري')
                    ->sortable()
                    ->searchable()
                    ->visible(Setting::find(Auth::user()->company)->many_place),
                TextColumn::make('stock1')
                    ->label('الرصيد'),
                TextColumn::make('stock2')
                    ->label('رصيد الصغري')
                    ->visible(Setting::find(Auth::user()->company)->many_place),
                TextColumn::make('price1')
                    ->label('السعر'),
                TextColumn::make('price2')
                    ->label('سعر الصغري')
                    ->visible(Setting::find(Auth::user()->company)->many_place),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
              Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
