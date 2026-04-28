<?php

namespace App\Livewire\widget;

use App\Models\Rebh_first_new;
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
    }
    protected string $view = 'livewire.widget.arbah-all';
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
$res=Rebh_first_new::selectRaw('
                wyear,
                wmonth,
                wmonth_name date ,
                round(dbo.RebhNew(wyear,wmonth,\'rebh\'),0) rebh,
                round(dbo.RebhNew(wyear,wmonth,\'masr\'),0) masr,
                round(dbo.RebhNew(wyear,wmonth,\'rent\'),0) rent,
                round(dbo.RebhNew(wyear,wmonth,\'sal\'),0) sal,
                round(dbo.RebhNew(wyear,wmonth,\'ksm\'),0) ksm,

                round(dbo.RebhNew(wyear,wmonth,\'rebh\'),0) -
                round(dbo.RebhNew(wyear,wmonth,\'masr\'),0) -
                round(dbo.RebhNew(wyear,wmonth,\'rent\'),0) -
                round(dbo.RebhNew(wyear,wmonth,\'ksm\'),0) -
                round(dbo.RebhNew(wyear,wmonth,\'sal\'),0) safi
                ')
    ->Where('wyear',$this->year)
    ->groupBy('wyear','wmonth','wmonth_name')
;

                return $res;
            }

            )
            ->paginated([5,10,12,])
            ->defaultPaginationPageOption(12)
            ->extremePaginationLinks()
            ->heading(fn()=> new HtmlString('<div class="text-primary-400 text-lg">'.'الارباح بالأشهر لسنه '.$this->year.'</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
            ->defaultSort('wmonth')
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
