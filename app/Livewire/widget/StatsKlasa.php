<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use App\Models\Masrofat;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\Salarytran;
use App\Models\Sell;
use App\Models\Tar_buy;
use App\Models\Tar_sell;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class StatsKlasa extends BaseWidget
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
    #[On('updateklasaplace')]
  public function updatklasaplace($place)
    {
        $this->place_id=$place;
    }

    protected function getStats(): array
    {
        return [
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مشتريات</span>'))
            ->value(new HtmlString('<span class="text-primary-500 ">'.
              number_format(Buy::whereBetween('order_date',[$this->repDate1,$this->repDate2])
                  ->when($this->place_id,function ($q){
                      return $q->where('place_id',$this->place_id);
                  })
                  ->sum('tot'),2, '.', ',').'</span>')),
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مبيعات</span>'))
            ->value(new HtmlString('<span class="text-danger-600 ">'.
              number_format(Sell::whereBetween('order_date',[$this->repDate1,$this->repDate2])
                  ->when($this->place_id,function ($q){
                      return $q->where('place_id',$this->place_id);
                  })
                  ->sum('total'),2, '.', ',').'</span>')),

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">قبض</span>'))
            ->value(new HtmlString('<span class="text-primary-500">'.
              number_format(Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',0)
                      ->when($this->place_id,function ($q){
                          return $q->where('place_id',$this->place_id);
                      })->sum('val') +
                                 Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',0)
                                     ->when($this->place_id,function ($q){
                                         return $q->where('place_id',$this->place_id);
                                     })->sum('val')
                  ,2, '.', ',').'</span>')) ,

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">دفع</span>'))
            ->value(new HtmlString('<span class="text-danger-600">'.
              number_format(Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',1)
                      ->when($this->place_id,function ($q){
                          return $q->where('place_id',$this->place_id);
                      })->sum('val') +
                                 Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->where('imp_exp',1)
                                     ->when($this->place_id,function ($q){
                                         return $q->where('place_id',$this->place_id);
                                     })->sum('val')
                  ,2, '.', ',').'</span>'))
            ,
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">ترجيع مشتريات</span>'))
            ->value(new HtmlString('<span class="text-danger-600">'.
              number_format(Tar_buy::whereBetween('tar_date',[$this->repDate1,$this->repDate2])
                 ->sum('sub_tot')
                ,2, '.', ',').'</span>'))
            ,
          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">ترجيع مبيعات</span>'))
            ->value(new HtmlString('<span class="text-danger-600">'.
              number_format(Tar_sell::whereBetween('tar_date',[$this->repDate1,$this->repDate2])
                  ->sum('sub_tot')
                ,2, '.', ',').'</span>'))
            ,

          Stat::make('','')
            ->label(new HtmlString('<span class="text-indigo-700">مصروفات</span>'))
            ->value(new HtmlString('<span class="text-danger-600">'.
              number_format(Masrofat::whereBetween('masr_date',[$this->repDate1,$this->repDate2])
                  ->when($this->place_id,function ($q){
                      return $q->where('place_id',$this->place_id);
                  })->sum('val')
                ,2, '.', ',').'</span>'))
            ,

        ];
    }
}
