<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\RentResource\Pages\ListRents;
use App\Filament\Resources\RentResource\Pages\CreateRent;
use App\Filament\Resources\RentResource\Pages\EditRent;
use App\Filament\Resources\RentResource\Pages;
use App\Filament\Resources\RentResource\RelationManagers;
use App\Models\Rent;
use App\Models\Renttran;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RentResource extends Resource
{
    protected static ?string $model = Rent::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel=' إيجارات';
    protected static string | \UnitEnum | null $navigationGroup='إيجارات';
    protected static ?int $navigationSort=1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('إيجارات');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('الاسم'),
                TextInput::make('amount')
                    ->required()
                    ->label('الإيجار'),
                Select::make('place_id')
                    ->label('الصالة أو المحزن')
                    ->relationship('Place', 'name')
                    ->searchable()
                    ->required()
                    ->live()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('الإيجار')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('status')
                    ->label('الحالة')
                    ->sortable()
                    ->boolean(),
                TextColumn::make('raseed')
                    ->label('الرصيد')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn(Rent $record)=>
                        Renttran::where('rent_id',$record->id)->count()>0)  ,
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
            'index' => ListRents::route('/'),
            'create' => CreateRent::route('/create'),
            'edit' => EditRent::route('/{record}/edit'),
        ];
    }
}
