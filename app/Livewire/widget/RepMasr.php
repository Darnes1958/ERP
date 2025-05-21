<?php

namespace App\Livewire\widget;

use App\Models\Masrofat;
use App\Models\Tar_sell;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepMasr extends BaseWidget
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
      'val',

    ],
  ];
    public function table(Table $table): Table
    {
        return $table
          ->query(function (Masrofat $tar_sell){
            return Masrofat::whereBetween('masr_date',[$this->repDate1,$this->repDate2])->when($this->place_id,function ($q){
                return $q->where('place_id',$this->place_id);
            });
          }

          )
          ->heading(new HtmlString('<div class="text-primary-400 text-lg">المصروفات </div>'))
          ->defaultPaginationPageOption(5)

          ->defaultSort('masr_date','desc')
          ->striped()
            ->columns([
              TextColumn::make('masr_date')
                ->label('التاريخ'),
              TextColumn::make('Masr_type.name')
                ->color('info')
                ->label('البيان'),
              TextColumn::make('acc_name')
                ->state(function (Masrofat $record){
                  if ($record->acc_id) return $record->Acc->name;
                  else return $record->Kazena->name;
                })
                ->color('primary')
                ->label('دفعت من'),

              TextColumn::make('val')
                ->numeric(decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',')
                ->label('المبلغ'),
            ])
          ->emptyStateHeading('لا توجد بيانات')
          ->contentFooter(view('table.footer', $this->data_list));
    }
}
