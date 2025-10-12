<?php

namespace App\Filament\ins\Resources;

use App\Filament\ins\Resources\TajResource\Pages\CreateTaj;
use App\Filament\ins\Resources\TajResource\Pages\EditTaj;
use App\Filament\ins\Resources\TajResource\Pages\ListTajs;
use App\Filament\Resources\TajResource\Pages;
use App\Filament\Resources\TajResource\RelationManagers;
use App\Models\Bank;
use App\Models\Taj;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TajResource extends Resource
{
    protected static ?string $model = Taj::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
protected static ?string $pluralLabel='المصرف التجميعي';

    protected static string | \UnitEnum | null $navigationGroup='مصارف';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('TajName')
                    ->required()
                    ->label('اسم المصرف'),
                TextInput::make('TajAcc')
                    ->required()
                    ->label('رقم الحساب'),
                Select::make('bank_main_id')
                 ->required()
                 ->label('المصرف الأم')
                 ->relationship('BankMain', 'name')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('الرقم الألي'),
                TextColumn::make('TajName')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المصرف'),
                TextColumn::make('TajAcc')
                    ->sortable()
                    ->searchable()
                    ->label('رقم الحساب'),
                TextColumn::make('BankMain.name')
                    ->searchable()
                    ->sortable()
                    ->label('المصرف الأم'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->visible(fn(Model $record): bool =>!Bank::where('taj_id',$record->id)->exists()),
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
            'index' => ListTajs::route('/'),
            'create' => CreateTaj::route('/create'),
            'edit' => EditTaj::route('/{record}/edit'),
        ];
    }
}
