<?php

namespace App\Filament\ins\Resources;

use App\Enums\Status;
use App\Enums\Tar_type;
use App\Filament\ins\Resources\OverkstResource\Pages\CreateOverkst;
use App\Filament\ins\Resources\OverkstResource\Pages\EditOverkst;
use App\Filament\ins\Resources\OverkstResource\Pages\ListOverksts;
use App\Filament\Resources\OverkstResource\Pages;
use App\Filament\Resources\OverkstResource\RelationManagers;
use App\Livewire\Traits\AksatTrait;
use App\Livewire\Traits\PublicTrait;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Overkst;
use App\Models\Taj;
use App\Models\Tarkst;
use Carbon\Carbon;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OverkstResource extends Resource
{
    protected static ?string $model = Overkst::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='خصم بالفائض';
    public static function getNavigationBadge(): ?string
    {
        return Overkst::count();
    }

    use AksatTrait,PublicTrait;


    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                 ->schema([
                   MorphToSelect::make('overkstable')
                     ->types([
                        Type::make(Main::class)
                            ->getOptionLabelFromRecordUsing(fn (Main $record): string => "{$record->Customer->name} {$record->sul}")
                            ->label('العقود القائمة'),
                         Type::make(Main_arc::class)
                             ->getOptionLabelFromRecordUsing(fn (Main_arc $record) => "{$record->Customer->name} {$record->sul}")
                             ->label('الأرشيف'),
                     ])
                     ->searchable()
                     ->preload()
                     ->label('فائض من'),
                     //  self::getMainSelectFromComponent(),
                     self::getDateFromComponent(),
                     self::getKstFromComponent(),
                     Hidden::make('user_id')
                         ->default(Auth::id())
                 ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('الصفحات')

            ->paginationPageOptions([5,10,25,50,100])
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي'),
                TextColumn::make('overkstable_id')
                    ->label('رقم العقد'),

                TextColumn::make('overkstable.Customer.name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHasMorph(
                            'overkstable',
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

                TextColumn::make('over_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('kst')
                    ->summarize(Sum::make()->label('')
                    ->numeric('2','.',','))
                    ->label('المبلغ'),
                TextColumn::make('status')
                    ->label('الحالة'),
                TextColumn::make('overkstable_type')
                    ->label('حالة العقد'),
            ])
            ->filters([
                SelectFilter::make('overkstable_type')
                 ->label('حالة العقد')
                ->options([
                    'App\Models\Main' => 'القائم',
                    'App\Models\Main_arc' => 'الأرشيف'
                ]),
                Filter::make('taj')
                    ->schema([
                        Select::make('taj_id')
                            ->label('بالمصرف التجميعي')
                            ->live()
                            ->searchable()
                            ->preload()
                            ->options(Taj::all()->pluck('TajName', 'id')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['taj_id'],
                            fn (Builder $subQuery) => $subQuery->whereIn('overkstable_id',Main::where('taj_id',$data['taj_id'])->pluck('id')));
                    }),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('Date1')
                            ->label('من تاريخ'),
                        DatePicker::make('Date2')
                            ->label('إلي تاريخ'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['Date1'] && ! $data['Date2']) { return null;   }
                        if ( $data['Date1'] && !$data['Date2'])
                            return 'ادخلت بتاريخ  ' . Carbon::parse($data['Date1'])->toFormattedDateString();
                        if ( !$data['Date1'] && $data['Date2'])
                            return 'حتي تاريخ  ' . Carbon::parse($data['Date2'])->toFormattedDateString();
                        if ( $data['Date1'] && $data['Date2'])
                            return 'ادخلت في الفترة من  ' . Carbon::parse($data['Date1'])->toFormattedDateString()
                                .' إلي '. Carbon::parse($data['Date1'])->toFormattedDateString();

                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Date1'],
                                fn (Builder $query, $date): Builder => $query->whereDate('over_date', '>=', $date),
                            )
                            ->when(
                                $data['Date2'],
                                fn (Builder $query, $date): Builder => $query->whereDate('over_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                 ->visible(function (Model $record) {
                     return $record->haf_id==0 && $record->status==Status::غير_مرجع;
                 }),
                DeleteAction::make()
                    ->visible(function (Model $record) {
                        return $record->haf_id==0 && $record->status==Status::غير_مرجع;
                    })
                ->after(function (Model $record) {
                    if ($record->overkstable_type=='App\Models\Main') $main=Main::find($record->overkstable_id);
                    else $main=Main_arc::find($record->overkstable_id);
                     self::OverTarseed2($main);
                }),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->status->value ==1,
            )
            ->toolbarActions([
                BulkAction::make('ترجيع')
                    ->color('success')
                    ->deselectRecordsAfterCompletion()

                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                            foreach ($records as  $item){
                                $item->tarkst()->create([
                                    'main_id' => $item->overkstable_id,
                                    'tar_date' => date('Y-m-d'),
                                    'kst' => $item->kst,
                                    'tar_type' => Tar_type::من_الفائض,
                                    'haf_id' => $item->haf_id,
                                    'user_id' => Auth::id(),
                                ]);

                                $item->update(['status'=>Status::مرجع]);

                                $count=Tarkst::where('main_id',$item->main_id)->count();
                                $sum=Tarkst::where('main_id',$item->main_id)->sum('kst');
                                Main::where('id',$item->main_id)->update([
                                    'tar_count'=>$count,
                                    'tar_kst'=>$sum,
                                ]);
                            }

                    }),
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
            'index' => ListOverksts::route('/'),
            'create' => CreateOverkst::route('/create'),
            'edit' => EditOverkst::route('/{record}/edit'),
        ];
    }
}
