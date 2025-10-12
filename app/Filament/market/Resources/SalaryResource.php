<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\SalaryResource\Pages\CreateSalary;
use App\Filament\market\Resources\SalaryResource\Pages\EditSalary;
use App\Filament\market\Resources\SalaryResource\Pages\ListSalaries;
use App\Filament\Resources\SalaryResource\Pages;
use App\Filament\Resources\SalaryResource\RelationManagers;
use App\Models\Salary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalaryResource extends Resource
{

    protected static ?string $pluralModelLabel='ادراج مرتبات';
    protected static string | \UnitEnum | null $navigationGroup='مرتبات';
    protected static ?int $navigationSort=1;

    protected static ?string $model = Salary::class;
    protected static ?string $pluralLabel='مرتب';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مرتبات');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                 ->required()
                 ->label('الاسم'),
                TextInput::make('sal')
                    ->required()
                    ->numeric()
                    ->label('المرتب'),
                Select::make('place_id')
                    ->label('مكان العمل')
                    ->relationship('Place', 'name')
                    ->searchable()
                    ->placeholder('قم باختيار مكان العمل .. او اتركه كما هو اذا كان العمل بالادارة')
                    ->live()
                    ->preload(),
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
            'index' => ListSalaries::route('/'),
            'create' => CreateSalary::route('/create'),
            'edit' => EditSalary::route('/{record}/edit'),


        ];
    }
}
