<?php

namespace App\Livewire\widget;

use Filament\Tables\Columns\TextColumn;
use App\Livewire\Traits\AksatTrait;
use App\Models\Rebh_second;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RebhMonth extends BaseWidget
{
    use AksatTrait;
  public $year;



  #[On('updateyear')]
  public function updateyear($year)
  {
    $this->year=$year;

  }

    public array $data_list= [
        'calc_columns' => [
            'rebh',
            'rent',
            'masr',
            'sal',
            'safi',
        ],
    ];
  public function getTableRecordKey(Model|array $record): string
  {
    return uniqid();
  }

  public function table(Table $table): Table
    {
        return $table
            ->query(function (){

                $res=Rebh_second::selectRaw('month(date) date
                ,round(sum(rebh),0) rebh
                ,round(sum(masr),0) masr
                ,round(sum(rent),0) rent
                ,round(sum(sal),0) sal
                ,round(sum(rebh),0)-
                 round(sum(masr),0)-
                 round(sum(rent),0)-
                 round(sum(sal),0) safi')
                    ->WhereYear('date',$this->year)
                    ->groupByRaw('month(date)');

              return $res;
            }

            )
          ->heading(new HtmlString('<div class="text-primary-400 text-lg">'.'الارباح بالأشهر لسنه '.$this->year.'</div>'))
          ->contentFooter(view('table.footer', $this->data_list))
          ->defaultSort('date')
            ->columns([
                TextColumn::make('date')

                 ->label('الشهر'),
                TextColumn::make('rebh')
                 ->label('هامش الربح'),
              TextColumn::make('masr')
                ->label('مصروفات'),
              TextColumn::make('sal')
                ->label('مرتبات'),
              TextColumn::make('rent')
                ->label('ايجارات'),
              TextColumn::make('safi')
                    ->label('صافي الأرباح'),


            ]);
    }
}
