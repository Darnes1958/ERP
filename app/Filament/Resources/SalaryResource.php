<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryResource\Pages;
use App\Filament\Resources\SalaryResource\RelationManagers;
use App\Models\Salary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Auth;

class SalaryResource extends Resource
{

    protected static ?string $pluralModelLabel='ادراج مرتبات';
    protected static ?string $navigationGroup='مرتبات';
    protected static ?int $navigationSort=1;

    protected static ?string $model = Salary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مرتبات');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                 ->required()
                 ->label('الاسم'),
                TextInput::make('sal')
                    ->required()
                    ->numeric()
                    ->label('المرتب'),
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
              TextColumn::make('sal')
                  ->label('المرتب')
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
            'index' => Pages\ListSalaries::route('/'),
            'create' => Pages\CreateSalary::route('/create'),
            'edit' => Pages\EditSalary::route('/{record}/edit'),


        ];
    }
}
