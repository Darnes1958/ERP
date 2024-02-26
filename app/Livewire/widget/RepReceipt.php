<?php

namespace App\Livewire\widget;


use App\Models\Receipt;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepReceipt extends BaseWidget
{
    public $repDate;

    #[On('updateRep')]
    public function updaterep($repdate)
    {
        $this->repDate=$repdate;

    }
    public array $data_list= [
        'calc_columns' => [
            'val',
        ],
    ];

    public function table(Table $table): Table
    {

        return $table
            ->query(function (Receipt $buy){
                if (!$this->repDate) return $buy=Receipt::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
                $buy=Receipt::where('receipt_date',$this->repDate);
                return $buy;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">إيصالات الزبائن</div>'))
            ->defaultPaginationPageOption(5)


            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم الألي'),
                Tables\Columns\TextColumn::make('Customer.name')
                    ->label('المورد'),
                Tables\Columns\TextColumn::make('val')
                    ->label('المبلغ'),
                Tables\Columns\TextColumn::make('rec_who')
                    ->label('البيان')
                    ->badge(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات'),

            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ;
    }
}
