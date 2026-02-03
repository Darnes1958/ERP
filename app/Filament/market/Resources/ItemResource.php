<?php

namespace App\Filament\market\Resources;

use App\Enums\TwoUnit;
use App\Filament\market\Resources\ItemResource\Pages\CreateItem;
use App\Filament\market\Resources\ItemResource\Pages\EditItem;
use App\Filament\market\Resources\ItemResource\Pages\ListItems;
use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Price_buy;
use App\Models\Price_sell;
use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $pluralModelLabel='أصناف';
  protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مشتريات');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                ->columnSpan(2),
                TextInput::make('barcode')
                    ->label('الباركود')
                    ->required()
                    ->hidden(!Setting::find(Auth::user()->company)->barcode)
                    //->disabled(fn(string $operation,Get $get)=>$operation=='edit' && Buy_tran::where('item_id',$get('id'))->exists())
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

             //   Select::make('unitb_id')
             //       ->label('الوحدة الصغري')
             //       ->relationship('Unitb','name')
             //    //   ->required()
             //       ->columnSpan(2)
             //      ->createOptionForm([
             //           Section::make('ادخال وحدات صغري')
             //               ->description('ادخال وحدة صغري (قطعة,علبة .... الخ)')
             //               ->schema([
             //                   TextInput::make('name')
             //                       ->required()
             //                       ->unique()
             //                       ->label('الاسم'),
             //               ])->columns(2)
             //       ])
             //        ->editOptionForm([
             //           Section::make('تعديل وحدات صغري')
             //               ->schema([
             //                   TextInput::make('name')
             //                       ->required()
             //                       ->unique()
             //                       ->label('الاسم'),
             //               ])->columns(2)
             //       ])
             //       ->hidden(),
                 //   ->hidden(fn(Get $get): bool => ! $get('two_unit')),
        //      TextInput::make('count')
        //            ->label('العدد')
        //            ->required()
        //            ->hidden(fn(Get $get): bool =>  ! $get('two_unit')),
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
           //     TextInput::make('price2')
           //         ->label('سعر الصغري قطاعي')
           //         ->required()
           //         ->hidden(fn(Get $get): bool => ! $get('two_unit')),
            //  TextInput::make('pricej1')
            //    ->label('سعر البيع جملة')
            //    ->hidden(!Setting::find(Auth::user()->company)->jomla)
            //    ->required(),
           //   TextInput::make('pricej2')
           //     ->label('سعر الصغري جملة')
           //     ->required()
           //     ->hidden(fn(Get $get): bool => ! $get('two_unit')),

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
           //elect::make('company_id')
           //   ->label('الشركة المصنعة')
           //   ->relationship('Company','name')
           //   ->default(1)
           //   ->columnSpan(2)
           //   ->createOptionForm([
           //       Section::make('ادخال شركات مصنع')
           //           ->schema([
           //               TextInput::make('name')
           //                   ->required()
           //                   ->unique()
           //                   ->label('الاسم'),
           //           ])
           //   ])
           //   ->editOptionForm([
           //       Section::make('تعديل شركات مصنعة')
           //           ->schema([
           //               TextInput::make('name')
           //                   ->required()
           //                   ->unique()
           //                   ->label('الاسم'),
           //           ])
           //   ]),
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
                TextColumn::make('two_unit')
                    ->hidden(),
                TextColumn::make('count')
                    ->label('العدد')
                    ->sortable()
                    ->formatStateUsing(function (string $state) {
                        if ($state==1) return '';
                        return $state;
                    })
                    ->searchable()
                    ->visible(Setting::find(Auth::user()->company)->has_two),
                TextColumn::make('Unitb.name')
                    ->label('الوحدة الصغري')
                    ->sortable()
                    ->searchable()
                    ->visible(Setting::find(Auth::user()->company)->has_two),

                TextColumn::make('stock1')
                    ->label('الرصيد'),
                TextColumn::make('stock2')
                    ->label('رصيد الصغري')
                    ->formatStateUsing(function (string $state) {
                        if ($state==0) return '';
                        return $state;
                    })
                    ->visible(Setting::find(Auth::user()->company)->has_two),
              TextInputColumn::make('price_buy')
                  ->afterStateUpdated(function ($state,Model $record){
                      Price_buy::where('item_id',$record->id)->where('price_type_id',1)->update(['price'=>$state]);
                  })
                ->label('سعر الشراء'),
                TextInputColumn::make('price1')
                    ->afterStateUpdated(function ($state,Model $record){
                        Price_sell::where('item_id',$record->id)->where('price_type_id',1)->update(['price1'=>$state]);
                    })
                    ->label('سعر البيع'),
                TextColumn::make('price2')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('سعر الصغري')
                    ->formatStateUsing(function (string $state) {
                        if ($state==0) return '';
                        return $state;
                    })
                    ->visible(Setting::find(Auth::user()->company)->has_two),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
              DeleteAction::make()
                  ->hidden(fn ($record):bool =>
                  $record->Buy_tran()->exists()
                  || Auth::user()->can('الغاء مشتريات')
                  )

                  ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListItems::route('/'),
            'create' => CreateItem::route('/create'),
            'edit' => EditItem::route('/{record}/edit'),
        ];
    }
}
