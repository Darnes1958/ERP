<?php

namespace App\Filament\market\Resources;

use App\Enums\PlaceType;
use App\Filament\market\Resources\PlaceResource\Pages\CreatePlace;
use App\Filament\market\Resources\PlaceResource\Pages\EditPlace;
use App\Filament\market\Resources\PlaceResource\Pages\ListPlaces;
use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Models\Account;
use App\Models\Factory;
use App\Models\Hall_stock;
use App\Models\Place;
use App\Models\Place_stock;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='ادخال مخازن ';
    protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=6;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('الاسم'),
                Radio::make('place_type')
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
                TextColumn::make('name')
                 ->label('الاسم')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('del')
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
            'index' => ListPlaces::route('/'),
            'create' => CreatePlace::route('/create'),
            'edit' => EditPlace::route('/{record}/edit'),
        ];
    }
}
