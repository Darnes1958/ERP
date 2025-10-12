<?php

namespace App\Filament\ins\Resources;

use App\Filament\ins\Resources\MainArcResource\Pages\CreateMainArc;
use App\Filament\ins\Resources\MainArcResource\Pages\EditMainArc;
use App\Filament\ins\Resources\MainArcResource\Pages\ListMainArcs;
use App\Filament\Resources\MainArcResource\Pages;
use App\Filament\Resources\MainArcResource\RelationManagers;
use App\Models\Main_arc;
use App\Models\MainArc;
use App\Models\Trans_arc;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MainArcResource extends Resource
{
    protected static ?string $model = Main_arc::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='الأرشيف';
    protected static ?int $navigationSort=8;
    public static function getNavigationBadge(): ?string
    {
        return Main_arc::count();
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
            ->recordUrl(
                null
            )
            ->columns([
                TextColumn::make('id')->label('رقم العقد')->sortable()->searchable(),
                TextColumn::make('Customer.name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('Bank.BankName')->label('المصرف')->searchable()->sortable(),
                TextColumn::make('acc')->label('رقم الحساب')->searchable()->sortable(),
                TextColumn::make('sul')->label('الاجمالي')->sortable(),
                TextColumn::make('pay')->label('المسدد')->sortable(),
                TextColumn::make('raseed')->label('الرصيد')->sortable(),
            ])
            ->filters([
                SelectFilter::make('bank_id')
                    ->relationship('Bank','BankName')
                    ->label('مصارف'),

            ])
            ->recordActions([
                Action::make('tran')
                    ->hiddenLabel()
                    ->iconButton()->color('primary')
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-m-eye')
                    ->url(fn (Main_arc $record): string => route('filament.ins.pages.cont-all-thing-arc', ['main_id'=>$record->id])),
                Action::make('toMain')
                    ->label('استرجاع')
                    ->color('primary')
                    ->size(Size::ExtraSmall)
                    ->requiresConfirmation()
                    ->action(function (Main_arc $record) {
                        $oldRecord= $record;
                        $newRecord = $oldRecord->replicate();
                        $newRecord->setTable('mains');
                        $newRecord->id=$record->id;
                        $newRecord->save();
                        Trans_arc::query()
                            ->where('main_id', $record->id)
                            ->each(function ($oldTran) {
                                $newTran = $oldTran->replicate();
                                $newTran->setTable('trans');
                                $newTran->save();
                                $oldTran->delete();
                            });
                        $record->delete();
                    })

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
            'index' => ListMainArcs::route('/'),
            'create' => CreateMainArc::route('/create'),
            'edit' => EditMainArc::route('/{record}/edit'),
        ];
    }
}
