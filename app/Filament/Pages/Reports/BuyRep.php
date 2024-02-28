<?php

namespace App\Filament\Pages\Reports;

use App\Models\Buy;

use App\Models\Customer;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class BuyRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.buy-rep';
  protected static ?string $navigationLabel = 'فواتير مشتريات';
  protected static ?string $navigationGroup = 'تقارير';
  protected ?string $heading = "";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مشتريات');
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
      ->query(function (Buy $buy){
        $buy->all();
        return $buy;
      })

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
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            )
          ->label('اجمالي الفاتورة'),
        TextColumn::make('pay')
          ->label('المدفوع')
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ),
        TextColumn::make('baky')
          ->label('الباقي')
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ),
        TextColumn::make('notes')
          ->label('ملاحظات'),
      ])
        ->contentFooter(view('table.footer', $this->data_list))
      ->actions([

        Action::make('عرض')
          ->modalHeading(false)
          ->action(fn (Buy $record) => $record->id())
          ->modalSubmitAction(false)
          ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
          ->modalContent(fn (Buy $record): View => view(
            'filament.pages.reports.views.view-buy-tran',
            ['record' => $record],
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
      ]);
  }

}
