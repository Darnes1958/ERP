<?php

namespace App\Filament\Pages\Reports;

use App\Models\Acc;
use App\Models\Acc_tran;
use App\Models\Kazena;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class KazTran extends Page implements HasForms,HasTable
{
  use InteractsWithTable,InteractsWithForms;
  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel='حركة خزينة';
  protected static ?string $navigationGroup='مصارف وخزائن';
  protected ?string $heading="";

    protected static string $view = 'filament.pages.reports.kaz-tran';

  public $repDate1;
  public $repDate2;
  public $kazena_id;
  public function mount(){
    $this->repDate1=now();
    $this->repDate2=now();

    $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,]);
  }
  public array $data_list= [
    'calc_columns' => [
      'mden',
      'daen',
    ],
  ];

  public function getTableRecordKey(Model $record): string
  {
    return uniqid();
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Select::make('kazena_id')
          ->options(Kazena::all()->pluck('name','id'))
          ->searchable()
          ->preload()
          ->live()
          ->afterStateUpdated(function ($state){
            $this->kazena_id=$state;
          })
          ->label('الحساب'),
        DatePicker::make('repDate1')
          ->live()
          ->afterStateUpdated(function ($state){
            $this->repDate1=$state;
          })
          ->label('من تاريخ'),
        DatePicker::make('repDate2')
          ->live()
          ->afterStateUpdated(function ($state){
            $this->repDate2=$state;
          })
          ->label('إلي تاريخ'),
          \Filament\Forms\Components\Actions::make([

              \Filament\Forms\Components\Actions\Action::make('Exl')
                  ->label('Excel')
                  ->visible(function (){
                      return ($this->chkDate($this->repDate1) || $this->chkDate($this->repDate2)) && $this->kazena_id;
                  })
                  ->button()
                  ->color('success')
                  ->url(fn (): string => route('kazenatranexl', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'kazena_id'=>$this->kazena_id,]))
          ])->verticalAlignment(VerticalAlignment::End),

      ])->columns(6);
  }
  public function table(Table $table): Table
  {
    return $table
      ->query(function (){
        $report=Acc_tran::
        where('kazena_id',$this->kazena_id)
          ->where('kazena_id','!=',null)
          ->when($this->repDate1,function ($q){
            $q->where('receipt_date','>=',$this->repDate1);
          })
          ->when($this->repDate2,function ($q){
            $q->where('receipt_date','<=',$this->repDate2);
          })

        ;
        return $report;
      })
      ->emptyStateHeading('لا توجد بيانات')
      ->contentFooter(view('table.footer', $this->data_list))
      ->columns([
        TextColumn::make('rec_who')
          ->sortable()
          ->description(function (Acc_tran $record) {
            if ($record->rec_who->value ==10) return 'الي '.Acc::find($record->acc2_id)->name;
            if ($record->rec_who->value ==11 ) return 'من '.Acc::find($record->acc2_id)->name;
            if ($record->rec_who->value ==9){
              if ($record->mden==0) return 'الي '.Kazena::find($record->kazena2_id)->name;
              else return 'من '.Kazena::find($record->kazena2_id)->name;
            }
          })

          ->searchable()
          ->label('البيان'),
        TextColumn::make('receipt_date')
          ->sortable()
          ->searchable()
          ->label('التاريخ'),

        TextColumn::make('mden')
          ->color('danger')
          ->searchable()
          ->numeric(
            decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',',
          )
          ->label('مدين'),
        TextColumn::make('daen')
          ->color('info')
          ->searchable()
          ->numeric(
            decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',',
          )
          ->label('دائن'),
        TextColumn::make('order_id')
          ->searchable()
          ->label('رقم الفاتورة'),
        TextColumn::make('notes')
          ->searchable()
          ->label('ملاحظات'),

      ])
      ->defaultSort('receipt_date')
      ->striped();
  }
    public function chkDate($repDate){
        try {
            Carbon::parse($repDate);
            return true;
        } catch (InvalidFormatException $e) {
            return false;
        }
    }
}
