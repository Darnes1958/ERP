<?php

namespace App\Livewire\widget;


use App\Models\Sell;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepSell extends BaseWidget
{

  public $repDate1;
  public $repDate2;
  public function mount(){
    $this->repDate1=now();
    $this->repDate2=now();

  }

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
            'total',
            'pay',
            'baky',
        ],
    ];

    public function table(Table $table): Table
    {

        return $table
            ->query(function (Sell $sell){


              if ($this->repDate1 && !$this->repDate2)
                $sell=Sell::where('order_date','>=',$this->repDate1);
              if ($this->repDate2 && !$this->repDate1)
                $sell=Sell::where('order_date','<=',$this->repDate1);
              if ($this->repDate1 && $this->repDate2)
                $sell=Sell::whereBetween('order_date',[$this->repDate1,$this->repDate2]);


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
                    ->sortable()
                    ->label('رقم الفاتورة'),
                Tables\Columns\TextColumn::make('Customer.name')
                    ->label('الزبون'),
                Tables\Columns\TextColumn::make('total')
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
                    ->url(fn (Sell $record): string => route('pdfsell', ['id' => $record->id]))
            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ;
    }
}
