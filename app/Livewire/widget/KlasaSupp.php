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
  public $repDate1;
  public $repDate2;

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
            'val',
            'exp',
        ],
    ];
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function(Recsupp $rec){
              if (!$this->repDate1 && !$this->repDate2)
                  return $rec=Recsupp::where('id',null);
              $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate1[4]);
              $errors = \DateTime::getLastErrors();
              if (!empty($errors['warning_count'])) {
                return false ;
              }
              $dateTime = \DateTime::createFromFormat('d/m/Y',$this->repDate2[4]);
              $errors = \DateTime::getLastErrors();
              if (!empty($errors['warning_count'])) {
                return false ;
              }
                $first=Recsupp::
                  when($this->repDate1,function ($q){
                    $q->where('receipt_date','>=',$this->repDate1); })
                  ->when($this->repDate2,function ($q){
                    $q->where('receipt_date','<=',$this->repDate2); })
                  ->join('price_types','price_type_id','price_types.id')
                  ->leftjoin('accs','acc_id','accs.id')
                  ->where('imp_exp',0)
                  ->selectRaw('rec_who,price_types.name,accs.name accName,0 as exp,sum(recsupps.val) as val')
                  ->groupby('rec_who','price_types.name','accs.name');

                $rec=Recsupp::
                  when($this->repDate1,function ($q){
                    $q->where('receipt_date','>=',$this->repDate1); })
                  ->when($this->repDate2,function ($q){
                    $q->where('receipt_date','<=',$this->repDate2); })
                  ->join('price_types','price_type_id','price_types.id')
                  ->leftjoin('accs','acc_id','accs.id')
                  ->where('imp_exp',1)
                  ->selectRaw('rec_who,price_types.name,accs.name accName,sum(recsupps.val) as exp,0 as val')
                  ->groupby('rec_who','price_types.name','accs.name')
                    ->union($first);
                return $rec;
            }

            )
          ->emptyStateHeading('لا توجد بيانات')
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">الموردين</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
            ->paginated(false)
            ->defaultSort('val')
            ->columns([
                Tables\Columns\TextColumn::make('rec_who')
                    ->label('البيان'),
                Tables\Columns\TextColumn::make('name')
                    ->label('طريقة الدفع'),
              Tables\Columns\TextColumn::make('accName')
                ->label('الحساب المصرفي'),
                Tables\Columns\TextColumn::make('val')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->state(function (Recsupp $record): string {
                        if ($record->val==0)
                            return ''; else return $record->val;
                    })
                    ->label('قبض'),
                Tables\Columns\TextColumn::make('exp')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->state(function (Recsupp $record): string {
                        if ($record->exp==0)
                            return ''; else return $record->exp;
                    })
                    ->label('دفع'),

            ]);
    }
}
