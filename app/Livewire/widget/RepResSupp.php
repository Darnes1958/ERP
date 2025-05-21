<?php

namespace App\Livewire\widget;


use App\Models\Recsupp;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepResSupp extends BaseWidget
{

  public $repDate1;
  public $repDate2;
    public $place_id;
  public $raseed;
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
            ->query(function (Recsupp $buy){
              if ($this->repDate1 && !$this->repDate2)
                $buy=Recsupp::where('receipt_date','>=',$this->repDate1)->when($this->place_id,function ($q){
                    return $q->where('place_id',$this->place_id);
                });
              if ($this->repDate2 && !$this->repDate1)
                $buy=Recsupp::where('receipt_date','<=',$this->repDate1)->when($this->place_id,function ($q){
                    return $q->where('place_id',$this->place_id);
                });
              if ($this->repDate1 && $this->repDate2)
                $buy=Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->when($this->place_id,function ($q){
                    return $q->where('place_id',$this->place_id);
                });
              $this->raseed=Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->when($this->place_id,function ($q){
                  return $q->where('place_id',$this->place_id);
              })
                  ->where('imp_exp',0)->sum('val') -
                Recsupp::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])
                  ->where('imp_exp',1)->sum('val') ;
                return $buy;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">إيصالات الموردين</div>'))
            ->defaultPaginationPageOption(5)


            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم الألي'),
                Tables\Columns\TextColumn::make('Supplier.name')
                    ->label('المورد'),
                Tables\Columns\TextColumn::make('rec_who')
                    ->label('البيان')
                    ->badge(),
                Tables\Columns\TextColumn::make('val')
                    ->label('المبلغ'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات'),

            ])
            ->emptyStateHeading('لا توجد بيانات')
          ->contentFooter(function (){return view('table.Recfooter', $this->data_list,['raseed'=>$this->raseed,]);} )
            ;
    }
}
