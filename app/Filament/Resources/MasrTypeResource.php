<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasrTypeResource\Pages;
use App\Filament\Resources\MasrTypeResource\RelationManagers;
use App\Models\Masr_type;

use App\Models\Masrofat;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasrTypeResource extends Resource
{
    protected static ?string $model = Masr_type::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralLabel='انواع المصروفات';
    protected static ?string $navigationGroup='مصروفات';
    protected static ?int $navigationSort=2;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصروفات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Tables\Columns\TextColumn::make('name')
                ->label('البيان'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Masr_type $record)=>
                        Masrofat::where('masr_type_id',$record->id)->count()>0),
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
            'index' => Pages\ListMasrTypes::route('/'),
            'create' => Pages\CreateMasrType::route('/create'),
            'edit' => Pages\EditMasrType::route('/{record}/edit'),
        ];
    }
}
