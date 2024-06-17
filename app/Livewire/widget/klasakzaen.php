<?php

namespace App\Livewire\widget;

use App\Models\Acc_tran;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class klasakzaen extends BaseWidget
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
            'mden',
            'daen',
            'raseed',

        ],
    ];
    public function getTableRecordKey(Model $record): string
  {
    return uniqid();
  }
    public function table(Table $table): Table
    {
        return $table
            ->query(function (Acc_tran $rec){

              $rec=Acc_tran::
                where('receipt_date','>=',$this->repDate1)
                ->where('receipt_date','<=',$this->repDate2)
                ->where('kazena_id','!=',null)
                ->join('kazenas','kazena_id','kazenas.id')
                ->selectRaw('kazenas.name,sum(mden) as mden,sum(daen) as daen,sum(mden-daen) raseed')
                ->groupby('kazenas.name');
                return $rec;
            }

            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">أرصدة الخزائن</div>'))
            ->defaultPaginationPageOption(5)
            ->defaultSort('name')
            ->striped()
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('البيان'),
                Tables\Columns\TextColumn::make('mden')
                    ->label('مدين'),
                Tables\Columns\TextColumn::make('daen')
                    ->label('دائن'),
                Tables\Columns\TextColumn::make('raseed')
                  ->color(function ($state){if ($state<0) return 'danger'; else return 'info';})
                    ->label('الرصيد'),
            ])

            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ;
    }
}
