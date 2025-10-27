<?php

namespace App\Filament\ins\Resources;

use App\Enums\Morahela;
use App\Filament\ins\Resources\HafithaResource\Pages\CreateHafitha;
use App\Filament\ins\Resources\HafithaResource\Pages\EditHafitha;
use App\Filament\ins\Resources\HafithaResource\Pages\ListHafithas;

use App\Livewire\Traits\AksatTrait;
use App\Models\Hafitha;
use App\Models\HafithaTran;
use App\Models\Main;
use App\Models\Overkst;
use App\Models\Tran;
use App\Models\Trans_arc;
use App\Models\Wrongkst;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HafithaResource extends Resource
{
    use AksatTrait;
    protected static ?string $model = Hafitha::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='حوافظ';

    protected static ?int $navigationSort=9;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('taj_id')
                 ->relationship('Taj', 'TajName')
                 ->searchable()
                 ->preload(),
                Hidden::make('status')->default(0),
                Hidden::make('auto')->default(0),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('status'),
                TextColumn::make('id')->label('الرقم الألي')->sortable()->searchable(),
                TextColumn::make('Taj.TajName')->label('المصرف')->searchable()->sortable(),

                TextColumn::make('from_date')->label('تاريخ بداية الحافظ')->toggleable()->toggledHiddenByDefault()->searchable()->sortable(),
                TextColumn::make('to_date')->label('تاريخ نهاية الحافظة')->toggleable()->toggledHiddenByDefault()->searchable()->sortable(),
                TextColumn::make('tot')->label('الاجمالي')->searchable()->sortable(),
                TextColumn::make('morahel')->label('المرحل')->sortable(),
                TextColumn::make('over_kst')->label('الفائض')->sortable(),
                TextColumn::make('over_kst_arc')->label('الفائض من الارشيف')->sortable(),
                TextColumn::make('half')->label('الجزئي')->sortable(),
                TextColumn::make('wrong_kst')->label('بالخطأ')->sortable(),

            ])
            ->filters([
                SelectFilter::make('status')->options(Morahela::class)->label('الحالة'),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query->orderBy('status')->orderBy('id');
            })
            ->defaultKeySort(false)
            ->recordActions([
                Action::make('Delete Hafitha')
                    ->color('danger')
                    ->action(function ($record){
                        DB::connection(Auth()->user()->company)->beginTransaction();
                        try {
                                Tran::where('haf_id',$record->id)->delete();
                                Trans_arc::where('haf_id',$record->id)->delete();
                                Overkst::where('haf_id',$record->id)->delete();
                                Wrongkst::where('haf_id',$record->id)->delete();
                                HafithaTran::where('hafitha_id',$record->id)->delete();
                                $mains=Main::where('taj_id',$record->taj_id)->get();
                                foreach ($mains as $main){
                                    self::MainTarseed2($main->id);
                                }


                                $record->delete();


                            DB::connection(Auth()->user()->company)->commit();
                                Notification::make()
                                    ->title('تم حذف الحافظة')
                                    ->success()
                                    ->send();

                        }
                        catch (\Exception $e) {
                            Notification::make()
                                ->title('حدث خطأ !!')
                                ->color('danger')
                                ->icon('heroicon-o-x-circle')
                                ->danger()
                                ->send();
                            info($e);
                            DB::connection(Auth()->user()->company)->rollback();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(Auth::id()==1),
                Action::make('see')
                    ->label('ادخال أقساط')
                    ->visible(fn(Model $record): bool=>$record->status->value==0)
                    ->url(fn ($record) => route('filament.ins.pages.inp-hafitha-tran.{record}', ['record' => $record->id]))
            ])
            ->recordUrl(false)
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
            'index' => ListHafithas::route('/'),
            'create' => CreateHafitha::route('/create'),
            'edit' => EditHafitha::route('/{record}/edit'),

        ];
    }
}
