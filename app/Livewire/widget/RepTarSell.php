<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use App\Models\Sell;
use App\Models\Tar_sell;
use Filament\Actions\StaticAction;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepTarSell extends BaseWidget
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
          ->query(function (Tar_sell $tar_sell){
            return Tar_sell::whereBetween('tar_date',[$this->repDate1,$this->repDate2])
                ->join('sells','sells.id','=','tar_sells.sell_id')
                ->when($this->place_id,function ($q){
                    return $q->where('place_id', $this->place_id);
                });
          }

          )
          ->heading(new HtmlString('<div class="text-primary-400 text-lg">ترجيع المبيعات</div>'))
          ->defaultPaginationPageOption(5)

          ->defaultSort('tar_date','desc')
          ->striped()
          ->columns([
            Tables\Columns\TextColumn::make('id')
              ->label('رقم ألي'),
            Tables\Columns\TextColumn::make('Item.name')
              ->label('الصنف'),
            Tables\Columns\TextColumn::make('sub_tot')
              ->label('الإجمالي'),
            Tables\Columns\TextColumn::make('q1')
              ->label('الكمية'),
            Tables\Columns\TextColumn::make('sell_id')
              ->label('رقم فاتورة المبيعات'),
            Tables\Columns\TextColumn::make('notes')
              ->label('ملاحظات'),

          ])

          ->actions([

            //
          ])

          ->emptyStateHeading('لا توجد بيانات')
          ->contentFooter(view('table.footer', $this->data_list))
          ;
    }
}
