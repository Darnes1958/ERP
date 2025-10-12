<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\CustomerTypeResource\Pages\CreateCustomerType;
use App\Filament\market\Resources\CustomerTypeResource\Pages\EditCustomerType;
use App\Filament\market\Resources\CustomerTypeResource\Pages\ListCustomerTypes;
use App\Filament\Resources\CustomerTypeResource\Pages;
use App\Filament\Resources\CustomerTypeResource\RelationManagers;
use App\Models\Customer;
use App\Models\Customer_type;
use App\Models\CustomerType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CustomerTypeResource extends Resource
{
    protected static ?string $model = Customer_type::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralLabel='تصنيف الزبائن';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين';
    protected static ?int $navigationSort=10;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasrole('admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema

            ->components([
                TextInput::make('name')
                    ->label('البيان')
                    ->required()
                    ->autofocus()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ])        ,
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('البيان'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn(Customer_type $record)=>
                        Customer::where('customer_type_id',$record->id)->count()>0),
            ])
            ->toolbarActions([
                //
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
            'index' => ListCustomerTypes::route('/'),
            'create' => CreateCustomerType::route('/create'),
            'edit' => EditCustomerType::route('/{record}/edit'),
        ];
    }
}
