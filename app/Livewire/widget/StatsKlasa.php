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

  public $repDate;

  #[On('updateRep')]
  public function updaterep($repdate)
  {
    $this->repDate=$repdate;

  }
    protected function getStats(): array
    {
        return [
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مشتريات</span>'))
            ->value(new HtmlString('<span class="text-primary-500 ">'.
              number_format(Buy::where('order_date',$this->repDate)->sum('tot'),2, '.', ',').'</span>')),
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مبيعات</span>'))
            ->value(new HtmlString('<span class="text-danger-600 ">'.
              number_format(Sell::where('order_date',$this->repDate)->sum('tot'),2, '.', ',').'</span>')),

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">قبض</span>'))
            ->value(new HtmlString('<span class="text-primary-500">'.
              number_format(Receipt::where('receipt_date',$this->repDate)->where('imp_exp',0)->sum('val') +
                                 Recsupp::where('receipt_date',$this->repDate)->where('imp_exp',0)->sum('val')
                  ,2, '.', ',').'</span>')),

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">دفع</span>'))
            ->value(new HtmlString('<span class="text-danger-600">'.
              number_format(Receipt::where('receipt_date',$this->repDate)->where('imp_exp',1)->sum('val') +
                                 Recsupp::where('receipt_date',$this->repDate)->where('imp_exp',1)->sum('val')
                  ,2, '.', ',').'</span>')),

        ];
    }
}
