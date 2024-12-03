<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerResource\Pages;
use App\Filament\Resources\PerResource\RelationManagers;
use App\Models\Factory;
use App\Models\Hall_stock;
use App\Models\Item;
use App\Models\Per;
use App\Models\Place_stock;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PerResource extends Resource
{
    protected static ?string $model = Per::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='نقل أصناف بين المخازن والمعارض';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('placefrom')
                        ->label('مــن')
                        ->schema([
                            Select::make('place_from')
                                ->label('مــــن')
                                ->relationship('Placefrom', 'name')
                                ->searchable()
                                ->afterStateUpdated(function ($livewire){
                                    $livewire->dispatch('hall1-submitted');
                                })
                                ->required()
                                ->preload()
                                ->columnSpan(3)
                                ->live(),
                        ]),
                    Wizard\Step::make('placeto')
                        ->label('إلــي')
                        ->schema([
                            Select::make('place_to')
                                ->label('إلـــــي')
                                ->relationship('Placeto', 'name',
                                    modifyQueryUsing: fn (Builder $query,Forms\Get $get) =>
                                    $query->where('id','!=',$get('place_from'))
                                )
                                ->searchable()
                                ->afterStateUpdated(function ($livewire){
                                    $livewire->dispatch('hall2-submitted');
                                })
                                ->required()
                                ->preload()
                                ->columnSpan(3)
                                ->live(),
                            Forms\Components\DatePicker::make('per_date')
                                ->label('التاريخ')
                                ->required()
                                ->default(now()),
                            Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                        ]),
                    Wizard\Step::make('quantity')
                        ->label('الاصناف المنقولة')
                        ->schema([
                            TableRepeater::make('Per_tran')
                                ->hiddenLabel()
                                ->relationship()
                                ->headers([
                                    Header::make('الصنف')
                                        ->width('50%'),
                                    Header::make('الكمية')
                                        ->width('25%'),
                                    Header::make('الرصيد')
                                        ->width('25%'),
                                ])
                                ->schema([
                                    Select::make('item_id')
                                        ->relationship('Item', 'name',
                                            modifyQueryUsing: fn (Builder $query,Forms\Get $get) =>
                                             $query->whereIn('id',Place_stock::
                                                where('place_id', $get('../../place_from'))
                                                ->where('stock1','>',0)->pluck('item_id')),)
                                        ->searchable()
                                        ->required()
                                        ->preload()
                                        ->afterStateUpdated(function ($state,Forms\Set $set,Forms\Get $get){
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


                                    Forms\Components\TextInput::make('quantity')
                                        ->label('الكمية')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Get $get,$state,Forms\Set $set){
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
                        ])
                ])
                    ->extraAlpineAttributes([

                        '@hall1-submitted.window' => "step='placeto'",
                        '@hall2-submitted.window' => "step='quantity'",
                    ])

                    ->columnSpan(2),


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
            ->actions([
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
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
                    ->modalContent(fn (Per $record): View => view(
                        'filament.pages.reports.views.view-per-tran-widget',
                        ['per_id' => $record->id],
                    )),
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
            'index' => Pages\ListPers::route('/'),
            'create' => Pages\CreatePer::route('/create'),
            'edit' => Pages\EditPer::route('/{record}/edit'),
        ];
    }
}
