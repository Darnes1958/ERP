<?php

namespace App\Filament\ins\Resources;

use App\Enums\R_type;
use App\Filament\ins\Resources\BankMainResource\Pages\CreateBankMain;
use App\Filament\ins\Resources\BankMainResource\Pages\EditBankMain;
use App\Filament\ins\Resources\BankMainResource\Pages\ListBankMains;
use App\Filament\Resources\BankMainResource\Pages;
use App\Filament\Resources\BankMainResource\RelationManagers;
use App\Models\BankMain;
use App\Models\Taj;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BankMainResource extends Resource
{
    protected static ?string $model = BankMain::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralLabel='المصرف الأم';
    protected static string | \UnitEnum | null $navigationGroup='مصارف';
    public static function shouldRegisterNavigation(): bool
    {
        return  auth()->user()->id==1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('اسم المصرف'),
                Radio::make('r_type')
                    ->options(R_type::class)
                    ->inline()
                    ->inlineLabel(false)

                    ->required()
                    ->label('نوع الخصم'),
                TextInput::make('ratio')
                    ->numeric()
                    ->required()
                    ->label('النسبة'),

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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المصرف'),
                TextColumn::make('r_type')
                    ->sortable()
                    ->searchable()
                    ->label('نوع الخصم'),
                TextColumn::make('ratio')
                    ->searchable()
                    ->sortable()
                    ->label('النسبة'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->visible(fn(Model $record): bool =>!Taj::where('bank_main_id',$record->id)->exists()),
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
            'index' => ListBankMains::route('/'),
            'create' => CreateBankMain::route('/create'),
            'edit' => EditBankMain::route('/{record}/edit'),
        ];
    }
}
