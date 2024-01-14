<?php

namespace App\Livewire\Sell;

use App\Enums\PlaceType;
use App\Livewire\Forms\SellForm;
use App\Livewire\Forms\SellTranForm;
use App\Livewire\Traits\Raseed;
use App\Models\Barcode;

use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use App\Models\Setting;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InpSell extends Component implements HasForms,HasTable,HasActions
{
  use InteractsWithForms, InteractsWithTable, InteractsWithActions;
  use Raseed;

  public ?array $sellData = [];
  public ?array $selltranData = [];

  public $sell_id;
  public $sell_id2;


  public $has_two;
  public $is_filled='false';
  public SellForm $sellForm;
  public SellTranForm $sellTranForm;


  protected function getForms(): array
  {
    return [
      'sellFormBlade',
      'selltranFormBlade',
    ];
  }
  public function is_two(){
      if (isset($this->selltranData['item_id']) && $this->selltranData['item_id']!='') {
      return Setting::find(Auth::user()->company)->has_two && Item::find($this->selltranData['item_id'])->two_unit==1;}
      else return false;
  }
  public function chkQuant(){
        if ($this->is_two())
            $this->dispatch('goto', test: 'q2');
        else $this->add_rec();
    }
  public function itemFill($item,$barcode,$stock1){
      $this->sellTranForm->item_id=$item;
      $this->sellTranForm->barcode_id=$barcode;
      $this->sellTranForm->place_id=$this->sellForm->place_id;

      $rec=$this->retPrice($item,$this->sellForm->single,$this->sellForm->price_type_id);

      $this->sellTranForm->price1=$rec['price1'];
      $this->sellTranForm->price2=$rec['price2'];

      $this->selltranFormBlade->fill([
        'item_id'=>$item,
        'price1'=>$rec['price1'],
        'price2'=>$rec['price2'],
        'barcode_id'=>$barcode,
        'raseed_all'=>$stock1,
        'raseed_place'=>Place_stock::where('item_id',$item)
          ->where('place_id',$this->sellForm->place_id)->first()->stock1,
        'place_id'=>$this->sellForm->place_id,
      ]);

    }
  public function ChkBarcode(){
        if ($this->selltranData['barcode_id']==null) return;
        $res=Barcode::with('Item')->find($this->selltranData['barcode_id']);

        if (! $res)
            Notification::make()
                ->title('هذا الباركود غير مخزون ')
                ->icon('heroicon-o-check')
                ->iconColor('success')
                ->send();
        else {
          $this->itemFill($res->item_id,$res->id,$res->item->stock1);
          $this->dispatch('goto', test: 'q1');
        }
    }
  public function ChkItem(){
    $res=Item::find($this->selltranData['item_id']);
    $this->itemFill($res->id,$res->barcode,$res->stock1);
    $this->dispatch('goto', test: 'q1');

  }
  public function add_rec()
    {
        $this->sellTranForm->loadForm($this->sell_id, $this->sell_id2, $this->selltranData);
        $this->sellTranForm->place_id=$this->sellForm->place_id;

        $chk=$this->sellTranForm->chkData();
        if ($chk != 'ok') {
          Notification::make()->title($chk)->icon('heroicon-o-check')->iconColor('danger')->send();
          return;
        }

        $this->sellTranForm->SetQuant();

        $res = Sell_tran_work::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)
            ->where('item_id', $this->sellTranForm->item_id)->get();

        if ($res->count() > 0)
            Sell_tran_work::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)
                ->where('item_id', $this->sellTranForm->item_id)
                ->update($this->sellTranForm->all());
        else  Sell_tran_work::create($this->sellTranForm->all());

        $this->sellTranForm->reset();
        $this->selltranFormBlade->fill($this->sellTranForm->toArray());
        $tot = Sell_tran_work::where('sell_id', $this->sell_id)->where('sell_id2', $this->sell_id2)->sum('sub_tot');
        $baky = $tot - Sell_work::find([$this->sell_id,$this->sell_id2])->pay;
        Sell_work::find([$this->sell_id,$this->sell_id2])->update([
            'tot' => $tot,
            'baky' => $baky,
        ]);
        $this->sellForm->tot=$tot;
        $this->sellForm->baky=$baky;
        $this->sellFormBlade->fill($this->sellForm->toArray());
        $this->is_filled=true;
        $this->dispatch('goto', test: 'barcode_id');
    }

  public function sellFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            DatePicker::make('order_date')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'customer_id' })",
              ])
              ->id('order_date')
              ->autofocus()
              ->label('التاريخ')
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->order_date=$state;
                $res->save();
              })
              ->columnSpan(2)
              ->inlineLabel()
              ->required(),
            Select::make('customer_id')
              ->label('الزبون')
              ->relationship('Customer','name')
              ->live()
              ->required()
              ->inlineLabel()
              ->columnSpan(3)
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->customer_id=$state;
                $res->save();
                $this->sellForm->customer_id=$state;
              })
              ->createOptionForm([
                Section::make('ادخال زبون جديد')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->label('الاسم'),
                    TextInput::make('address')
                      ->label('العنوان'),
                    TextInput::make('mdar')
                      ->label('مدار'),
                    TextInput::make('libyana')
                      ->label('لبيانا'),
                    Hidden::make('user_id'),
                  ])
              ])
              ->editOptionForm([
                Section::make('تعديل بيانات زبون')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->label('الاسم'),
                    TextInput::make('address')
                      ->label('العنوان'),
                    TextInput::make('mdar')
                      ->label('مدار'),
                    TextInput::make('libyana')
                      ->label('لبيانا'),
                    Hidden::make('user_id')
                      ->default(Auth::id()),

                  ])->columns(2)
              ])
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'place_id' })",
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'place_id' })",
              ])
              ->id('customer_id'),
            Select::make('place_id')
              ->label('مكان التخزين')
              ->relationship('Place','name')
              ->live()
              ->required()
              ->inlineLabel()
              ->columnSpan(2)
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->place_id=$state;
                $res->save();
                $this->sellForm->place_id=$state;
              })
              ->createOptionForm([
                Section::make('ادخال مكان تخزين')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->unique()
                      ->label('الاسم'),
                    Radio::make('place_type')
                      ->inline()
                      ->options(PlaceType::class)
                  ])
              ])
              ->editOptionForm([
                Section::make('تعديل مكان تخزين')
                  ->schema([
                    TextInput::make('name')
                      ->required()
                      ->unique()
                      ->label('الاسم'),
                    Radio::make('place_type')
                      ->inline()
                      ->options(PlaceType::class)
                  ])->columns(2)
              ])
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'price_type_id' })",
                'wire:keydown..enter' => "\$dispatch('goto', { test: 'price_type_id' })",
              ])
              ->id('place_id')
              ->visible(Setting::find(Auth::user()->company)->many_place)
              ->disabled(function (){return $this->is_filled;}),

            Select::make('price_type_id')
              ->label('طريقة الدفع')
              ->disabled(function (){return $this->is_filled;})
              ->columnSpan(2)
              ->inlineLabel()
              ->default(1)
              ->relationship('Price_type','name')
              ->required()
              ->afterStateUpdated(function ($state){
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->price_type_id=$state;
                $res->save();
                $this->sellForm->price_type_id=$state;
              })
              ->extraAttributes([
                'wire:change' => "\$dispatch('goto', { test: 'pay' })",
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'pay' })",
              ])
              ->id('price_type_id'),


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
                $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                $res->pay=$get('pay');
                $res->baky=$get('baky');
                $res->save();
                $this->sellForm->pay=$state;
              })
              ->id('pay'),
            TextInput::make('baky')
              ->label('المتبقي')
              ->columnSpan(2)
              ->inlineLabel()
              ->disabled()
              ->default('0'),
              Radio::make('single')
                  ->hiddenLabel()
                  ->inline()
                  ->inlineLabel(false)
                  ->disabled(fn(): bool=>$this->is_filled)
                  ->visible(Setting::find(Auth::user()->company)->jomla)
                ->afterStateUpdated(function ($state){
                  $res=Sell_work::find([$this->sell_id,$this->sell_id2]);
                  $res->single=$state;
                  $res->save();
                  $this->sellForm->single=$state;
                })
                  ->options([
                      1 => 'قطاعي',
                      0 => 'جملة'
                  ]),

          ])
          ->columns(8)
      ])
      ->statePath('sellData')
      ->model(Sell_work::class);
  }

  public function selltranFormBlade(Form $form): Form
  {
    return $form
      ->schema([
        Section::make()
          ->schema([
            TextInput::make('barcode_id')
              ->label('الباركود')
              ->columnSpan(2)
              ->required()
              ->inlineLabel()
              ->exists()
              ->live(onBlur: true)


              ->extraAttributes([
                'wire:keydown.enter' => "ChkBarcode",
              ])
              ->id('barcode_id'),

            Select::make('item_id')
              ->label('الصنف')
              ->columnSpan(2)
              ->searchable()
              ->preload()
              ->relationship('Item','name')
              ->inlineLabel()
              ->live()
              ->required()

              ->extraAttributes([
                'wire:change' => "ChkItem",
                'wire:keydown..enter' => "ChkItem",
              ])
              ->id('item_id'),
            TextInput::make('raseed_all')
             ->hiddenLabel()
             ->prefix('الرصيد الكلي')
             ->disabled(),
            TextInput::make('raseed_place')
              ->hiddenLabel()
              ->prefix('رصيد المكان')
              ->disabled(),

            TextInput::make('price1')
              ->label('السعر')
              ->inlineLabel()
              ->numeric()
              ->live()
              ->required()
              ->id('price1')
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
              ]),
            TextInput::make('price2')
              ->hiddenLabel()
              ->numeric()
              ->live()
              ->required()
              ->id('price2')
              ->visible(function (){
                  return $this->is_two();
              })
              ->extraAttributes([
                'wire:keydown.enter' => "\$dispatch('goto', { test: 'q1' })",
              ]),

            TextInput::make('q1')
              ->label('الكمية')
              ->inlineLabel()
              ->numeric()
              ->required()
              ->afterStateUpdated(function (Set $set,Get $get){
                  if ($get('q2')==null) $set('q2',0);
                  if ($get('q1')==null) $set('q1',0);

              })
              ->extraAttributes(['wire:keydown.enter' => "chkQuant",])
              ->id('q1'),
            TextInput::make('q2')
              ->hiddenLabel()
              ->numeric()
              ->required()
                ->afterStateUpdated(function (Set $set,Get $get){
                    if ($get('q2')==null) $set('q2',0);
                    if ($get('q1')==null) $set('q1',0);
                })

                ->visible(function (){
                    return $this->is_two();
                })
              ->extraAttributes([
                'wire:keydown.enter' => "add_rec",
              ])
              ->id('q2'),
          ])->columns(2),
        Section::make()
          ->schema([
            \Filament\Forms\Components\Actions::make([
              \Filament\Forms\Components\Actions\Action::make('تخزين')
                ->icon('heroicon-m-plus')
                ->button()
                ->visible(function () {return Sell_tran_work::where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->count()>0;})
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                  $sell=Sell_work::find([$this->sell_id,$this->sell_id2]);
                  $selltran=Sell_tran_work::with('Item')->where('sell_id',$this->sell_id)
                    ->where('sell_id2',$this->sell_id2)->get();
                  if ($selltran->count()==0)
                    Notification::make()
                      ->title('لم يتم ادخال اصناف بعد !! ')
                      ->icon('heroicon-o-exclamation-triangle')
                      ->iconColor('warning')
                      ->send();
                  $minus=false;
                  foreach ($selltran as $tran) {
                    $placeRaseed=$this->retRaseedPlace($tran->item_id,$this->sellForm->place_id);
                    if (
                      $this->TwoToOne($tran->item->count,$tran->q1,$tran->q2) >
                      $this->TwoToOne($tran->item->count,$placeRaseed['q1'],$placeRaseed['q2'])
                    ){
                      Notification::make()
                        ->title('الصنف : ('.$tran->item->name.') رصيده لا يسمح !!')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->iconColor('warning')
                        ->send();
                      $minus=true;
                      break;

                    }
                  }
                  if ($minus) return;

                    $this->sellForm->copyToSave($sell);
                    DB::connection(Auth()->user()->company)->beginTransaction();
                    try {

                      $this->sell_id=Sell::max('id')+1;
                      $this->sellForm->id=$this->sell_id;

                      $sell=Sell::create($this->sellForm->all());

                      foreach ($selltran as $item) {
                        $this->sellTranForm->copyToSave($this->sell_id,$this->sell_id2, $item);
                        $this->sellTranForm->place_id=$this->sellForm->place_id;

                        Sell_tran::create($this->sellTranForm->all());

                        $this->sellTranForm->DoDecALl();

                      }
                      $this->sell_id=Auth::id();
                      Sell_tran_work::where('sell_id',$this->sell_id)
                        ->where('sell_id2',$this->sell_id2)->delete();

                      $this->is_filled=false;

                      $sell->tot=0;  $sell->pay=0; $sell->baky=0;  $sell->save();
                      DB::connection(Auth()->user()->company)->commit();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('حدث خطأ !!')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->danger()
                            ->send();
                        info($e);
                        DB::connection(Auth()->user()->company)->rollback();
                    }
                }),
              \Filament\Forms\Components\Actions\Action::make('مسح')
                ->icon('heroicon-m-trash')
                ->button()
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                  Sell_tran_work::where('sell_id',$this->sell_id)->delete();
                  Sell_work::find([$this->sell_id,$this->sell_id2])->update([
                    'tot'=>0,'pay'=>0,'baky'=>0,
                  ]);
                  $this->is_filled=false;
                  $this->sellForm->fillForm($this->sell_id,$this->sell_id2);
                  $this->sellTranForm->reset();
                  $this->sellFormBlade->fill($this->sellForm->toArray());
                  $this->selltranFormBlade->fill($this->sellTranForm->toArray());
                })
            ])->extraAttributes(['class' => 'items-center justify-between']),

          ])

      ])
      ->statePath('selltranData')
      ->model(Sell_tran_work::class);
  }

  public function table(Table $table):Table
  {
    return $table
      ->query(function (Sell_tran_work $sell_tran)  {
        $sell_tran=Sell_tran_work::where('user_id',Auth::id())->where('sell_id',$this->sell_id)
          ->where('sell_id2',$this->sell_id2) ;
        return  $sell_tran;
      })
      ->columns([
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
          ->label('الكمية'),

        TextColumn::make('q2')
              ->label('صغري')
              ->visible(Setting::find(Auth::user()->company)->has_two)
              ->formatStateUsing(function (string $state) {
                  if ($state=='0') return '';
                  return $state;
              }),
        TextColumn::make('price1')
          ->label('سعر البيع'),

          TextColumn::make('price2')
              ->label('سعر الصغري')
              ->visible(Setting::find(Auth::user()->company)->has_two)
              ->formatStateUsing(function (string $state) {
                  if ($state=='0.0') return '';
                  return $state;
              }),
        TextColumn::make('sub_tot')
          ->label('المجموع'),

      ])
      ->actions([
        \Filament\Tables\Actions\Action::make('delete')
          ->action(function (Sell_tran_work $record){
            $record->delete();
            $this->sellForm->fillForm($this->sell_id,$this->sell_id2);
            $this->sellFormBlade->fill($this->sellForm->toArray());
            $this->is_filled=Sell_tran_work::where('sell_id',$this->sell_id)
              ->where('sell_id2',$this->sell_id2)->count()>0;
          })
          ->icon('heroicon-m-trash')
          ->iconButton()->color('danger')
          ->hiddenLabel()
          ->requiresConfirmation(),
        \Filament\Tables\Actions\Action::make('edit')
          ->action(function (Sell_tran_work $record){
            $this->selltranFormBlade->fill($record->toArray());
            $this->sellTranForm->loadForm($this->sell_id,$this->sell_id2,$record);
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
            $this->sellForm->fillForm($this->sell_id,$this->sell_id2);
            $this->sellFormBlade->fill($this->sellForm->toArray());
            $this->is_filled=Sell_tran_work::where('sell_id',$this->sell_id)
                ->where('sell_id2',$this->sell_id2)->count()>0;

          })
          ->icon('heroicon-m-trash')
          ->color('danger')
          ->Label('الغاء المحدد')
          ->requiresConfirmation(),

      ])

      ->striped();
  }

  public function mount(){
    $this->has_two=Setting::find(Auth::user()->company)->has_two;
    $res=Sell_work::where('user_id',Auth::id())
      ->whereDate('created_at', '=', date('Y-m-d'))->first();
    if ($res){
      $this->sellForm->fillForm($res->id,$res->id2);
      $this->sell_id=$res->id;
      $this->sell_id2=$res->id2;
      }
    else {
      Sell_tran_work::where('sell_id',Auth::id())->delete();
      Sell_work::where('id',Auth::id())->delete();
      $this->sellForm->mountForm();
      $res=Sell_work::create($this->sellForm->all());
      $this->sell_id=$res->id;
      $this->sell_id2=$res->id2;

    }
    $this->is_filled=Sell_tran_work::where('sell_id',$this->sell_id)
        ->where('sell_id2',$this->sell_id2)->count()>0;
    $this->sellFormBlade->fill($this->sellForm->toArray());
    $this->selltranFormBlade->fill($this->sellTranForm->toArray());
  }

  public function render()
    {
        return view('livewire.sell.inp-sell');
    }
}
