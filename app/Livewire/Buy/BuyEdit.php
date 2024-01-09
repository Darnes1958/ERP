<?php

namespace App\Livewire\Buy;

use App\Livewire\Forms\BuyFormEdit;
use App\Livewire\Forms\BuyTranFormEdit;
use App\Models\Place;
use App\Models\Price_type;
use App\Models\Setting;
use Illuminate\Support\HtmlString;
use Livewire\Component;

use App\Enums\PlaceType;
use App\Livewire\Forms\BuyForm;
use App\Livewire\Forms\BuyTranForm;
use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\Item;


use App\Models\Place_stock;
use App\Models\Price_buy;
use App\Models\Supplier;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;


class BuyEdit extends Component implements HasForms,HasTable,HasActions
{
  use InteractsWithForms,InteractsWithTable,InteractsWithActions;

  public ?array $buyData = [];
  public ?array $buytranData = [];

  public $id;
  public $buy_id;
  protected function getForms(): array
  {
    return [
      'buyFormBlade',
      'buytranFormBlade',
    ];
  }



  public BuyFormEdit $buyForm;
  public BuyTranFormEdit $buyTranForm;

  public function ChkBarcode(){
    $res=Barcode::find($this->buytranData['barcode_id']);
    if (! $res)
      Notification::make()
        ->title('هذا الباركود غير مخزون ')
        ->icon('heroicon-o-check')
        ->iconColor('success')
        ->send();
    else $this->dispatch('goto', test: 'q1');
  }
  public function add_rec(){
    $this->buyTranForm->loadFromBuyTran($this->buy_id,$this->buytranData);
    $this->buyTranForm->place_id=$this->buyForm->place_id;

    $res=Buy_tran::where('buy_id',$this->buy_id)
      ->where('item_id',$this->buyTranForm->item_id)->get();

    if ($res->count()>0)
      Buy_tran::where('buy_id',$this->buy_id)
        ->where('item_id',$this->buyTranForm->item_id)
        ->update($this->buyTranForm->all());
    else  Buy_tran::create($this->buyTranForm->all());

    $this->buyTranForm->reset();
    $this->buytranFormBlade->fill($this->buyTranForm->toArray());
    $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
    $baky=$tot-Buy::find($this->buy_id)->pay;
    Buy::find($this->buy_id)->update([
      'tot'=>$tot,
      'baky'=>$baky,

    ]);

    $this->buyForm->loadFromBuy($this->buy_id);
    $this->buyFormBlade->fill($this->buyForm->toArray());


    $this->dispatch('goto', test: 'barcode_id');
  }

  public function buyFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
        Select::make('id')
          ->label('رقم الفاتورة')
          ->options(DB::connection('other')->table('buys')
            ->join('suppliers','buys.supplier_id','=','suppliers.id')
            ->selectRaw('\'المورد : \'+suppliers.name+\'  اجمالي الفاتورة : \'+str(tot) as name,buys.id') ->latest('buys.created_at')->pluck('name','id'))
          ->searchable()
          ->live()
          ->preload()
          ->inlineLabel()
          ->columnSpan(2)
          ->afterStateUpdated(function ($state){
            if(Buy_tran::where('buy_id',$state)
                          ->where('q1','!=',DB::raw('qs1'))->exists()) {

                Notification::make()
                    ->title(fn () => new HtmlString('الفاتورة رقم <span class="text-primary-400">'.$state.'</span> تم بيع أصناف منها ولا يجوز تعديلها '))
                    ->icon('heroicon-o-check')
                    ->iconColor('success')
                    ->send();
              $this->buy_id='';
              $this->buyForm->reset();$this->buyTranForm->reset();
              $this->buyFormBlade->fill($this->buyForm->toArray());
              $this->buytranFormBlade->fill($this->buyTranForm->toArray());
            }
            else
                if(Buy::find($state)->morajeh>0) {

                    Notification::make()
                        ->title(fn () => new HtmlString('الفاتورة رقم <span class="text-primary-400">'.$state.'</span> تم <span class="text-primary-400">ترجيع</span> أصناف منها ولا يجوز تعديلها '))
                        ->icon('heroicon-o-check')
                        ->iconColor('success')
                        ->send();
                    $this->buy_id='';
                    $this->buyForm->reset();$this->buyTranForm->reset();
                    $this->buyFormBlade->fill($this->buyForm->toArray());
                    $this->buytranFormBlade->fill($this->buyTranForm->toArray());
                }
                else
            {
            $this->buy_id=$state;
            $this->id=$state;
            $this->buyForm->loadFromBuy($this->buy_id);
            $this->buyFormBlade->fill($this->buyForm->toArray());}

          })
          ->live(),
        ])->columns(4)->collapsible()->collapsed(function (){
                return $this->buy_id != null;
            }),
        Section::make()
          ->schema([
            DatePicker::make('order_date')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'supplier_id' })",
              ])
              ->id('order_date')
              ->autofocus()
              ->label('التاريخ')
              ->afterStateUpdated(function ($state){
                $res=Buy::find($this->buy_id);
                $res->order_date=$state;
                $res->save();
              })
              ->columnSpan(2)
              ->inlineLabel()
              ->required(),
            TextInput::make('Supplier_name')
              ->label('المورد')
              ->inlineLabel()
              ->columnSpan(3)
              ->disabled(),
            TextInput::make('Place_name')
                ->label('مكان التخزين')
                ->columnSpan(3)
                ->inlineLabel()
                ->disabled()
                ->visible(Setting::find(Auth::user()->company)->many_place),
            TextInput::make('Price_name')
              ->label('طريقة الدفع')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled(),
            TextInput::make('tot')
              ->label('إجمالي الفاتورة')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled(),
            TextInput::make('pay')
              ->label('المدفوع')
              ->columnSpan(2)
              ->live(onBlur: true)
              ->inlineLabel()
              ->default('0')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'barcode_id' })",
              ])
              ->afterStateUpdated(function (Set $set,Get $get,$state){
                if (!$state) $set('pay',0);
                $set('baky',$get('tot')-$get('pay'));
                $res=Buy::find($this->buy_id);
                $res->pay=$get('pay');
                $res->baky=$get('baky');
                $res->save();
              })
              ->id('pay'),
            TextInput::make('baky')
              ->label('المتبقي')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled()
              ->default('0'),


          ])
          ->columns(8)
          ->collapsible()
          ->reactive()
          ->hidden(function (){
              return $this->buy_id==null;
          })
      ])
      ->statePath('buyData')
      ->model(Buy::class);
  }

  public function buytranFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            TextInput::make('barcode_id')
              ->label('الباركود')
              ->required()
              ->exists()
              ->live(onBlur: true)
              ->afterStateUpdated(function (Set $set,$state) {
                $res=Barcode::find($state);

                if ($res) {
                  $rec=Item::find($res->item_id);
                  $set('item_id',$res->item_id) ;
                  $set('price_input', $rec->price_buy);
                }
              })
              ->extraAttributes([
                'wire:keydown.enter' => "ChkBarcode",
              ])
              ->id('barcode_id'),

            Select::make('item_id')
              ->label('الصنف')
              ->searchable()
              ->preload()
              ->relationship('Item','name')
              ->inlineLabel()
              ->live()
              ->reactive()
              ->required()
              ->afterStateUpdated(function (Set $set,$state){
                $res=Item::find($state);
                $set('barcode_id',$res->barcode) ;
                $set('price_input', $res->price_buy);
              })
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'q1' })",
                'wire:keydown..enter' => "\$dispatch('goto', { test: 'q1' })",
              ])
              ->id('item_id'),
              DatePicker::make('exp_date')
                  ->label('تاريخ الصلاحية')
                  ->inlineLabel()
                  ->extraAttributes([
                      'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
                  ])
                  ->visible(Setting::find(Auth::user()->company)->has_exp),

            TextInput::make('price_input')
              ->label('السعر')
              ->inlineLabel()
              ->numeric()
              ->live()
              ->required()
              ->id('price_input')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
              ]),

            TextInput::make('q1')
              ->label('الكمية')
              ->inlineLabel()
              ->numeric()
              ->required()
              ->extraAttributes([
                'wire:keydown.enter' => "add_rec",
              ])
              ->id('q1'),
          ])
            ->hidden(function (){
                return $this->buy_id==null;
            })

      ])
      ->statePath('buytranData')
      ->model(Buy_tran::class);
  }

  public function table(Table $table):Table
  {
    return $table
      ->query(function (Buy_tran $buy_tran)  {
        $buy_tran=Buy_tran::where('buy_id',$this->buy_id) ;
        return  $buy_tran;
      })
      ->columns([
        TextColumn::make('sort')
          ->label('ت')
          ->sortable(),
        TextColumn::make('item_id')
          ->label('رقم الصنف')
          ->sortable(),
        TextColumn::make('barcode_id')
          ->label('الباركود')
          ->sortable(),
        TextColumn::make('Item.name')
          ->label('اسم الصنف')
          ->color('info')
          ->sortable(),
        TextColumn::make('q1')
          ->label('الكمية')
          ->sortable(),
        TextColumn::make('price_input')
          ->label('سعر الشراء')
          ->sortable(),
        TextColumn::make('sub_input')
          ->label('المجموع')
          ->sortable(),
      ])
      ->actions([
        \Filament\Tables\Actions\Action::make('delete')
          ->action(function (Buy_tran $record){
            $record->delete();
            $res=Buy_tran::where('buy_id',$this->buy_id)->orderBy('sort')->get();
            $i=0;
            foreach ($res as $item) {$item->sort=++$i;$item->save();}

            $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
            $baky=$tot-Buy::find($this->buy_id)->pay;
            Buy::find($this->buy_id)->update([
              'tot'=>$tot,
              'baky'=>$baky,

            ]);

              $this->buyForm->loadFromBuy($this->buy_id);
              $this->buyFormBlade->fill($this->buyForm->toArray());
          })
          ->icon('heroicon-m-trash')
          ->iconButton()->color('danger')
          ->hiddenLabel()
            ->hidden(function (){
                return Buy_tran::where('buy_id',$this->buy_id)->count()==1;
            })
          ->requiresConfirmation(),
        \Filament\Tables\Actions\Action::make('edit')
          ->action(function (Buy_tran $record){
            $this->buytranFormBlade->fill($record->toArray());
            $this->dispatch('goto',  test: 'q1' );
          })
          ->icon('heroicon-m-pencil')
          ->iconButton()->color('info')
          ->hiddenLabel()

      ])
      ->bulkActions([

        BulkAction::make('deleteAll')
          ->action(function (Collection $records){
            $records->each->delete();
            $res=Buy_tran::where('buy_id',$this->buy_id)->orderBy('sort')->get();
            $i=0;
            foreach ($res as $item) {$item->sort=++$i;$item->save();}

            $tot=Buy_tran::where('buy_id',$this->buy_id)->sum('sub_input');
            $baky=$tot-Buy::find($this->buy_id)->pay;
            Buy::find($this->buy_id)->update([
                  'tot'=>$tot,
                  'baky'=>$baky,

              ]);

            $this->buyForm->loadFromBuy($this->buy_id);
            $this->buyFormBlade->fill($this->buyForm->toArray());
          })
          ->icon('heroicon-m-trash')
          ->color('danger')
          ->Label('الغاء المحدد')
            ->hidden(function (){
                return Buy_tran::where('buy_id',$this->buy_id)->count()==1;
            })
          ->requiresConfirmation(),

      ])

      ->striped()
        ;
  }

  public function mount(){
    $this->buyForm->mountForEdit();
    $this->buyTranForm->reset();
    $this->buyFormBlade->fill();

    $this->buytranFormBlade->fill();
  }

    public function render()
    {
        return view('livewire.buy.buy-edit');
    }
}
