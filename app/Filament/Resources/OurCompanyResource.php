<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\OurCompanyResource\Pages\ListOurCompanies;
use App\Filament\Resources\OurCompanyResource\Pages\CreateOurCompany;
use App\Filament\Resources\OurCompanyResource\Pages\EditOurCompany;
use App\Filament\Resources\OurCompanyResource\Pages;
use App\Filament\Resources\OurCompanyResource\RelationManagers;
use App\Models\OurCompany;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class OurCompanyResource extends Resource
{
  public static function shouldRegisterNavigation(): bool
  {
      return  auth()->user()->id==1;
  }
    protected static ?string $model = OurCompany::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
  protected static string | \UnitEnum | null $navigationGroup='Setting';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
              TextInput::make('Company')->unique(ignoreRecord: true)->required(),
              TextInput::make('CompanyName')->unique(ignoreRecord: true)->required(),
              TextInput::make('CompanyNameSuffix')->required(),
              TextInput::make('CompCode')->unique(ignoreRecord: true)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              TextColumn::make('Company'),
              TextColumn::make('CompanyName'),
              TextColumn::make('CompanyNameSuffix'),
              TextColumn::make('CompCode'),
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
            'index' => ListOurCompanies::route('/'),
            'create' => CreateOurCompany::route('/create'),
            'edit' => EditOurCompany::route('/{record}/edit'),
        ];
    }
}
