<?php

namespace App\Livewire\widget;


use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Models\Sell;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepSell extends BaseWidget
{

  public $repDate1;
  public $repDate2;
  public $place_id;
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
    #[On('updatePlace')]
  public function updateplace($place)
    {
        $this->place_id=$place;

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
                $sell=Sell::where('order_date','>=',$this->repDate1)
                    ->when($this->place_id,function ($q){
                        return $q->where('place_id',$this->place_id);
                    });
              if ($this->repDate2 && !$this->repDate1)
                $sell=Sell::where('order_date','<=',$this->repDate1)->when($this->place_id,function ($q){
                    return $q->where('place_id',$this->place_id);
                });
              if ($this->repDate1 && $this->repDate2)
                $sell=Sell::whereBetween('order_date',[$this->repDate1,$this->repDate2])->when($this->place_id,function ($q){
                    return $q->where('place_id',$this->place_id);
                });


                return $sell;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">فواتير المبيعات</div>'))
            ->defaultPaginationPageOption(5)

            ->defaultSort('order_date','desc')
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('رقم الفاتورة'),
                TextColumn::make('Customer.name')
                    ->label('الزبون'),
                TextColumn::make('total')
                    ->label('الإجمالي'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->label('المتبقي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),

            ])
            ->recordActions([
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
