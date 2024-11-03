<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Models\Account;
use App\Models\Factory;
use App\Models\Hall_stock;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Sell;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='ادخال مخازن ';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('الاسم'),
                Forms\Components\Radio::make('place_type')
                    ->options(PlaceType::class)
                    ->label('')
                    ->inline()
                    ->inlineLabel()
                    ->default(0)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                 ->label('الاسم')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('del')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(function (Model $record){

                        $record->delete();
                    })
                    ->hidden(fn(Model $record): bool => Place_stock::where('place_id',$record->id)
                            ->where('stock1','>',0)->exists() ),
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
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
