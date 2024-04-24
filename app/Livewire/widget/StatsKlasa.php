<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\Sell;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class StatsKlasa extends BaseWidget
{
  protected int | string | array $columnSpan = 'full';

  public $repDate1;
  public $repDate2;

  #[On('updateDate1')]
  public function updatedate1($repdate)
  {
    $this->repDate1=$repdate;
    info($this->repDate1);

  }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;

  }
    protected function getStats(): array
    {
        return [
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مشتريات</span>'))
            ->value(new HtmlString('<span class="text-primary-500 ">'.
              number_format(Buy::whereBetween('order_date',[$this->repDate1,$this->repDate2])->sum('tot'),2, '.', ',').'</span>')),
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مبيعات</span>'))
            ->value(new HtmlString('<span class="text-danger-600 ">'.
              number_format(Sell::whereBetween('order_date',[$this->repDate1,$this->repDate2])->sum('total'),2, '.', ',').'</span>')),

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">قبض</span>'))
            ->value(new HtmlString('<span class="text-primary-500">'.
              number_format(Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',0)->sum('val') +
                                 Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',0)->sum('val')
                  ,2, '.', ',').'</span>')),

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">دفع</span>'))
            ->value(new HtmlString('<span class="text-danger-600">'.
              number_format(Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',1)->sum('val') +
                                 Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',1)->sum('val')
                  ,2, '.', ',').'</span>')),

        ];
    }
}
