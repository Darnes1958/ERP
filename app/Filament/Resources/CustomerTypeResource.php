<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerTypeResource\Pages;
use App\Filament\Resources\CustomerTypeResource\RelationManagers;
use App\Models\Customer;
use App\Models\Customer_type;
use App\Models\CustomerType;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CustomerTypeResource extends Resource
{
    protected static ?string $model = Customer_type::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralLabel='تصنيف الزبائن';
    protected static ?string $navigationGroup='زبائن وموردين';
    protected static ?int $navigationSort=10;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasrole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
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
                Tables\Columns\TextColumn::make('name')
                    ->label('البيان'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Customer_type $record)=>
                        Customer::where('customer_type_id',$record->id)->count()>0),
            ])
            ->bulkActions([
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
            'index' => Pages\ListCustomerTypes::route('/'),
            'create' => Pages\CreateCustomerType::route('/create'),
            'edit' => Pages\EditCustomerType::route('/{record}/edit'),
        ];
    }
}
