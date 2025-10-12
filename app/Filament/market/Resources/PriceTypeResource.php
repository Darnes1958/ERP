<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\PriceTypeResource\Pages\CreatePriceType;
use App\Filament\market\Resources\PriceTypeResource\Pages\EditPriceType;
use App\Filament\market\Resources\PriceTypeResource\Pages\ListPriceTypes;
use App\Filament\Resources\PriceTypeResource\Pages;
use App\Filament\Resources\PriceTypeResource\RelationManagers;
use App\Models\Price_type;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceTypeResource extends Resource
{
  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->id==1;
  }
    protected static ?string $model = Price_type::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
  protected static string | \UnitEnum | null $navigationGroup='Setting';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
              TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->unique(ignoreRecord: true),
              Toggle::make('buy'),
              Toggle::make('sell'),
              Toggle::make('receipt'),
              Select::make('inc_dec')
              ->options([
                '0' => 'طبيعي',
                '1' => 'يزداد',
                '2' => 'ينقص',
              ]),
              TextInput::make('rate'),
              TextInput::make('val'),
              Toggle::make('available')
              ->default('true'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                IconColumn::make('buy')
                 ->boolean(),
                IconColumn::make('sell')
                 ->boolean(),
                IconColumn::make('receipt')
                 ->boolean(),
                TextColumn::make('inc_dec')
                 ->badge()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListPriceTypes::route('/'),
            'create' => CreatePriceType::route('/create'),
            'edit' => EditPriceType::route('/{record}/edit'),
        ];
    }
}
