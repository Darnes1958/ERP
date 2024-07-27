<?php

namespace App\Filament\Pages\Reports;

use App\Models\Acc;
use App\Models\Acc_tran;

use App\Models\Kazena;
use App\Models\Masrofat;
use App\Models\Salarytran;
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
use Illuminate\Support\Facades\Auth;

class AccTran extends Page  implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='حركة مصرف';
    protected static ?string $navigationGroup='مصارف وخزائن';

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->can('ادخال خزائن') || Auth::user()->can('ادخال مصارف');
  }
    protected ?string $heading="";

    protected static string $view = 'filament.pages.reports.acc-tran';

    public $repDate1;
    public $repDate2;
    public $acc_id;
    public $mden=null;
    public $daen=null;
    public $last_mden=null;
    public $last_daen=null;
    public $balance=null;
    public $raseed;
    public $last_raseed;
    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();
        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);
    }
    public array $data_list= [
        'calc_columns' => [
            'mden',
            'daen',
            'order_id',
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
                        $this->balance=Acc::find($state)->balance;
                        $this->last_mden=$this->balance+Acc_tran::where('kazena_id',$this->acc_id)->where('receipt_date','<',$this->repDate1)->sum('mden');
                        $this->last_daen=Acc_tran::where('acc_id',$this->acc_id)->where('receipt_date','<',$this->repDate1)->sum('daen');
                        $this->last_raseed=abs($this->last_mden-$this->last_daen);
                        $this->mden=$this->last_mden+Acc_tran::where('acc_id',$this->acc_id)->whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->sum('mden');
                        $this->daen=$this->last_daen+Acc_tran::where('acc_id',$this->acc_id)->whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->sum('daen');
                        $this->raseed=abs($this->mden-$this->daen);
                    })
                    ->label('الحساب'),
                DatePicker::make('repDate1')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate1=$state;
                        if ($this->repDate1 && $this->balance)
                        {
                            $this->last_mden=$this->balance+Acc_tran::where('acc_id',$this->acc_id)->where('receipt_date','<',$this->repDate1)->sum('mden');
                            $this->last_daen=Acc_tran::where('acc_id',$this->acc_id)->where('receipt_date','<',$this->repDate1)->sum('daen');
                            $this->last_raseed=abs($this->last_mden-$this->last_daen);
                            $this->mden=$this->last_mden+Acc_tran::where('acc_id',$this->acc_id)->whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->sum('mden');
                            $this->daen=$this->last_daen+Acc_tran::where('acc_id',$this->acc_id)->whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->sum('daen');
                            $this->raseed=abs($this->mden-$this->daen);
                        }

                    })
                    ->label('من تاريخ'),
                DatePicker::make('repDate2')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate2=$state;
                        $this->repDate2=$state;
                        $this->mden=$this->last_mden+Acc_tran::where('acc_id',$this->acc_id)->whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->sum('mden');
                        $this->daen=$this->last_daen+Acc_tran::where('acc_id',$this->acc_id)->whereBetween('receipt_date',[$this->repDate1,$this->repDate2])->sum('daen');
                        $this->raseed=abs($this->mden-$this->daen);
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
                ->where('acc_id',$this->acc_id)
                ->when($this->repDate1,function ($q){
                        $q->where('receipt_date','>=',$this->repDate1);
                    })
                ->when($this->repDate2,function ($q){
                        $q->where('receipt_date','<=',$this->repDate2);
                    });
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
            ->header(function () {return view('table.acc_header', [
                'last_mden' => $this->last_mden,'last_daen'=>$this->last_daen,'balance'=>$this->balance,'last_raseed'=>$this->last_raseed,
            ]); })

            ->contentFooter(function (){return view('table.acc_footer', $this->data_list,['raseed'=>$this->raseed,'mden'=>$this->mden,'daen'=>$this->daen,]);} )
            ->columns([
                TextColumn::make('rec_who')
                    ->sortable()
                    ->description(function (Acc_tran $record) {
                        if ($record->rec_who->value ==10) return 'من '.Kazena::find($record->kazena2_id)->name;
                        if ($record->rec_who->value ==11 ) return 'إلي '.Kazena::find($record->kazena2_id)->name;
                        if ($record->rec_who->value ==12){
                         if ($record->mden==0) return 'الي '.Acc::find($record->acc2_id)->name;
                         else return 'من '.Acc::find($record->acc2_id)->name;
                        }
                        if ($record->rec_who->value ==13)  return Masrofat::find($record->id)->Masr_type->name;
                        if ($record->rec_who->value ==14)  return Salarytran::find($record->id)->Salary->name;
                    })
                    ->searchable()
                    ->label('البيان'),
                TextColumn::make('receipt_date')
                    ->sortable()
                    ->searchable()
                    ->label('التاريخ'),

                TextColumn::make('mden')
                    ->color('danger')

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
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('دائن'),
                TextColumn::make('order_id')
                    ->searchable()
                    ->label('رقم المستند'),
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
