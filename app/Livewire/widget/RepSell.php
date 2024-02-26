<?php

namespace App\Livewire\widget;


use App\Models\Sell;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepSell extends BaseWidget
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
            ->query(function (Sell $sell){
                if (!$this->repDate) return $sell=Sell::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
                $sell=Sell::where('order_date',$this->repDate);
                return $sell;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">فواتير المبيعات</div>'))
            ->defaultPaginationPageOption(5)

            ->defaultSort('order_date','desc')
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الفاتورة'),
                Tables\Columns\TextColumn::make('Supplier.name')
                    ->label('الزبون'),
                Tables\Columns\TextColumn::make('tot')
                    ->label('الإجمالي'),
                Tables\Columns\TextColumn::make('pay')
                    ->label('المدفوع'),
                Tables\Columns\TextColumn::make('baky')
                    ->label('المتبقي'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات'),

            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ;
    }
}
