<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KazenaResource\Pages;
use App\Filament\Resources\KazenaResource\RelationManagers;
use App\Models\Kazena;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class KazenaResource extends Resource
{
    protected static ?string $model = Kazena::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='خزائن';
    protected static ?string $navigationGroup='مصارف وخزائن';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال خزينة');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('اسم الخزينة')
                    ->required()
                    ->autofocus()
                    ->columnSpan(2)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ]) ,
                Forms\Components\Select::make('user_id')
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
                Tables\Columns\TextColumn::make('name')
                 ->label('اسم الخزينة'),
                Tables\Columns\TextColumn::make('balance')
                    ->label('رصيد بداية المدة'),
                Tables\Columns\TextColumn::make('user_name')
                    ->state(function (Kazena $record) {if ($record->user_id) {return User::find($record->user_id)->name;} else return null;} )
                    ->label('المستخدم'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Kazena $record)=>
                        Receipt::where('kazena_id',$record->id)->count()>0
                        || Recsupp::where('kazena_id',$record->id)->count()>0
                        || !Auth::user()->can('الغاء خزينة'))
                ,
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
            'index' => Pages\ListKazenas::route('/'),
            'create' => Pages\CreateKazena::route('/create'),
            'edit' => Pages\EditKazena::route('/{record}/edit'),
        ];
    }
}
