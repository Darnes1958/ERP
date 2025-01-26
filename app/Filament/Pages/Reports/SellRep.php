<?php

namespace App\Filament\Pages\Reports;

use Filament\Tables\Enums\FiltersLayout;
use App\Models\Buy;
use App\Models\Customer;
use App\Models\Sell;
use Carbon\Carbon;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


class SellRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.sell-rep';
    protected static ?string $navigationLabel = 'تقرير فواتير مبيعات';
    protected static ?string $navigationGroup = 'فواتير مبيعات';
    protected static ?int $navigationSort=3;
    protected ?string $heading = "";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مبيعات');
    }

    public array $data_list= [
        'calc_columns' => [
            'total',
            'tot',
            'pay',
            'baky',
          'sell_tran_sum_profit',
        ],
    ];
 public function table(Table $table): Table
 {
   return $table
    ->query(function (Sell $sell){
      $sell->all();
      return $sell;
    })
     ->defaultSort('id','desc')
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
             ->visible(Auth::user()->hasRole('admin'))
             ->sum('Sell_tran','profit')
             ->label('الربح'),
       TextColumn::make('notes')
         ->label('ملاحظات'),

     ])
       ->contentFooter(view('table.footer', $this->data_list))
     ->actions([
       Action::make('عرض ')
         ->modalHeading(false)
         ->modalSubmitAction(false)
         ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
         ->modalContent(fn (Sell $record): View => view(
           'filament.pages.reports.views.view-sell-tran-widget',
           ['sell_id' => $record->id],
         ))
         ->icon('heroicon-o-eye')
         ->iconButton(),
         Action::make('print')
             ->icon('heroicon-o-printer')
             ->iconButton()
             ->color('blue')
             ->url(fn (Sell $record): string => route('pdfsell', ['id' => $record->id]))
     ])
     ->filtersFormWidth(MaxWidth::Small)

     ->filters([
       SelectFilter::make('customer_id')
         ->options(Customer::all()->pluck('name', 'id'))
         ->searchable()
         ->label('زبون معين'),
       Filter::make('created_at')
         ->form([
           DatePicker::make('Date1')
             ->label('من تاريخ'),
           DatePicker::make('Date2')
             ->label('إلي تاريخ'),
         ])

         ->indicateUsing(function (array $data): ?string {
           if (! $data['Date1'] && ! $data['Date2']) { return null;   }
           if ( $data['Date1'] && !$data['Date2'])
             return 'ادخلت بتاريخ  ' . Carbon::parse($data['Date1'])->toFormattedDateString();
           if ( !$data['Date1'] && $data['Date2'])
             return 'حتي تاريخ  ' . Carbon::parse($data['Date2'])->toFormattedDateString();
           if ( $data['Date1'] && $data['Date2'])
             return 'ادخلت في الفترة من  ' . Carbon::parse($data['Date1'])->toFormattedDateString()
               .' إلي '. Carbon::parse($data['Date1'])->toFormattedDateString();

         })
         ->query(function (Builder $query, array $data): Builder {
           return $query
             ->when(
               $data['Date1'],
               fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
             )
             ->when(
               $data['Date2'],
               fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
             );
         })
     ], layout: FiltersLayout::Modal);
 }

}
