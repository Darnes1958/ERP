<?php

namespace App\Livewire\widget;

use App\Models\Sell;
use App\Models\Tar_sell;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaTar extends BaseWidget
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
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function (Tar_sell $rec){

                $rec=Tar_sell::when($this->repDate1,function ($q){
                    $q->where('tar_date','>=',$this->repDate1);
                })
                    ->when($this->repDate2,function ($q){
                        $q->where('tar_date','<=',$this->repDate2);
                    })
                    ->selectRaw('tar_date,sum(sub_tot) as sub_tot')
                    ->groupBy('tar_date');

                return $rec;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">ترجيع المبيعات</div>'))
            ->defaultPaginationPageOption(5)

            ->defaultSort('tar_date','desc')
            ->striped()
            ->columns([
                TextColumn::make('tar_date')
                    ->label('التاريخ'),
                TextColumn::make('sub_tot')
                    ->label('الاجمالي'),
            ])
            ->actions([

            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ;
    }
}
