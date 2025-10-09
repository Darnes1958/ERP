<?php

namespace App\Livewire\widget;

use Filament\Tables\Columns\TextColumn;
use App\Models\Buy;
use App\Models\Tar_buy;
use App\Models\Tar_sell;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepTarBuy extends BaseWidget
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
      'sub_tot',

    ],
  ];
    public function table(Table $table): Table
    {
      return $table
        ->query(function (Tar_buy $tar_sell){
          return Tar_buy::whereBetween('tar_date',[$this->repDate1,$this->repDate2])
              ->when($this->place_id,function ($q){
                  return $q->whereIn('buy_id',Buy::where('place_id', $this->place_id)->pluck('id'));
              });
        }

        )
        ->heading(new HtmlString('<div class="text-primary-400 text-lg">ترجيع المشتريات</div>'))
        ->defaultPaginationPageOption(5)

        ->defaultSort('tar_date','desc')
        ->striped()
        ->columns([
          TextColumn::make('id')
            ->label('رقم ألي'),
          TextColumn::make('Item.name')
            ->label('الصنف'),
          TextColumn::make('sub_tot')
            ->label('الإجمالي'),
          TextColumn::make('q1')
            ->label('الكمية'),
          TextColumn::make('buy_id')
            ->label('رقم فاتورة المشتريات'),
          TextColumn::make('notes')
            ->label('ملاحظات'),

        ])

        ->recordActions([

          //
        ])

        ->emptyStateHeading('لا توجد بيانات')
        ->contentFooter(view('table.footer', $this->data_list))
        ;

    }
}
