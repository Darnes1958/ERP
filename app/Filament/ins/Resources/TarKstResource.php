<?php

namespace App\Filament\ins\Resources;

use App\Enums\Status;
use App\Enums\Tar_type;
use App\Filament\ins\Resources\TarKstResource\Pages\CreateTarKst;
use App\Filament\ins\Resources\TarKstResource\Pages\EditTarKst;
use App\Filament\ins\Resources\TarKstResource\Pages\ListTarKsts;
use App\Filament\Resources\TarKstResource\Pages;
use App\Filament\Resources\TarKstResource\RelationManagers;
use App\Livewire\Traits\AksatTrait;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\TarKst;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TarKstResource extends Resource
{
    use AksatTrait;
    protected static ?string $model = TarKst::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='اقساط مرجعة';
    protected static ?int $navigationSort = 6;
    public static function getNavigationBadge(): ?string
    {
        return TarKst::count();
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
            ->pluralModelLabel('الترجيع')
            ->paginationPageOptions([5,10,25,50,100])
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي'),
                TextColumn::make('main_id')
                    ->label('رقم العقد'),
                TextColumn::make('tarkstable.name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHasMorph(
                            'tarkstable',
                            [Main::class, Main_arc::class],
                            function ($query, $type) use ($search) {
                                if ($type === 'App\Models\Main') {
                                    $query->whereHas('Customer', function ($query) use ($search) {
                                        return $query->where('name', 'like', '%'.$search.'%');
                                    });
                                } elseif ($type === 'App\Models\Main_arc') {
                                    $query->whereHas('Customer', function ($query) use ($search) {
                                        return $query->where('name', 'like', '%'.$search.'%');
                                    });
                                }
                            }
                        );
                    })
                    ->label('الاسم'),
                TextColumn::make('tar_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('kst')
                    ->label('المبلغ')
                    ->summarize(Sum::make()->label('')->numeric('2','.',',')),
                TextColumn::make('tar_type')
                    ->label('البيان'),
                TextColumn::make('haf_id')
                    ->label('رقم الحافظة'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make()
                 ->modalHeading('الغاء الترجيع')
                 ->after(function (Model $record){
                     if ($record->tar_type==Tar_type::من_الخطأ || $record->tar_type==Tar_type::من_الفائض){
                         $record->tarkstable->status=Status::غير_مرجع;
                         $record->tarkstable->save();
                     }
                     if ($record->tar_type==Tar_type::من_قسط_مخصوم){
                         self::StoreTran2($record->tarkstable->id,$record->tar_date,$record->kst,$record->haf_id);
                         self::SortTrans2($record->tarkstable->id);

                     }
                     if ($record->tar_type==Tar_type::من_قسط_مخصوم || $record->tar_type==Tar_type::من_الفائض)
                     self::MainTarseed2($record->main_id);
                 }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('الغاء الترجيع')
                        ->color('success')
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as  $item){
                                if ($item->tar_type==Tar_type:: من_الخطأ || $item->tar_type==Tar_type::من_الفائض  ){
                                    $item->tarkstable->status=Status::غير_مرجع;
                                    $item->tarkstable->save();
                                    $item->delete();
                                }
                                if ($item->tar_type==Tar_type::من_قسط_مخصوم){
                                    self::StoreTran2($item->tarkstable->id,$item->tar_date,$item->kst,$item->haf_id);
                                    self::SortTrans2($item->tarkstable->id);
                                }
                                if ($item->tar_type==Tar_type::من_قسط_مخصوم || $item->tar_type==Tar_type::من_الفائض)
                                    self::MainTarseed2($item->main_id);
                            }

                        }),
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
            'index' => ListTarKsts::route('/'),
            'create' => CreateTarKst::route('/create'),
            'edit' => EditTarKst::route('/{record}/edit'),
        ];
    }
}
