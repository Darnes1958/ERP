<?php

namespace App\Livewire\widget;

use App\Models\Rebh_second;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Filament\Widgets\TableWidget as BaseWidget;

class ArbahAll extends BaseWidget
{
   public $year;
    public array $data_list= [
        'calc_columns' => [
            'rebh',
            'rent',
            'masr',
            'sal',
            'ksm',
            'safi',
        ],
    ];
    public function getTableRecordKey(Model|array $record): string
    {
        return uniqid();
    }

    #[On('updateyear')]
    public function updateyear($year)
    {
        $this->year=$year;
        info($year);
        info($this->year);

    }
    protected string $view = 'livewire.widget.arbah-all';
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){

                $res=Rebh_second::selectRaw('month(date) date
                ,round(sum(rebh),0) rebh
                ,round(sum(masr),0) masr
                ,round(sum(rent),0) rent
                ,round(sum(sal),0) sal
                ,round(sum(ksm),0) ksm
                ,round(sum(rebh),0)-
                 round(sum(masr),0)-
                 round(sum(rent),0)-
                 round(sum(ksm),0)-
                 round(sum(sal),0) safi')
                    ->WhereYear('date',$this->year)
                    ->groupByRaw('month(date)');

                return $res;
            }

            )
            ->paginated([5,10,12,])
            ->defaultPaginationPageOption(12)
            ->extremePaginationLinks()
            ->heading(fn()=> new HtmlString('<div class="text-primary-400 text-lg">'.'الارباح بالأشهر لسنه '.$this->year.'</div>'))
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
                TextColumn::make('ksm')
                    ->label('خصومات'),
                TextColumn::make('safi')
                    ->label('صافي الأرباح'),


            ]);
    }
}
