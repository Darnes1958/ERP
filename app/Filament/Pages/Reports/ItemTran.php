<?php

namespace App\Filament\Pages\Reports;

use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Recsupp;
use App\Models\Sell_tran;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ItemTran extends Page implements HasForms,HasTable
{
  use InteractsWithForms,InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.item-tran';
    protected static ?string $navigationLabel='حركة صنف';
    protected static ?string $navigationGroup='تقارير';
    protected ?string $heading='';
    public static function shouldRegisterNavigation(): bool
    {
      return Auth::user()->hasRole('Admin');
    }

   public $item_id;
   public $repDate;
    public function form(Form $form): Form
  {
    return $form
      ->schema([
        Select::make('item_id')
          ->options(Item::all()->pluck('name','id'))
          ->live()
          ->searchable()
          ->preload()
          ->afterStateUpdated(function ($state){
            $this->item_id=$state;
          })
          ->label('الصنف')
          ->inlineLabel()
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(function(Recsupp $rec){

        $first=Buy_tran::where('item_id',$this->item_id)
          ->where('order_date','>=',$this->repDate)
          ->join('buys','buy_id','buys.id')
          ->join('price_types','buys.price_type_id','price_types.id')
          ->join('suppliers','buys.supplier_id','suppliers.id')
          ->selectRaw('\'مشتريات\' as buy,created_at,order_date,suppliers.name ,q1,price_input as price1,sub_input as sub_tot');
        $secound=Sell_tran::where('item_id',$this->item_id)
          ->where('order_date','>=',$this->repDate)
          ->join('sells','sell_id','sells.id')
          ->join('price_types','sells.price_type_id','price_types.id')
          ->join('customers','sells.customer_id','customers.id')
          ->selectRaw('\'مبيعات\'  as sell,created_at,order_date,customers.name ,q1,price1,sub_tot')
          ->union($first);

        return $secound;
      }

      )
      ->defaultSort('created_at')
      ->columns([
        TextColumn::make('order_date')
          ->label('التاريخ'),
        TextColumn::make('name')
          ->label('طريقة الدفع'),
        TextColumn::make('val')
          ->numeric(decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',')
          ->state(function (Recsupp $record): string {
            if ($record->val==0)
              return ''; else return $record->val;
          })
          ->label('قبض'),
        Tables\Columns\TextColumn::make('exp')
          ->numeric(decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',')
          ->state(function (Recsupp $record): string {
            if ($record->exp==0)
              return ''; else return $record->exp;
          })
          ->label('دفع'),

      ]);
  }

}
