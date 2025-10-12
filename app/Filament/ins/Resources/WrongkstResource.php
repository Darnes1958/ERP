<?php

namespace App\Filament\ins\Resources;

use App\Enums\Status;
use App\Enums\Tar_type;
use App\Filament\ins\Resources\WrongkstResource\Pages\CreateWrongkst;
use App\Filament\ins\Resources\WrongkstResource\Pages\EditWrongkst;
use App\Filament\ins\Resources\WrongkstResource\Pages\ListWrongksts;
use App\Filament\Resources\WrongkstResource\Pages;
use App\Filament\Resources\WrongkstResource\RelationManagers;
use App\Livewire\Traits\AksatTrait;
use App\Livewire\Traits\MainTrait;
use App\Models\Main;
use App\Models\Tran;
use App\Models\Wrongkst;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class WrongkstResource extends Resource
{
    use MainTrait;
    use AksatTrait;
    protected static ?string $model = Wrongkst::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='أقساط واردة بالخطأ';
    protected static ?int $navigationSort = 4;
    public static function getNavigationBadge(): ?string
    {
        return Wrongkst::count();
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
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                 ->label('الاسم'),
                TextColumn::make('Taj.TajName')
                    ->searchable()
                    ->sortable()
                    ->label('المصرف'),
                TextColumn::make('acc')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->label('رقم الحساب'),
                TextColumn::make('wrong_date')
                    ->sortable()
                   ->label('التاريخ'),
                TextColumn::make('kst')
                    ->label('المبلغ'),
                TextColumn::make('status')
                    ->label('الحالة'),

            ])
            ->recordUrl(
                null
            )
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->status->value === 1,
            )
            ->filters([
                SelectFilter::make('taj_id')
                    ->relationship('Taj','TajName')
                    ->label('مصارف'),
                SelectFilter::make('status')
                    ->options(Status::class)
                    ->label('الحالة'),

            ])
            ->recordActions([
                Action::make('toMain')
                 ->label('تصحيح')
                ->icon('heroicon-o-check')
                ->iconButton()
                ->visible(function (Model $record): bool {
                    return $record->status->value==1;
                })
                ->color('success')
                ->schema([
                        Select::make('main_id')
                         ->label('العقد')
                         ->options(function (Model $record) {
                             return Main::where('taj_id',$record->taj_id)->join('customers','customers.id','mains.customer_id')
                                 ->pluck('customers.name', 'mains.id');
                         })

                        ->searchable()
                        ->preload()
                        ->required()
                    ])
                ->action(function (Model $record,array $data) {
                    $wrong=Wrongkst::where('acc',$record->acc)->get();
                    foreach ($wrong as $wr) {
                        $res= Tran::create([
                            'main_id'=>$data['main_id'],
                            'ksm'=>$wr->kst,
                            'ksm_type_id'=>2,
                            'ksm_date'=>$wr->wrong_date,
                            'user_id'=>Auth::id(),
                            'ser'=>Tran::where('main_id',$data['main_id'])->max('ser')+1,
                            'kst_date'=>self::getKst_date2($data['main_id']),
                            'haf_id'=>$wr->haf_id,
                        ]);
                        $wr->status=3;
                        $wr->save();
                    }
                    Main::find($data['main_id'])->update(['acc'=>$record->acc]);
                    self::MainTarseed2($data['main_id']);
                })
            ])
            ->toolbarActions([
                BulkAction::make('ترجيع')
                    ->color('success')
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as  $item){
                            $item->tarkst()->create([
                                'tar_date' => date('Y-m-d'),
                                'kst' => $item->kst,
                                'tar_type' => Tar_type::من_الخطأ,
                                'haf_id' => $item->haf_id,
                                'user_id' => Auth::id(),
                            ]);
                            $item->status=2;
                            $item->save();
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
            'index' => ListWrongksts::route('/'),
            'create' => CreateWrongkst::route('/create'),
            'edit' => EditWrongkst::route('/{record}/edit'),
        ];
    }
}
