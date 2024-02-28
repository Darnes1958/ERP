<?php

namespace App\Livewire\widget;

use App\Models\Recsupp;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaSupp extends BaseWidget
{
    public $repDate;

    #[On('updateRep')]
    public function updaterep($repdate)
    {
        $this->repDate=$repdate;

    }
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function(Recsupp $rec){
                if (!$this->repDate) return $rec=Recsupp::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
                $first=Recsupp::where('receipt_date',$this->repDate)
                    ->where('imp_exp',0)
                    ->selectRaw('0 as exp,sum(val) as val');

                $rec=Recsupp::where('receipt_date',$this->repDate)
                    ->where('imp_exp',1)
                    ->selectRaw('sum(val) as exp,0 as val')
                    ->union($first);
                return $rec;
            }

            )
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">الموردين</div>'))
            ->paginated(false)
            ->defaultSort('val')
            ->columns([
                Tables\Columns\TextColumn::make('val')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('قبض'),
                Tables\Columns\TextColumn::make('exp')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('دفع'),

            ]);
    }
}
