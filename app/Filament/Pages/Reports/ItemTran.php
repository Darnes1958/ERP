<?php

namespace App\Filament\Pages\Reports;

use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Item_tran;
use App\Models\Recsupp;
use App\Models\Sell_tran;
use Filament\Actions\Action;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class ItemTran extends Page implements HasForms,HasTable
{
  use InteractsWithForms,InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.item-tran';
    protected static ?string $navigationLabel='حركة صنف';
  protected static ?string $navigationGroup='مخازن و أصناف';
  protected static ?int $navigationSort=4;
    protected ?string $heading='';
    public static function shouldRegisterNavigation(): bool
    {
      return Auth::user()->hasRole('admin');
    }

   public $item_id;
   public $repDate;

   public function mount(){
       $this->repDate = now()->copy()->startOfYear();
       $this->form->fill(['repdate'=>$this->repDate]);
   }
   public function SetDate($repdate){
       $this->repDate=$repdate;
   }
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
          ->columnSpan(2),

       DatePicker::make('repDate')
           ->live(onBlur: true)
           ->afterStateUpdated(function ($state){
                $this->SetDate($state);
           })

           ->label('من تاريخ'),

      ])->columns(6);
  }
  public function printAction(): Action
  {
    return Action::make('print')
      ->label('Excel')
      ->button()
      ->color('danger')
      ->icon('heroicon-m-printer')
      ->color('info')
      ->url(fn (): string => route('itemtranexl', ['item_id'=>$this->item_id,'repDate'=>$this->repDate,]));
  }

    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
  public function table(Table $table): Table
  {
    return $table
      ->query(function(Item_tran $rec){

       $rec=Item_tran::where('item_id',$this->item_id)
          ->where('order_date','>=',$this->repDate)
           ;

        return $rec;
      }

      )
      ->defaultSort('created_at')

      ->columns([
        TextColumn::make('created_at')
              ->label('تاريخ الإدخال'),
        TextColumn::make('type')
          ->color(function ($state){
              if ($state=='مشتريات') return 'info';
              if ($state=='مبيعات') return 'success';
          })
          ->label('البيان'),
        TextColumn::make('order_date')
          ->label('تاريخ الفاتورة'),
        TextColumn::make('id')
          ->label('رقم الفاتورة'),

        TextColumn::make('name')
          ->label('العميل'),
        TextColumn::make('price_type')
          ->label('طريقة الدفع'),
        TextColumn::make('notes')
          ->label('ملاحظات'),

        TextColumn::make('q1')
          ->label('الكمية'),
        TextColumn::make('price1')
          ->numeric(decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',')
          ->label('السعر'),
        TextColumn::make('sub_tot')
          ->numeric(decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',')
          ->label('المجموع'),

      ]);
  }

}
