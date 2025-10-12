<?php

namespace App\Filament\ins\Resources;

use App\Filament\ins\Resources\DateofexcelResource\Pages\CreateDateofexcel;
use App\Filament\ins\Resources\DateofexcelResource\Pages\EditDateofexcel;
use App\Filament\ins\Resources\DateofexcelResource\Pages\ListDateofexcels;
use App\Filament\Resources\DateofexcelResource\Pages;
use App\Filament\Resources\DateofexcelResource\RelationManagers;
use App\Models\Dateofexcel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DateofexcelResource extends Resource
{
    protected static ?string $model = Dateofexcel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup='Setting';
    protected static ?int $navigationSort=6;
    public static function shouldRegisterNavigation(): bool
    {
        return  auth()->user()->id==1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make('Taj.TajName')->sortable()->searchable(),
                TextColumn::make('date_begin')->sortable()->searchable(),
                TextColumn::make('date_end')->sortable()->searchable(),
                TextColumn::make('created_at')->sortable()->searchable()
            ])
            ->filters([
                SelectFilter::make('التجميعي')
                 ->relationship('Taj','TajName')
            ],FiltersLayout::AboveContent)
            ->recordActions([
                DeleteAction::make(),
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
            'index' => ListDateofexcels::route('/'),
            'create' => CreateDateofexcel::route('/create'),
            'edit' => EditDateofexcel::route('/{record}/edit'),
        ];
    }
}
