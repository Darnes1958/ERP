<?php

namespace App\Livewire\widget;


use App\Models\Receipt;
use App\Models\ReceiptUnion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaCust extends BaseWidget
{
  public $repDate1;
  public $repDate2;
    public $place_id;

  public function mount(){
    $this->repDate1=now();
    $this->repDate2=now();

  }
    #[On('updateklasaplace')]
    public function updatklasaplace($place)
    {
        $this->place_id=$place;
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
            ->query(function(ReceiptUnion $rec){



                $rec=ReceiptUnion::
                    when($this->repDate1,function ($q){
                      $q->where('receipt_date','>=',$this->repDate1); })
                    ->when($this->repDate2,function ($q){
                        $q->where('receipt_date','<=',$this->repDate2); })
                    ->join('price_types','price_type_id','price_types.id')
                  ->leftjoin('accs','acc_id','accs.id')
                    ->leftjoin('kazenas','kazena_id','kazenas.id')

                    ->selectRaw('rec_who,price_types.name,accs.name accName,kazenas.name kazenaName,sum(receipt_unions.exp) as exp,sum(receipt_unions.val) as val')
                    ->groupby('rec_who','price_types.name','accs.name','kazenas.name')
                   ;

                return $rec;
            }

            )
          ->emptyStateHeading('لا توجد بيانات')
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">الزبائن</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
          ->defaultPaginationPageOption(5)
            ->defaultSort('val')
            ->columns([
                Tables\Columns\TextColumn::make('rec_who')
                    ->label('البيان'),
                Tables\Columns\TextColumn::make('name')
                    ->label('طريقة الدفع'),
                Tables\Columns\TextColumn::make('accName')
                    ->state(function (Model $record) {
                        if ($record->accName!=null) return $record->accName; else return $record->kazenaName;
                    })
                    ->label('الحساب'),

                Tables\Columns\TextColumn::make('val')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->state(function (ReceiptUnion $record): string {
                        if ($record->val==0)
                        return ''; else return $record->val;
                    })
                    ->label('قبض'),
                Tables\Columns\TextColumn::make('exp')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->state(function (ReceiptUnion $record): string {
                        if ($record->exp==0)
                            return ''; else return $record->exp;
                    })
                    ->label('دفع'),
            ]);
    }
}
