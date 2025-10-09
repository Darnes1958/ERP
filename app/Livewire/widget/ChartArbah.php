<?php

namespace App\Livewire\widget;

use App\Models\Country;
use App\Models\Family;
use App\Models\Rebh_first_place;
use App\Models\Victim;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class ChartArbah extends ChartWidget
{
    public $year;
    public $place;
    #[On('updateyearplace')]
    public function updateyearplace($year,$place)
    {
        $this->year=$year;
        $this->place=$place;

    }
    protected ?string $heading = ' ';

  protected static ?int $sort=14;
    protected function getData(): array
    {
      $data=$this->getInfo();
      return [
        'datasets' => [
          [
            'label' => 'صافي الأرباح حسب الشهر',
            'data' => $data['theData'],
            'backgroundColor' => [
              "#483D8B",
              "#FFB6C1",
              "#7FFF00",
              "#0000FF",
              "#DEB887",
              "#006400",
              "#8B0000",
              "#FF8C00",
              '#483D8B',
              '#8B008B',
              '#2F4F4F',
              '#00CED1',
              '#FFD700',

            ],
          ],
        ],
        'labels' => $data['theLabels'],
      ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
  private function getInfo(): array {
      $res=Rebh_first_place::selectRaw('
                wyear,
                wmonth ,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rebh\'),0) rebh,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'masr\'),0) masr,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rent\'),0) rent,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'sal\'),0) sal,

                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rebh\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'masr\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rent\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'sal\'),0) safi
                ')
          ->Where('wyear',$this->year)
          ->groupBy('wyear','wmonth')->get();

    $theLabels=$res->pluck('wmonth');
    $theData=$res->pluck('safi');

    return [
      'theLabels'=> $theLabels,
      'theData' => $theData,
    ];
  }
}
