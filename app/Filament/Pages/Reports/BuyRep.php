<?php

namespace App\Filament\Pages\Reports;

use App\Models\Buy;

use App\Models\Customer;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class BuyRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.buy-rep';
  protected static ?string $navigationLabel = 'تقرير فواتير مشتريات';
  protected static ?string $navigationGroup = 'فواتير شراء';
  protected static ?int $navigationSort=4;
  protected ?string $heading = "";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مشتريات') || Auth::user()->can('تقارير مشتريات');
    }



  public function table(Table $table): Table
  {
    return $table
      ->query(function (Buy $buy){
        $buy->all();
        return $buy;
      })
      ->defaultSort('id','desc')
        ->pluralModelLabel('المشتريات')
      ->columns([
        TextColumn::make('id')
          ->searchable()
          ->sortable()
          ->label('الرقم الالي'),
        TextColumn::make('Supplier.name')
          ->searchable()
          ->sortable()
          ->label('اسم المورد'),
        TextColumn::make('order_date')
          ->searchable()
          ->sortable()
          ->label('التاريخ'),
        TextColumn::make('tot')
          ->searchable()
          ->sortable()
            ->summarize(Sum::make()->label('')->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ))
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            )
          ->label('اجمالي الفاتورة'),
        TextColumn::make('pay')
            ->summarize(Sum::make()->label('')->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ))
          ->label('المدفوع')
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ),
        TextColumn::make('baky')
            ->summarize(Sum::make()->label('')->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ))
          ->label('الباقي')
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ),
        TextColumn::make('notes')
          ->label('ملاحظات'),
      ])

      ->actions([

        Action::make('عرض ')
          ->modalHeading(false)
          ->modalSubmitAction(false)
          ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
          ->modalContent(fn (Buy $record): View => view(
            'filament.pages.reports.views.view-buy-tran-widget',
            ['buy_id' => $record->id],
          ))
          ->icon('heroicon-o-eye')
          ->iconButton(),
      Action::make('print')
      ->icon('heroicon-o-printer')
      ->iconButton()
      ->color('blue')
      ->url(fn (Buy $record): string => route('pdfbuy', ['id' => $record->id]))
      ])
      ->filters([
        SelectFilter::make('supplier_id')
          ->options(Supplier::all()->pluck('name', 'id'))
          ->searchable()
          ->label('مورد معين'),
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
      ]);
  }

}
