<?php

namespace App\Livewire\Reports;

use App\Livewire\Traits\MainTrait;
use App\Models\Bank;
use App\Models\Main;
use App\Models\Taj;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Form;
use App\Http\Controllers\PdfController;
use Illuminate\Database\Query\Builder;
use Filament\Tables\Columns\Summarizers\Summarizer;



class RepAll extends Component implements HasTable, HasForms
{

public $bank_id;
public $bank;
public $taj;
public $By=1;
public $is_show=false;
public $field='id';
public $query;
public $rep_name='All';
public $Date1;
public $Date2;
public $Baky=5;
public $BakyLabel='الباقي';

public $sul;
public $pay;
public $raseed;
    use InteractsWithTable,InteractsWithForms;
    use MainTrait;
  public array $data_list= [
    'calc_columns' => [
      'acc',
      'sul',
      'pay',
      'raseed',
    ],
  ];

    public function updatedBy(){
      $this->form($this->form);

    }

  public function form(Schema $schema): Schema
  {
    return $schema
      ->schema([

        Select::make('bank')
          ->columnSpan(2)
          ->inlineLabel()
          ->options(Bank::all()->pluck('BankName', 'id')->toArray())

          ->searchable()
          ->reactive()
          ->Label('فرع المصرف')
          ->visible($this->By==1)
          ->afterStateUpdated(function (callable $get) {
            $this->bank_id=$get('bank');
            $this->field='id';
            $this->table($this->table);

          }),
        Select::make('taj')
            ->columnSpan(2)
          ->inlineLabel()
          ->options(Taj::all()->pluck('TajName', 'id')->toArray())
          ->searchable()
          ->Label('المصرف التجميعي')
          ->reactive()
          ->visible($this->By==2)
          ->afterStateUpdated(function (callable $get) {
            $this->bank_id=$get('taj');
            $this->field='taj_id';
            $this->table($this->table);
          }),
        Select::make('rep_name')
           ->columnSpan(2)
          ->inlineLabel()
          ->label('التقرير')
          ->default('All')
          ->reactive()

          ->options([
            'All' => 'كشف بالأسماء',
            'Mosdada' => 'المسددة',
            'Motakra' => 'المتأخرة',
            'Mohasla' => 'المحصلة',
            'Not_Mohasla' => 'الغير محصلة',
          ])
            ->afterStateUpdated(function (callable $get){
              if ($get('rep_name')=='Mosdada') {$this->Baky=5;$this->BakyLabel='الباقي';}
              if ($get('rep_name')=='Motakra') {$this->Baky=1;$this->BakyLabel='عدد الأقساط المتأخرة';}

            }),

          TextInput::make('Baky')
              ->inlineLabel()
              ->label(function (){
                return $this->BakyLabel;
              })
              ->reactive()
          ->numeric()
              ->visible(fn (Get $get): bool => $get('rep_name')=='Mosdada' || $get('rep_name')=='Motakra'),

          DatePicker::make('Date1')
            ->inlineLabel()
            ->label('من')
            ->reactive()
            ->visible(fn (Get $get): bool => $get('rep_name')=='Mohasla' || $get('rep_name')=='Not_Mohasla'),
          DatePicker::make('Date2')
            ->inlineLabel()
            ->label('إلي')
            ->reactive()
              ->visible(fn (Get $get): bool => $get('rep_name')=='Mohasla' || $get('rep_name')=='Not_Mohasla'),

      ])->columns(7);
  }



    public function table(Table $table):Table
    {
      return $table
        ->query(function ()  {
            if ($this->By==1) {
                 $main=Main::where('bank_id',$this->bank_id)
                 ->when($this->rep_name=='Mosdada' , function ($q) {
                     $q->where('raseed','<=',$this->Baky); })
                 ->when($this->rep_name=='Motakra' , function ($q) {
                    $q->where('late','>=',$this->Baky); }) ;
            }
            if ($this->By==2) {
                $main=Main::whereIn('bank_id',function ($q){
                    $q->select('id')->from('banks')->where('taj_id',$this->bank_id);
                    })
                    ->when($this->rep_name=='Mosdada' , function ($q) {
                        $q->where('raseed','<=',$this->Baky); })
                    ->when($this->rep_name=='Motakra' , function ($q) {
                        $q->where('late','>=',$this->Baky); }) ;
            }
          $this->sul=number_format($main->sum('sul'),0, '', ',')  ;
          $this->pay=number_format($main->sum('pay'),0, '', ',')  ;
          $this->raseed=number_format($main->sum('raseed'),0, '', ',')  ;
            return  $main;
        })
        ->columns([
            TextColumn::make('id')
                ->label('رقم العقد'),
            TextColumn::make('acc')
                ->label('رقم الحساب'),
            TextColumn::make('Customer.name')
             ->label('الاسم'),
            TextColumn::make('sul')
              ->label('اجمالي العقد')

              ,
            TextColumn::make('kst')
              ->label('القسط'),
            TextColumn::make('pay')
              ->label('المسدد')
              ,
            TextColumn::make('raseed')
              ->label('الرصيد')
              ,

            TextColumn::make('Late')
                ->label('متأخرة')
                ->visible(fn (Get $get): bool =>$this->rep_name =='Motakra')
                ->color('danger')
              ,
            TextColumn::make('sul_begin')
                ->label('تاريخ العقد')
                ->visible(fn (Get $get): bool =>$this->rep_name =='Motakra')
                ->color('info'),
            TextColumn::make('LastKsm')
                ->label('ت.أخر قسط')
                ->visible(fn (Get $get): bool =>$this->rep_name =='Motakra')
                ->color('danger'),


        ])
        ->contentFooter(view('sum-footer', $this->data_list));
    }

    public function mount(){
     $this->Date1=date('Y-m-d');
     $this->Date2=date('Y-m-d');

     $this->LateChk();
    }
    public function render()
    {

        return view('livewire.reports.rep-all');
    }
}
