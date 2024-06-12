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
  public $repDate1;
  public $repDate2;


    #[On('updateDate1')]
    public function updatedate1($repdate)
    {
        $this->repDate1=$repdate;

    }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;

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
                if (!$this->repDate1 && !$this->repDate2)
                  return $buy=Buy::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate1[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
              $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate2[4]);
              $errors = \DateTime::getLastErrors();
              if (!empty($errors['warning_count'])) {
                return false ;
              }

               $buy=Buy::when($this->repDate1,function ($q){
                 $q->where('order_date','>=',$this->repDate1);
               })
                 ->when($this->repDate2,function ($q){
                   $q->where('order_date','<=',$this->repDate2);
                 })
                   ->join('places','place_id','places.id')
                   ->selectRaw('places.name, sum(tot) as tot,sum(pay) as pay,sum(baky) as baky')
                   ->groupBy('places.name');
               return $buy;
            }

            )
          ->emptyStateHeading('لا توجد بيانات')
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
