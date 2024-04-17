<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceTypeResource\Pages;
use App\Filament\Resources\PriceTypeResource\RelationManagers;
use App\Models\Price_type;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class PriceTypeResource extends Resource
{
  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->id==1;
  }
    protected static ?string $model = Price_type::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
  protected static ?string $navigationGroup='Setting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPriceTypes::route('/'),
            'create' => Pages\CreatePriceType::route('/create'),
            'edit' => Pages\EditPriceType::route('/{record}/edit'),
        ];
    }
}
