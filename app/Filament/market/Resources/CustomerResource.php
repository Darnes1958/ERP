<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\market\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\market\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\market\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Sell;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationLabel='زبائن';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال زبائن');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
              TextInput::make('name')
                ->required()
                ->label('الاسم'),
             Select::make('customer_type_id')
                    ->label('التصنيف')
                    ->relationship('Customer_type','name')
                    ->required()

                    ->createOptionForm([
                        Section::make('ادخال تصنيف للزبائن')

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
              TextInput::make('address')
                ->label('العنوان'),
              TextInput::make('mdar')
                ->label('مدار'),
              TextInput::make('libyana')
                ->label('لبيانا'),
              Hidden::make('user_id')
                ->default(Auth::id()),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              TextColumn::make('Customer_type.name')
                ->sortable()
                ->label('التصنيف'),
              TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->label('الاسم'),
              TextColumn::make('address')
                ->icon('heroicon-o-envelope')
                ->iconColor('blue')
                ->label('العنوان'),
              TextColumn::make('mdar')
                ->searchable()
                ->icon('heroicon-o-phone')
                ->iconColor('green')
                ->label('مدار'),
              TextColumn::make('libyana')
                ->searchable()
                ->icon('heroicon-o-phone')
                ->label('لبيانا')
                ->iconColor('Fuchsia'),
            ])
            ->striped()
            ->filters([
                SelectFilter::make('Customer_type')
                    ->relationship('Customer_type', 'name')
                    ->label('التصنيف')
            ])
            ->recordActions([
                EditAction::make()
                 ->iconButton()
                 ->hidden(fn(Customer $record)=>$record->id<3),
                DeleteAction::make()
                  ->iconButton()
                  ->modalHeading('حذف زبون')
                 ->modalDescription('هل انت متأكد من الغاء هذا الزبون ؟')
                 ->hidden(fn(Customer $record)=>
                   Sell::where('customer_id',$record->id)->exists()
                   || $record->id<3
                   || Receipt::where('customer_id',$record->id)->exists()
                   || !Auth::user()->can('الغاء زبائن')),
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

}
