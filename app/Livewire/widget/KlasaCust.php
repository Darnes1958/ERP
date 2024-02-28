<?php

namespace App\Livewire\widget;


use App\Models\Receipt;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaCust extends BaseWidget
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
            ->query(function(Receipt $rec){
                if (!$this->repDate) return $rec=Receipt::where('id',null);
                $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate[4]);
                $errors = \DateTime::getLastErrors();
                if (!empty($errors['warning_count'])) {
                    return false ;
                }
                $first=Receipt::where('receipt_date',$this->repDate)
                    ->where('imp_exp',0)
                    ->selectRaw('\'قبض\' as name,sum(val) as val');

                $rec=Receipt::where('receipt_date',$this->repDate)
                    ->where('imp_exp',1)
                    ->selectRaw('\'دفع\' as name, sum(val) as val')
                    ->union($first);
                return $rec;
            }

            )
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">الزبائن</div>'))
            ->paginated(false)
            ->defaultSort('val')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('البيان'),
                Tables\Columns\TextColumn::make('val')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('الإجمالي'),

            ]);
    }
}
