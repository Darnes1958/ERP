<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use App\Models\Masr_view;
use App\Models\Masrofat;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaMasr extends BaseWidget
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
      'val',
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

        $masr=Masr_view::when($this->repDate1,function ($q){
          $q->where('masr_date','>=',$this->repDate1);
        })
          ->when($this->repDate2,function ($q){
            $q->where('masr_date','<=',$this->repDate2);
          })
          ->selectRaw('name, acc_name,sum(val) as val')
          ->groupBy('name','acc_name');
        return $masr;
      }

      )
      ->emptyStateHeading('لا توجد بيانات')
      ->heading(new HtmlString('<div class="text-primary-400 text-lg">المصروفات</div>'))
      ->contentFooter(view('table.footer', $this->data_list))
      ->paginated(false)
      ->defaultSort('val')
      ->columns([
        TextColumn::make('name')
          ->color('info')
          ->label('البيان'),
        TextColumn::make('acc_name')
          ->color('primary')
          ->label('دفعت من'),

        TextColumn::make('val')
          ->numeric(decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',')
          ->label('الإجمالي'),

      ]);
  }

}
