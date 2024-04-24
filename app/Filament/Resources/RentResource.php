<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentResource\Pages;
use App\Filament\Resources\RentResource\RelationManagers;
use App\Models\Rent;
use App\Models\Renttran;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel=' إيجارات';
    protected static ?string $navigationGroup='إيجارات';
    protected static ?int $navigationSort=1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('إيجارات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('الاسم'),
                TextInput::make('amount')
                    ->label('الإيجار'),
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Rent $record)=>
                        Renttran::where('rent_id',$record->id)->count()>0)  ,
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
            'index' => Pages\ListRents::route('/'),
            'create' => Pages\CreateRent::route('/create'),
            'edit' => Pages\EditRent::route('/{record}/edit'),
        ];
    }
}
