<?php

namespace App\Livewire\widget;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Models\Buy;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepBuy extends BaseWidget
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
            'tot',
            'pay',
            'baky',
        ],
    ];

    public function table(Table $table): Table
    {

            return $table
                ->query(function (Buy $buy){

                    if ($this->repDate1 && !$this->repDate2)
                      $buy=Buy::where('order_date','>=',$this->repDate1)->when($this->place_id,function ($q){
                          return $q->where('place_id',$this->place_id);
                      });
                    if ($this->repDate2 && !$this->repDate1)
                      $buy=Buy::where('order_date','=<',$this->repDate1)->when($this->place_id,function ($q){
                          return $q->where('place_id',$this->place_id);
                      });
                    if ($this->repDate1 && $this->repDate2)
                      $buy=Buy::whereBetween('order_date',[$this->repDate1,$this->repDate2])->when($this->place_id,function ($q){
                          return $q->where('place_id',$this->place_id);
                      });


                  return $buy;
                }
                // ...
                )
                ->heading(new HtmlString('<div class="text-primary-400 text-lg">فواتير المشتريات</div>'))
                ->defaultPaginationPageOption(5)

                ->defaultSort('order_date','desc')
                ->striped()
                ->columns([
                    TextColumn::make('id')
                        ->sortable()
                        ->label('رقم الفاتورة'),
                    TextColumn::make('Supplier.name')
                        ->label('المورد'),
                    TextColumn::make('tot')
                        ->label('الإجمالي'),
                    TextColumn::make('pay')
                        ->label('المدفوع'),
                    TextColumn::make('baky')
                        ->label('المتبقي'),
                    TextColumn::make('notes')
                        ->label('ملاحظات'),

                ])

                ->recordActions([
                    Action::make('print')
                        ->icon('heroicon-o-printer')
                        ->iconButton()
                        ->color('blue')
                        ->url(fn (Buy $record): string => route('pdfbuy', ['id' => $record->id]))
                    ])

              ->recordActions([

                Action::make('عرض')
                  ->modalHeading(false)
                  ->action(fn (Buy $record) => $record->id())
                  ->modalSubmitAction(false)
                  ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
                  ->modalContent(fn (Buy $record): View => view(
                    'filament.pages.reports.views.view-buy-tran',
                    ['record' => $record],
                  ))
                  ->icon('heroicon-o-eye')
                  ->iconButton()
              ])

                ->emptyStateHeading('لا توجد بيانات')
                ->contentFooter(view('table.footer', $this->data_list))
                ;
    }
}
