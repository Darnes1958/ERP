<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaBuy extends BaseWidget
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
            ->query(function(Buy $buy){
                if (!$this->repDate) return $buy=Buy::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
               $buy=Buy::where('order_date',$this->repDate)
                   ->join('places','place_id','places.id')

                   ->selectRaw('places.name, sum(tot) as tot,sum(pay) as pay,sum(baky) as baky')
                   ->groupBy('places.name');
               return $buy;
            }

            )
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">المشتريات</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
            ->paginated(false)
            ->defaultSort('tot')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                 ->color('info')
                 ->label('نقطة البيع'),
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
