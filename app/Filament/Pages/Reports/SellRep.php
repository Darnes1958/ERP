<?php

namespace App\Filament\Pages\Reports;

use App\Models\Buy;
use App\Models\Customer;
use App\Models\Sell;
use Filament\Actions\StaticAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;


class SellRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.sell-rep';
    protected static ?string $navigationLabel = 'فواتير مبيعات';
    protected static ?string $navigationGroup = 'تقارير';
    protected ?string $heading = "";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مبيعات');
    }

    public array $data_list= [
        'calc_columns' => [
            'tot',
            'pay',
            'baky',
        ],
    ];
 public function table(Table $table): Table
 {
   return $table
    ->query(function (Sell $sell){
      $sell->all();
      return $sell;
    })

     ->columns([
       TextColumn::make('id')
         ->searchable()
         ->sortable()
         ->label('الرقم الالي'),
       TextColumn::make('Customer.name')
         ->searchable()
         ->sortable()
         ->label('اسم الزبون'),
       TextColumn::make('order_date')
         ->searchable()
         ->sortable()
         ->label('التاريخ'),
       TextColumn::make('tot')
         ->searchable()
         ->sortable()
           ->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           )
         ->label('اجمالي الفاتورة'),
       TextColumn::make('cost')
         ->searchable()
         ->sortable()
         ->numeric(
           decimalPlaces: 2,
           decimalSeparator: '.',
           thousandsSeparator: ',',
         )
         ->label('تكاليف إضافية'),
       TextColumn::make('differ')
         ->searchable()
         ->numeric(
           decimalPlaces: 2,
           decimalSeparator: '.',
           thousandsSeparator: ',',
         )
         ->sortable()
         ->label('فرق عملة'),
       TextColumn::make('total')
         ->searchable()
         ->numeric(
           decimalPlaces: 2,
           decimalSeparator: '.',
           thousandsSeparator: ',',
         )
         ->sortable()
         ->label('الإجمالي النهائي'),

       TextColumn::make('pay')
           ->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           )
         ->label('المدفوع'),
       TextColumn::make('baky')
           ->numeric(
               decimalPlaces: 2,
               decimalSeparator: '.',
               thousandsSeparator: ',',
           )
         ->label('الباقي'),
         TextColumn::make('sell_tran_sum_profit')
             ->visible(Auth::user()->hasRole('Admin'))
             ->sum('Sell_tran','profit')
             ->label('الربح'),
       TextColumn::make('notes')
         ->label('ملاحظات'),

     ])
       ->contentFooter(view('table.footer', $this->data_list))
     ->actions([

         Action::make('عرض')
         ->modalHeading(false)
         ->action(fn (Sell $record) => $record->id())
         ->modalSubmitAction(false)
         ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
         ->modalContent(fn (Sell $record): View => view(
           'filament.pages.reports.views.view-sell-tran',
           ['record' => $record],
         ))
         ->icon('heroicon-o-eye')
         ->iconButton(),
         Action::make('print')
             ->icon('heroicon-o-printer')
             ->iconButton()
             ->color('blue')
             ->url(fn (Sell $record): string => route('pdfsell', ['id' => $record->id]))


     ])
     ->filters([
       SelectFilter::make('customer_id')
         ->options(Customer::all()->pluck('name', 'id'))
         ->searchable()
         ->label('زبون معين'),
     ]);
 }

}
