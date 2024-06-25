<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccResource\Pages;
use App\Filament\Resources\AccResource\RelationManagers;
use App\Models\Acc;
use App\Models\Receipt;
use App\Models\Recsupp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use http\Client\Curl\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class AccResource extends Resource
{
    protected static ?string $model = Acc::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='حسابات مصرفية';
    protected static ?string $navigationGroup='مصارف وخزائن';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصارف');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                 ->hidden(fn(Acc $records): bool => Receipt::where('acc_id',$records->id)->count()>0
                                                 || Recsupp::where('acc_id',$records->id)->count()>0
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
            'index' => Pages\ListAccs::route('/'),
            'create' => Pages\CreateAcc::route('/create'),
            'edit' => Pages\EditAcc::route('/{record}/edit'),
        ];
    }
}
