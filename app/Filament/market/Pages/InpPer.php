<?php

namespace App\Filament\Market\Pages;

use App\Filament\Tables\ItemTable;
use App\Models\Barcode;
use App\Models\Per;
use App\Models\PerTran;
use App\Models\Place_stock;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TableSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class InpPer extends Page implements HasSchemas,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    protected string $view = 'filament.market.pages.inp-per';

    public $per;
    public $per_tran;
    public $place_from;
    public $place_to;
    public $per_date;
    public $kydedata=[];
    public function mount(){
        $this->perForm->fill([]);
    }
    public  function perForm(Schema $schema): Schema
    {
        return $schema
            ->model(Per::class)
            ->statePath('per')
            ->components([
                Grid::make()
                  ->schema([
                      Select::make('place_from')
                          ->label('مــــن')
                          ->relationship('Placefrom', 'name')
                          ->searchable()
                          ->afterStateUpdated(function ($livewire,$state,Set $set){
                              $this->place_from=$state;
                              $livewire->dispatch('hall1-submitted');
                          })
                          ->required()
                          ->preload()
                          ->columnSpan(2)
                          ->live(),

                      Select::make('place_to')
                          ->label('إلـــــي')
                          ->relationship('Placeto', 'name',
                              modifyQueryUsing: fn (Builder $query,Get $get) =>
                              $query->where('id','!=',$get('place_from'))
                          )
                          ->searchable()
                          ->afterStateUpdated(function ($state,$livewire){
                              $this->place_to=$state;
                              $livewire->dispatch('hall2-submitted');
                          })
                          ->required()
                          ->preload()
                          ->columnSpan(2)
                          ->live(),
                      DatePicker::make('per_date')
                          ->label('التاريخ')
                          ->required()
                          ->default(fn()=>now()),
                      Hidden::make('user_id')->default(auth()->id()),

                  ])
                  ->columns(5)

            ]);


    }

    public function tranForm(Schema $schema): Schema
    {
        return $schema
            ->model(PerTran::class)
            ->statePath('per_tran')
            ->components([
                Hidden::make('place_id')

                    ->dehydrated(false),
                TextInput::make('barcode')
                    ->afterStateUpdated(function ($state,Set $set){

                        if ($state){
                            $res=Barcode::find($state);
                            if ($res) {
                                $set('item_id',$res->item_id);
                                $set('stock',Place_stock::where('place_id', $this->place_from)
                                    ->where('item_id', $res->item_id)->first()->stock1
                                );
                            }

                        }
                    })
                    ->live()
                    ->dehydrated(false),
                Select::make('item_id')
                    ->relationship('Item', 'name',
                        modifyQueryUsing: fn (Builder $query) =>
                        $query->whereIn('id',Place_stock::
                        where('place_id', $this->place_from)
                            ->where('stock1','>',0)->pluck('item_id')),)
                    ->searchable()
                    ->suffixAction(
                        Action::make('select_item')
                            ->label('بحث عن الصنف')
                            ->icon(Heroicon::MagnifyingGlass)
                            ->schema([
                                TableSelect::make('item_id')
                                    ->relationship('Item','name')
                                    ->tableConfiguration(ItemTable::class)
                                    ->columnSpanFull()
                                    ->tableArguments(function (): array {

                                        return [
                                            'place_id' =>  $this->placeFrom,
                                            'noZero' => 1,
                                        ];
                                    })
                            ])
                            ->action(function (array $data,Set $set,Get $get){
                                $set('item_id',$data['item_id']);
                                $set('barcode',Barcode::where('item_id', $data['item_id'])->first()->id);
                                $set('stock',Place_stock::where('place_id', $this->placeFrom)
                                    ->where('item_id', $data['item_id'])->first()->stock1
                                );
                            })

                    )
                    ->required()
                    ->distinct()
                    ->preload()

                    ->live(),


                TextInput::make('quantity')
                    ->label('الكمية')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get,$state,Set $set){
                        $stock=Place_stock::where('place_id', $this->place_from)
                            ->where('item_id',$get('item_id'))->first();
                        if (!$stock || $state > $stock->stock1){
                            Notification::make()
                                ->title('الرصيد لايسمح بهذه الكمية')
                                ->send();
                            $set('quant',0);
                        };
                    })
                    ->gt(0)
                    ->required(),
                TextInput::make('stock')
                    ->readOnly()
                    ->numeric()
                    ->mask(0.00)
                    ->dehydrated(false),
            ]);
    }

    public function putRecToArr($tran)
    {
     //   if (!$this->account_id || !Account::find($this->account_id || !Account::find($this->account_id)->is_active))
     //   {
     //       Notification::make()->title('يجب ادخال حساب صحيح')->danger()->send();
     //       $this->dispatch('gotoitem', test: 'account_id');
     //       return ;
     //   }
        $One= array_column($this->kydedata, 'item_id');
        $index = array_search( $tran->item_id, $One);


        if  ($index!='') {
            $this->kydedata[$index]['item_id']=$tran->item_id;
            $this->kydedata[$index]['barcode']=$tran->barcode;
            $this->kydedata[$index]['name']=$tran->name;
            $this->kydedata[$index]['quantity']=$tran->quantity;
        }
        else {
            $this->kydedata[] =['item-id'=>$tran->item_id,'barcode'=>$tran->barcode,'name'=>$tran->name,'quantity'=>$tran->quantity,];
        }
      //  $this->sumArr();
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn(): Collection=> collect($this->kydedata))
            ->columns([
                TextColumn::make('barcode'),
                TextColumn::make('item_id'),
                TextColumn::make('name'),
                TextColumn::make('quantity'),
            ]);
    }


}
