<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FromexcelResource\Pages\CreateFromexcel;
use App\Filament\Resources\FromexcelResource\Pages\EditFromexcel;
use App\Filament\Resources\FromexcelResource\Pages\ListFromexcels;
use App\Filament\Resources\FromexcelResource\RelationManagers;

use App\Models\Fromexcel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FromexcelResource extends Resource
{

    protected static ?string $model = Fromexcel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup='Setting';
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
                TextColumn::make('main_id')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('acc')->searchable()->sortable(),
                TextColumn::make('ksm_date'),
                TextColumn::make('ksm'),
                TextColumn::make('taj_id'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
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
            'index' => ListFromexcels::route('/'),
            'create' => CreateFromexcel::route('/create'),
            'edit' => EditFromexcel::route('/{record}/edit'),
        ];
    }
    public static function getWidgets(): array
    {
        return [
            \App\Livewire\FromExcelWidget::class,
        ];
    }
}
