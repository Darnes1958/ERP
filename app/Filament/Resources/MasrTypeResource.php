<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\MasrTypeResource\Pages\ListMasrTypes;
use App\Filament\Resources\MasrTypeResource\Pages\CreateMasrType;
use App\Filament\Resources\MasrTypeResource\Pages\EditMasrType;
use App\Filament\Resources\MasrTypeResource\Pages;
use App\Filament\Resources\MasrTypeResource\RelationManagers;
use App\Models\Masr_type;

use App\Models\Masrofat;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasrTypeResource extends Resource
{
    protected static ?string $model = Masr_type::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralLabel='انواع المصروفات';
    protected static string | \UnitEnum | null $navigationGroup='مصروفات';
    protected static ?int $navigationSort=2;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصروفات');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('البيان')
                    ->required()
                    ->autofocus()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ])        ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label('البيان'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn(Masr_type $record)=>
                        Masrofat::where('masr_type_id',$record->id)->count()>0),
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
            'index' => ListMasrTypes::route('/'),
            'create' => CreateMasrType::route('/create'),
            'edit' => EditMasrType::route('/{record}/edit'),
        ];
    }
}
