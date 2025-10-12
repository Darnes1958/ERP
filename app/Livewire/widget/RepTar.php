<?php

namespace App\Livewire\widget;

use App\Models\Sell;
use App\Models\Tar_sell;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepTar extends BaseWidget
{
    public $repDate1;
    public $repDate2;
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
    public array $data_list= [
        'calc_columns' => [
            'sub_tot',
        ],
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(function (){

                if ($this->repDate1 && !$this->repDate2)
                    $sell=Tar_sell::where('tar_date','>=',$this->repDate1);
                if ($this->repDate2 && !$this->repDate1)
                    $sell=Tar_Sell::where('tar_date','<=',$this->repDate1);
                if ($this->repDate1 && $this->repDate2)
                    $sell=Tar_Sell::whereBetween('tar_date',[$this->repDate1,$this->repDate2]);


                return $sell;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">ترجيع المبيعات</div>'))
            ->defaultPaginationPageOption(5)

            ->defaultSort('order_date','desc')
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي'),
                TextColumn::make('Item.name')
                    ->label('اسم الصنف'),
                TextColumn::make('q1')
                    ->label('الكمية'),
                TextColumn::make('sub_tot')
                    ->label('الاجمالي'),
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
