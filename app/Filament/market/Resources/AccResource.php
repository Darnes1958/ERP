<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\AccResource\Pages\CreateAcc;
use App\Filament\market\Resources\AccResource\Pages\EditAcc;
use App\Filament\market\Resources\AccResource\Pages\ListAccs;
use App\Filament\market\Resources\AccResource\Pages;
use App\Filament\market\Resources\AccResource\RelationManagers;
use App\Models\Acc;
use App\Models\Receipt;
use App\Models\Recsupp;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccResource extends Resource
{
    protected static ?string $model = Acc::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='حسابات مصرفية';
    protected static string | \UnitEnum | null $navigationGroup='مصارف وخزائن';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصارف');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المصرف')
                    ->required()
                    ->autofocus()
                    ->columnSpan(2)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ])        ,
                TextInput::make('acc')
                    ->label('رقم الحساب')

                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ])  ,
                TextInput::make('raseed')
                 ->label('رصيد بداية المدة')
                 ->numeric()
                 ->required()
                 ,

            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              TextColumn::make('id')
                ->label('الرقم الألي'),
              TextColumn::make('name')
                    ->label('اسم المصرف'),
              TextColumn::make('raseed')
                    ->label('الرصيد الافتتاحي'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                 ->hidden(fn(Acc $record): bool => Receipt::where('acc_id',$record->id)->count()>0
                                                 || Recsupp::where('acc_id',$record->id)->count()>0
                                                 || !Auth::user()->can('الغاء مصارف')),
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
            'index' => ListAccs::route('/'),
            'create' => CreateAcc::route('/create'),
            'edit' => EditAcc::route('/{record}/edit'),
        ];
    }
}
