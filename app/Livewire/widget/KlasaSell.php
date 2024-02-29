<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use App\Models\Sell;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaSell extends BaseWidget
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
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function(Sell $rec){
                if (!$this->repDate) return $rec=Sell::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
                $rec=Sell::where('order_date',$this->repDate)
                    ->join('places','place_id','places.id')

                    ->selectRaw('places.name, sum(tot) as tot,sum(pay) as pay,sum(baky) as baky')
                    ->groupBy('places.name');

                return $rec;
            }

            )
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">المبيعات</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
            ->paginated(false)
            ->defaultSort('tot')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نقطة البيع')
                    ->color('info'),
                Tables\Columns\TextColumn::make('tot')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('الإجمالي'),
                Tables\Columns\TextColumn::make('pay')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('المدفوع'),
                Tables\Columns\TextColumn::make('baky')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('الباقي'),

            ]);
    }
}
