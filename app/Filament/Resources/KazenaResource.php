<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\KazenaResource\Pages\ListKazenas;
use App\Filament\Resources\KazenaResource\Pages\CreateKazena;
use App\Filament\Resources\KazenaResource\Pages\EditKazena;
use App\Filament\Resources\KazenaResource\Pages;
use App\Filament\Resources\KazenaResource\RelationManagers;
use App\Models\Kazena;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class KazenaResource extends Resource
{
    protected static ?string $model = Kazena::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='خزائن';
    protected static string | \UnitEnum | null $navigationGroup='مصارف وخزائن';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال خزينة');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الخزينة')
                    ->required()
                    ->autofocus()
                    ->columnSpan(2)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ]) ,
                Select::make('user_id')
                 ->label('المستخدم')
                 ->searchable()
                 ->preload()
                 ->options(User::
                      where('company',Auth::user()->company)
                      ->where('id','!=',1)
                     ->pluck('name','id')),
                TextInput::make('balance')
                    ->label('رصيد بداية المدة')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                 ->label('اسم الخزينة'),
                TextColumn::make('balance')
                    ->label('رصيد بداية المدة'),
                TextColumn::make('user_name')
                    ->state(function (Kazena $record) {if ($record->user_id) {return User::find($record->user_id)->name;} else return null;} )
                    ->label('المستخدم'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn(Kazena $record)=>
                        Receipt::where('kazena_id',$record->id)->count()>0
                        || Recsupp::where('kazena_id',$record->id)->count()>0
                        || !Auth::user()->can('الغاء خزينة'))
                ,
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
            'index' => ListKazenas::route('/'),
            'create' => CreateKazena::route('/create'),
            'edit' => EditKazena::route('/{record}/edit'),
        ];
    }
}
