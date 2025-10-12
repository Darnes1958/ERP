<?php

namespace App\Filament\ins\Resources;

use App\Filament\ins\Resources\BankResource\Pages\CreateBank;
use App\Filament\ins\Resources\BankResource\Pages\EditBank;
use App\Filament\ins\Resources\BankResource\Pages\ListBanks;
use App\Filament\Resources\BankResource\Pages;
use App\Filament\Resources\BankResource\RelationManagers;
use App\Models\Bank;
use App\Models\Main;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مصارف';
    protected static string | \UnitEnum | null $navigationGroup='مصارف';
    protected static ?int $navigationSort=10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('BankName')
                    ->required()
                    ->label('اسم المصرف')
                    ->maxLength(255),
                Select::make('taj_id')
                    ->relationship('Taj','TajName')
                    ->label('المصرف التجميعي')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('TajName')
                            ->required()
                            ->label('المصرف التجميعي')
                            ->maxLength(255),
                        TextInput::make('TajAcc')
                            ->label('رقم الحساب')
                            ->required(),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('الرقم الألي'),
                TextColumn::make('BankName')
                    ->searchable()
                    ->sortable()
                 ->label('اسم المصرف'),
                TextColumn::make('Taj.TajName')
                    ->searchable()
                    ->sortable()
                    ->label('المصرف التجميعي'),
                TextColumn::make('main_count')
                    ->searchable()
                    ->sortable()
                    ->counts('Main')
                    ->label('عدد العقود'),


            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->visible(fn(Model $record): bool =>!Main::where('bank_id',$record->id)->exists()),
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
            'index' => ListBanks::route('/'),
            'create' => CreateBank::route('/create'),
            'edit' => EditBank::route('/{record}/edit'),
        ];
    }
}
