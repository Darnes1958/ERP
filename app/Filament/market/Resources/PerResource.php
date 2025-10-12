<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\PerResource\Pages\CreatePer;
use App\Filament\market\Resources\PerResource\Pages\EditPer;
use App\Filament\market\Resources\PerResource\Pages\ListPers;
use App\Filament\Resources\PerResource\Pages;
use App\Filament\Resources\PerResource\RelationManagers;
use App\Livewire\Traits\PublicTrait;
use App\Models\Per;
use App\Models\PerTran;
use App\Models\Place_stock;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;


class PerResource extends Resource
{
use PublicTrait;
    protected static ?string $model = Per::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='نقل أصناف بين المخازن والمعارض';
    protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('نقل أصناف');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('placefrom')
                        ->label('مــن')
                        ->schema([
                            Select::make('place_from')
                                ->label('مــــن')
                                ->relationship('Placefrom', 'name')
                                ->searchable()
                                ->afterStateUpdated(function ($livewire){
                                   // $livewire->dispatch('hall1-submitted');
                                })
                                ->required()
                                ->preload()
                                ->columnSpan(2)
                                ->live(),
                        ]),
                    Step::make('placeto')
                        ->label('إلــي')
                        ->schema([
                            Select::make('place_to')
                                ->label('إلـــــي')
                                ->relationship('Placeto', 'name',
                                    modifyQueryUsing: fn (Builder $query,Get $get) =>
                                    $query->where('id','!=',$get('place_from'))
                                )
                                ->searchable()
                                ->afterStateUpdated(function ($livewire){
                                 //   $livewire->dispatch('hall2-submitted');
                                })
                                ->required()
                                ->preload()
                                ->columnSpan(3)
                                ->live(),
                            DatePicker::make('per_date')
                                ->label('التاريخ')
                                ->required()
                                ->default(now()),
                            Hidden::make('user_id')->default(auth()->id()),
                        ])->columnSpan(2),
                    Step::make('quantity')
                        ->label('الاصناف المنقولة')
                        ->schema([
                            Repeater::make('Per_tran')
                                ->hiddenLabel()
                                ->columnSpanFull()
                                ->relationship('Per_tran')
                                ->table([
                                    TableColumn::make('الصنف')
                                        ->width('50%'),
                                    TableColumn::make('الكمية')
                                        ->width('25%'),
                                    TableColumn::make('الرصيد')
                                        ->width('25%'),
                                ])
                                ->addActionLabel('إضافة صنف')

                                ->schema([
                                    Select::make('item_id')
                                        ->relationship('Item', 'name',
                                            modifyQueryUsing: fn (Builder $query,Get $get) =>
                                             $query->whereIn('id',Place_stock::
                                                where('place_id', $get('../../place_from'))
                                                ->where('stock1','>',0)->pluck('item_id')),)
                                        ->searchable()
                                        ->required()
                                        ->preload()
                                        ->afterStateUpdated(function ($state,Set $set,Get $get){
                                                $set('stock',Place_stock::where('place_id', $get('../../place_from'))
                                                                               ->where('item_id', $get('item_id'))->first()->stock1
                                                );
                                        })
                                        ->disableOptionWhen(function ($value, $state, Get $get) {
                                            return collect($get('../*.item_id'))
                                                ->reject(fn($id) => $id == $state)
                                                ->filter()
                                                ->contains($value);
                                        })
                                        ->live(),


                                    TextInput::make('quantity')
                                        ->label('الكمية')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get,$state,Set $set){
                                            $stock=Place_stock::where('place_id', $get('../../place_from'))
                                                ->where('item_id',$get('item_id'))->first();
                                            if (!$stock || $state > $stock->stock1){
                                                Notification::make()
                                                    ->title('الرصيد لايسمح بهذه الكمية')
                                                    ->send();
                                                $set('quant',0);
                                            };
                                        })
                                        ->required(),
                                    TextInput::make('stock')
                                        ->readOnly()
                                        ->numeric()
                                        ->mask(0.00)
                                        ->dehydrated(false),
                                ])
                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get): array {
                                    $placefrom=Place_stock::where('item_id',$data['item_id'])
                                        ->where('place_id',$get('place_from'))->first();

                                    $placefrom->stock1 -= $data['quantity'];
                                    $placefrom->save();
                                    $placeto=Place_stock::where('item_id',$data['item_id'])
                                        ->where('place_id',$get('place_to'))->first();
                                    if ($placeto){
                                        $placeto->stock1+= $data['quantity'];
                                        $placeto->save();
                                    } else {
                                        Place_stock::create(['item_id' => $data['item_id'], 'place_id' => $get('place_to'), 'stock1' => $data['quantity']]);
                                    }
                                    return $data;
                                })
                        ])->columnSpanFull()
                ])

                ->extraAlpineAttributes([
                        '@hall1-submitted.window' => "step='placeto'",
                        '@hall2-submitted.window' => "step='quantity'",
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('per_date')
                    ->label('التاريخ')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Placefrom.name')
                    ->label('مــــــــن')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Placeto.name')
                    ->label('إلــــــــي')
                    ->sortable()
                    ->searchable(),
            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('del')
                    ->icon('heroicon-o-trash')
                    ->modalHeading('الغاء التصنيع')

                    ->iconSize(IconSize::Small)
                    ->requiresConfirmation()
                    ->color('danger')
                    ->iconButton()
                    ->action(function (Model $record){
                        $minus=false;
                        foreach ($record->Per_tran as $item) {
                            if (Place_stock::where('item_id', $item['item_id'])
                                    ->where('place_id', $record->place_to)
                                    ->first()->stock1 < $item['quantity']) {
                                Notification::make()->warning()->title('يوجد صنف او اصناف رصيدها لا يسمح')
                                    ->body('يجب مراجعة الكميات')
                                    ->persistent()
                                    ->send();
                                    $minus=true;
                                    break;

                            }
                        }
                        if ($minus) return;

                        foreach ($record->Per_tran as $tran) {
                            $place=Place_stock::where('item_id',$tran->item_id)
                                ->where('place_id',$record->place_from)->first();
                            $place->stock1+=$tran->quantity;
                            $place->save();
                            $place=Place_stock::where('item_id',$tran->item_id)
                                ->where('place_id',$record->place_to)->first();
                            $place->stock1-=$tran->quantity;
                            $place->save();
                        }

                        $record->delete();
                    }),
                Action::make('the_tran')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-m-list-bullet')
                    ->color('success')
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
                    ->modalContent(fn (Per $record): View => view(
                        'filament.market.pages.reports.views.view-per-tran-widget',
                        ['per_id' => $record->id],
                    )),
                Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->color('blue')
                    ->action(function (Per $record) {
                        $per=$record;
                        $res=PerTran::where('per_id',$record->id)->get();
                        return Response::download(self::ret_spatie($res,
                            'PDF.pdf-rep-per',[
                                'per'=>$per,
                            ]
                        ), 'filename.pdf', self::ret_spatie_header());

                    })
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
            'index' => ListPers::route('/'),
            'create' => CreatePer::route('/create'),
            'edit' => EditPer::route('/{record}/edit'),
        ];
    }
}
