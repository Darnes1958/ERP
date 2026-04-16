<?php

namespace App\Filament\Market\Pages;

use App\Filament\Tables\ItemTable;
use App\Models\Barcode;
use App\Models\Item;
use App\Models\Per;
use App\Models\PerTran;
use App\Models\Place;
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
use Filament\Schemas\Components\Section;
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
use Illuminate\Support\HtmlString;

class InpPer extends Page implements HasSchemas,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    protected string $view = 'filament.market.pages.inp-per';

    public $per;
    public $per_tran;
    public $place_from,$place_from_name;

    public $place_to;
    public $per_date;
    public $tableData=[];
    public function mount(){
        $this->perForm->fill([]);
        $this->tranForm->fill([]);
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
                              $this->place_from_name=Place::find($state)->name;
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

    public function ChkBarcode($barcode)
    {
        if (!$barcode) return;
        $res=Barcode::find($barcode);

        if ($res) $this->gotoQuantity($res->item_id,$res->id);
    }
    public function ChkItem($state){
        if ($state==null) return ;
        $res=Item::find($state);
        if ($res) $this->gotoQuantity($res->id,$res->barcode);

    }
    public function gotoQuantity($item_id,$barcode)
    {
        $place_stock=Place_stock::where('place_id', $this->place_from)
            ->where('item_id', $item_id)->where('stock1','>',0)->first();
        if (!$place_stock) return;

        $this->tranForm->fill([
                'barcode'=>$barcode,
                'item_id'=>$item_id,
                'stock'=>$place_stock->stock1
            ]);
            $this->dispatch('focus-next',next: 'quantity');

    }
    public function ChkQuantity(){

        if (!$this->per_tran['item_id'])
        {
            Notification::make()->title('يجب اختيار الصنف')->danger()->send();
            $this->dispatch('focus-next',next: 'barcode');
            return;

        }

        if (!$this->per_tran['quantity'] || $this->per_tran['quantity']<=0){
            Notification::make()->title('يجب اختيار الكمية')->danger()->send();
            $this->dispatch('focus-next',next: 'quantity');
            return;
        }
        if ($this->per_tran['quantity']> $this->per_tran['stock']){
            Notification::make()
                ->title('الرصيد لايسمح بهذه الكمية')
                ->send();
            return;
        };
        $this->putRecToArr($this->per_tran);

    }

    public function tranForm(Schema $schema): Schema
    {
        return $schema
            ->model(PerTran::class)
            ->statePath('per_tran')
            ->components([
               Section::make()
                ->schema([
                    TextInput::make('barcode')
                        ->exists(Barcode::class,column: 'id')
                        ->live()
                        ->extraInputAttributes(['wire:keydown.enter' => 'ChkBarcode($event.target.value)',])
                        ->autocomplete(false)
                        ->columnSpan(2)
                        ->id('barcode_id')
                        ->dehydrated(false),
                    Select::make('item_id')
                        ->relationship('Item', 'name',
                            modifyQueryUsing: fn (Builder $query) =>
                            $query->whereIn('id',Place_stock::
                            where('place_id', $this->place_from)
                                ->where('stock1','>',0)->pluck('item_id')),)
                        ->afterStateUpdated(function ($state){$this->ChkItem($state);})
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
                        ->id('item_id')
                        ->columnSpan(2)
                        ->live(),
                    TextInput::make('quantity')
                        ->label('الكمية')
                        ->live(onBlur: true)
                        ->extraInputAttributes(['wire:keydown.enter' => 'ChkQuantity',])
                        ->gt(0)
                        ->id('quantity')
                        ->required(),
                    TextInput::make('stock')
                        ->readOnly()
                        ->label(function (){
                            if($this->place_from_name)
                            return new HtmlString('<span class="text-indigo-700">رصيد : </span><span class="text-primary-600">'.$this->place_from_name.'</span>') ;
                            else return '-';
                        })
                        ->numeric()
                        ->mask(0.00)
                        ->dehydrated(false),
                ])
                ->columns(2),
            ]);
    }

    public function putRecToArr( $tran)
    {

        $One= array_column($this->tableData, 'item_id');
      $index = array_search( $tran['item_id'], $One);


        if  ($index!='') {
            $this->tableData[$index]['item_id']=$tran['item_id'];
            $this->tableData[$index]['barcode']=$tran['barcode'];
            $this->tableData[$index]['name']=Item::find($tran['item_id'])->name;
            $this->tableData[$index]['quantity']=$tran['quantity'];
        }
        else {
            $this->tableData[] =['item_id'=>$tran['item_id'],'barcode'=>$tran['barcode'],'name'=>Item::find($tran['item_id'])->name,'quantity'=>$tran['quantity'],];
        }
      //  $this->sumArr();
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn(): Collection=> collect($this->tableData))
            ->columns([
                TextColumn::make('barcode'),
                TextColumn::make('item_id'),
                TextColumn::make('name'),
                TextColumn::make('quantity'),
            ]);
    }


}
