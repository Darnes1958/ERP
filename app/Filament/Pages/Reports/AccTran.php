<?php

namespace App\Filament\Pages\Reports;

use App\Models\Acc;
use App\Models\Acc_tran;

use App\Models\Kazena;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AccTran extends Page  implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='حركة مصرف';
    protected static ?string $navigationGroup='مصارف وخزائن';


    protected ?string $heading="";

    protected static string $view = 'filament.pages.reports.acc-tran';

    public $repDate1;
    public $repDate2;
    public $acc_id;
    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();
        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);
    }
    public array $data_list= [
        'calc_columns' => [
            'mden',
            'daen',
        ],
    ];
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('acc_id')
                    ->options(Acc::all()->pluck('name','id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state,Set $set){
                        $this->acc_id=$state;
                    })
                    ->label('الحساب'),
                DatePicker::make('repDate1')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate1=$state;

                    })
                    ->label('من تاريخ'),
                DatePicker::make('repDate2')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate2=$state;

                    })
                    ->label('إلي تاريخ'),
                \Filament\Forms\Components\Actions::make([

                    \Filament\Forms\Components\Actions\Action::make('Exl')
                        ->label('Excel')
                        ->visible(function (){
                            return ($this->chkDate($this->repDate1) || $this->chkDate($this->repDate2)) && $this->acc_id;
                        })
                        ->button()
                        ->color('success')
                        ->url(fn (): string => route('acctranexl', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'acc_id'=>$this->acc_id,]))
                ])->verticalAlignment(VerticalAlignment::End),

            ])->columns(6);
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Acc_tran::
                when($this->acc_id==null,function ($q){
                    $q->where('id',null);
                })
                ->where(function ($q){
                    $q->where('acc_id',$this->acc_id)
                      ->orwhere('acc2_id',$this->acc_id);
                      })


                    ->when($this->repDate1,function ($q){
                        $q->where('receipt_date','>=',$this->repDate1);
                    })
                    ->when($this->repDate2,function ($q){
                        $q->where('receipt_date','<=',$this->repDate2);
                    });
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ->columns([
                TextColumn::make('rec_who')
                    ->sortable()
                    ->description(function (Acc_tran $record) {
                        if ($record->rec_who->value ==10) return 'من '.Acc::find($record->acc2_id)->name;
                        if ($record->rec_who->value ==11 ) return 'إلي '.Kazena::find($record->kazena2_id)->name;
                        if ($record->rec_who->value ==12){
                         if ($record->acc_id==$this->acc_id) return 'الي '.Acc::find($record->acc2_id)->name;
                         if ($record->acc2_id==$this->acc_id) return 'من '.Acc::find($record->acc_id)->name;
                        }
                    })
                    ->searchable()
                    ->label('البيان'),
                TextColumn::make('receipt_date')
                    ->sortable()
                    ->searchable()
                    ->label('التاريخ'),

                TextColumn::make('mden')
                    ->color('danger')
                    ->state(function (Acc_tran $record){
                     if ($record->rec_who->value>8){
                       if ($record->acc2_id==$this->acc_id) return $record->daen;
                       else return 0;
                     } else return $record->mden;

                    }

                    )
                    ->searchable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('مدين'),
                TextColumn::make('daen')
                    ->color('info')
                    ->searchable()
                    ->state(function (Acc_tran $record){
                        if ($record->rec_who->value>8){
                            if ($record->acc2_id==$this->acc_id) return 0;
                            else return $record->daen;
                        } else return $record->daen;

                    }

                    )

                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('دائن'),
                TextColumn::make('order_id')
                    ->searchable()
                    ->label('رقم الفاتورة'),
                TextColumn::make('notes')
                    ->searchable()
                    ->label('ملاحظات'),

            ])
            ->defaultSort('receipt_date')
            ->striped()
            ;
    }
    public function chkDate($repDate){
        try {
            Carbon::parse($repDate);
            return true;
        } catch (InvalidFormatException $e) {
            return false;
        }
    }
}
