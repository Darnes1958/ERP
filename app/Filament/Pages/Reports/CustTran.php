<?php

namespace App\Filament\Pages\Reports;


use App\Filament\Resources\SellResource\Pages\EditSell;
use App\Filament\Resources\SellResource\Pages\SellEdit;
use App\Models\Cust_tran2;
use App\Models\Customer;
use App\Models\Place_stock;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustTran extends Page implements HasForms,HasTable
{
  use InteractsWithTable,InteractsWithForms;
  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel='حركة زبون';
  protected static ?string $navigationGroup='زبائن وموردين';
  protected static ?int $navigationSort=5;
  protected ?string $heading="";
  protected static string $view = 'filament.pages.reports.cust-tran';

  public $cust_id;
  public $repDate;
  public $formData;
  public $order_no;

  public Sell $sell;

  public function mount(){
    $this->repDate=now();

    $this->myForm->fill(['repDate'=>$this->repDate,'raseed'=>0,'mden'=>0,'daen'=>0]);
  }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "myForm" => $this->makeForm()
                ->schema($this->getMyFormSchema())
                ->statePath('formData'),

        ]);
    }

  public function getTableRecordKey(Model $record): string
  {
    return $record->idd;
  }


  public function table(Table $table): Table
  {
    return $table
      ->query(function (){
        $report=Cust_tran2::
          where('customer_id',$this->cust_id)
          ->where('repDate','>=',$this->repDate);
        return $report;
      })

      ->actions([
        \Filament\Tables\Actions\Action::make('عرض')
          ->visible(function (Model $record) {return $record->rec_who->value==7;})
          ->modalHeading(false)
          ->action(fn (Cust_tran2 $record) =>  $record->idd)
            ->modalSubmitActionLabel('طباعة')
            ->modalSubmitAction(
                fn (\Filament\Actions\StaticAction $action,Cust_tran2 $record) =>
                $action->color('blue')
                    ->icon('heroicon-o-printer')
                    ->url(function () use($record){
                        $this->order_no=$record->id;
                        return route('pdfsell', ['id' => $record->id]);
                    })
            )
          ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
          ->modalContent(fn (Cust_tran2 $record): View => view(
            'filament.pages.reports.views.view-sell-tran',
            ['record' => Sell::with('Sell_tran')->where('id',$record->id)->first()],
          ))
          ->icon('heroicon-o-eye')
          ->iconButton(),
        ])
      ->columns([
        TextColumn::make('repDate')
          ->sortable()
          ->searchable()
          ->label('التاريخ'),
        TextColumn::make('id')
          ->sortable()
          ->searchable()

          ->label('الرقم الألي'),

        TextColumn::make('rec_who')
          ->sortable()
          ->searchable()
          ->label('البيان'),
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
        TextColumn::make('notes')
         ->label('ملاحظات')
      ])
        ->emptyStateHeading('لا توجد بيانات')
      ->defaultSort('idd')
      ->striped();
  }

    protected function getMyFormSchema(): array
  {
    return [
       Section::make()
      ->schema([
        Select::make('cust_id')
         ->options(Customer::all()->pluck('name','id'))
         ->searchable()
         ->preload()
         ->live()
          ->afterStateUpdated(function ($state,Set $set){
            $this->cust_id=$state;
            if ($this->repDate) {
                $mden=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                $daen=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                $set('mden',number_format($mden, 2, '.', ','));
                $set('daen',number_format($daen, 2, '.', ','));
                $set('raseed',number_format($mden-$daen, 2, '.', ','));


            }
          })
         ->label('الزبون'),
        DatePicker::make('repDate')
          ->live()
          ->afterStateUpdated(function ($state,Set $set){
            $this->repDate=$state;
              if ($this->repDate && $this->cust_id) {
                  $mden=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                  $daen=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                  $set('mden',number_format($mden, 2, '.', ','));
                  $set('daen',number_format($daen, 2, '.', ','));
                  $set('raseed',number_format($mden-$daen, 2, '.', ','));


              }
          })
          ->label('من تاريخ'),

        TextInput::make('mden')
         ->readOnly()
         ->label('مدين'),
        TextInput::make('daen')
              ->readOnly()
              ->label('دائن'),
       TextInput::make('raseed')
              ->readOnly()
              ->label('الرصيد'),
        \Filament\Forms\Components\Actions::make([
          \Filament\Forms\Components\Actions\Action::make('printorder')
          ->label('طباعة')
            ->visible(function (){
              return $this->chkDate($this->repDate) && $this->cust_id;
            })

            ->button()

          ->icon('heroicon-m-printer')
          ->color('info')
          ->url(fn (): string => route('pdfcusttran', ['tran_date'=>$this->repDate,'cust_id'=>$this->cust_id,])),
          \Filament\Forms\Components\Actions\Action::make('Exl')
            ->label('Excel')
            ->visible(function (){
              return $this->chkDate($this->repDate) && $this->cust_id;
            })
            ->button()
            ->color('success')
            ->url(fn (): string => route('custtranexl', ['repDate'=>$this->repDate,'cust_id'=>$this->cust_id,]))
        ])->verticalAlignment(VerticalAlignment::End),
      ])
      ->columns(6)
      ];
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
