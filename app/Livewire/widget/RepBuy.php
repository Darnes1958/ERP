<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use Filament\Actions\StaticAction;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepBuy extends BaseWidget
{

    public $repDate;

    #[On('updateRep')]
    public function updaterep($repdate)
    {
        $this->repDate=$repdate;

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
                    if (!$this->repDate) return $buy=Buy::where('id',null);
                    $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                    $errors = \DateTime::getLastErrors();
                    if (!empty($errors['warning_count'])) {
                        return false ;
                    }
                    $buy=Buy::where('order_date',$this->repDate);
                    return $buy;
                }
                // ...
                )
                ->heading(new HtmlString('<div class="text-primary-400 text-lg">فواتير المشتريات</div>'))
                ->defaultPaginationPageOption(5)

                ->defaultSort('order_date','desc')
                ->striped()
                ->columns([
                    Tables\Columns\TextColumn::make('id')
                        ->label('رقم الفاتورة'),
                    Tables\Columns\TextColumn::make('Supplier.name')
                        ->label('المورد'),
                    Tables\Columns\TextColumn::make('tot')
                        ->label('الإجمالي'),
                    Tables\Columns\TextColumn::make('pay')
                        ->label('المدفوع'),
                    Tables\Columns\TextColumn::make('baky')
                        ->label('المتبقي'),
                    Tables\Columns\TextColumn::make('notes')
                        ->label('ملاحظات'),

                ])

                ->actions([
                    Action::make('print')
                        ->icon('heroicon-o-printer')
                        ->iconButton()
                        ->color('blue')
                        ->url(fn (Buy $record): string => route('pdfbuy', ['id' => $record->id]))
                    ])

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
                  ->iconButton()
              ])

                ->emptyStateHeading('لا توجد بيانات')
                ->contentFooter(view('table.footer', $this->data_list))
                ;
    }
}
