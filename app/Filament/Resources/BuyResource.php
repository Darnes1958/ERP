<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuyResource\Pages;
use App\Filament\Resources\BuyResource\RelationManagers;
use App\Models\Buy;
use App\Models\Buys_work;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BuyResource extends Resource
{
    protected static ?string $model = Buy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الالي'),
                TextColumn::make('Supplier.name')
                    ->label('اسم المورد'),
                TextColumn::make('order_date')
                    ->label('التاريخ'),
                TextColumn::make('tot')
                    ->label('اجمالي الفاتورة'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->label('الباقي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\Action::make('buytran')
                    ->label('تعديل')
                    ->icon('heroicon-m-pencil')
                    ->color('info')
                    ->url(fn(Model $record) => self::getUrl('buyedit', ['record' => $record]))


            ])

            ;
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
            'index' => Pages\ListBuys::route('/'),
            'create' => Pages\CreateBuy::route('/create'),
            'edit' => Pages\EditBuy::route('/{record}/edit'),
            'buyedit' => Pages\BuyEdit::route('/{record}/buyedit'),
        ];
    }
}
