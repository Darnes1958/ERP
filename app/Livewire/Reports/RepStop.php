<?php

namespace App\Livewire\Reports;

use App\Livewire\Traits\PublicTrait;
use App\Models\OurCompany;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Columns\Summarizers\Summarizer;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use App\Livewire\Traits\MainTrait;
use App\Models\Bank;
use App\Models\Main;
use App\Models\Taj;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Forms;

use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;

class RepStop extends Component implements HasTable, HasForms,HasActions
{
  public $bank_id;
  public $bank;
  public $taj;
  public $By=2;
  public $is_show=false;
  public $field='id';
  public $query;
  public $rep_name='All';
  public $Date1;
  public $Date2;
  public $Baky=5;
  public $BakyLabel='الباقي';

  use InteractsWithTable,InteractsWithForms,InteractsWithActions;
use PublicTrait;
  public function printAction(): Action
  {
      return Action::make('print')
        ->label('طباعة')
        ->button()
        ->color('danger')
        ->icon('heroicon-m-printer')
        ->color('info')
        ->action(function (){
            $RepDate=date('Y-m-d');
            $cus=OurCompany::where('Company',Auth::user()->company)->first();
           if ($this->By==1)
            $taj=Taj::find(Bank::find($this->bank_id)->taj_id);
           else $taj=Taj::find($this->taj);

            $BankName=$taj->TajName;
            $TajAcc=$taj->TajAcc;

            Pdf::view('PrnView.pdf-stop',
                ['RepTable'=>$this->getTableQueryForExport()->get(),
                    'cus'=>$cus,'RepDate'=>$RepDate,'TajAcc' => $TajAcc,'BankName'=>$BankName,
                ])
                ->headerHtml('<div>My header</div>')
                ->footerView('PrnView.footer')
                ->margins(10, 10, 40, 10, Unit::Pixel)
                ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
            $file= public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

            $headers = [
                'Content-Type' => 'application/pdf',
            ];
            return Response::download($file, 'filename.pdf', $headers);

        });

  }

  public function form(Schema $schema): Schema
  {
    return $schema

      ->schema([
        Group::make([
          Radio::make('By')
            ->hiddenLabel()
            ->inlineLabel()
            ->inline()
            ->reactive()
            ->options([
              '2' => 'بالتجميعي',
              '1' => 'بفروع المصارف',

            ]),

        ]),
        Group::make([
        Select::make('bank')
          ->columnSpan(2)
          ->inlineLabel()
          ->options(Bank::all()->pluck('BankName', 'id')->toArray())
          ->searchable()
          ->reactive()
          ->Label('فرع المصرف')
          ->visible(function () {
            return $this->By==1;
          })
          ->afterStateUpdated(function (callable $get) {
            $this->bank_id=$get('bank');
            $this->field='id';
          }),
        Select::make('taj')
          ->columnSpan(2)
          ->inlineLabel()
          ->options(Taj::all()->pluck('TajName', 'id')->toArray())
          ->searchable()
          ->Label('المصرف التجميعي')
          ->reactive()
          ->visible(function () {
            return $this->By==2;
          })

          ->afterStateUpdated(function (callable $get) {
            $this->bank_id=$get('taj');
            $this->field='taj_id';
          }),
        DatePicker::make('Date1')
          ->inlineLabel()
          ->label('من')
          ->reactive(),
        DatePicker::make('Date2')
          ->inlineLabel()
          ->label('إلي')
          ->reactive(),
      ])->columns(5)
    ]);
  }

  public function table(Table $table):Table
  {
    return $table
      ->query(function ()  {
        if ($this->By==1) {
          $main=Main::where('bank_id',$this->bank_id)
            ->has('Stop')

          ;
        }
        if ($this->By==2) {
          $main=Main::whereIn('bank_id',function ($q){
            $q->select('id')->from('banks')->where('taj_id',$this->bank_id);
          })
            ->has('Stop')
          ;
        }
        return  $main;
      })
      ->columns([
        TextColumn::make('id')
          ->label('رقم العقد')
          ,
        TextColumn::make('acc')
          ->label('رقم الحساب')
          ->summarize(
            Summarizer::make()
              ->using(function (){
                return Main::when($this->By==1,function ($q){
                  $q->where('bank_id',$this->bank_id);
                })
                  ->when($this->By==2,function ($q){
                    $q->whereIn('bank_id',function ($q){
                      $q->select('id')->from('banks')->where('taj_id',$this->bank_id);
                    });
                  })
                  ->count();})
              ->label('العدد')

          ),
        TextColumn::make('Customer.name')
          ->label('الاسم'),
        TextColumn::make('sul')
          ->label('اجمالي العقد')
          ->summarize(
            Summarizer::make()
              ->using(function (){
                return Main::when($this->By==1,function ($q){
                  $q->where('bank_id',$this->bank_id);
                })
                  ->when($this->By==2,function ($q){
                    $q->whereIn('bank_id',function ($q){
                      $q->select('id')->from('banks')->where('taj_id',$this->bank_id);
                    });
                  })
                  ->sum('sul');})
          ),
        TextColumn::make('kst')
          ->label('القسط'),
        TextColumn::make('pay')
          ->label('المسدد'),
        TextColumn::make('raseed')
          ->label('الرصيد'),

        TextColumn::make('Stop.stop_date')
          ->label('تاريخ الإيقاف')
          ->color('info'),

      ])
      ->recordActions([
        Action::make('print')
          ->hiddenLabel()
          ->button()
          ->color('danger')
          ->icon('heroicon-m-printer')

          ->color('info')
          ->action(function (Main $record){
              $RepDate=date('Y-m-d');
              $cus=OurCompany::where('Company',Auth::user()->company)->first();
              $taj=Taj::find(Bank::find($record->bank_id)->taj_id);

              $BankName=$taj->TajName;
              $TajAcc=$taj->TajAcc;

              Pdf::view('PrnView.pdf-stop-one',
                  ['record'=>$record,
                      'cus'=>$cus,'RepDate'=>$RepDate,'TajAcc' => $TajAcc,'BankName'=>$BankName,
                  ])
                  ->headerHtml('<div>My header</div>')
                  ->footerView('PrnView.footer')
                  ->margins(10, 10, 40, 10, Unit::Pixel)
                  ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
              $file= public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

              $headers = [
                  'Content-Type' => 'application/pdf',
              ];
              return Response::download($file, 'filename.pdf', $headers);
          })




      ])
      ;


  }

  public function mount(){
    $date1=Carbon::now();
    $this->Date1=$date1->startOfYear()->toDateString();

    $this->Date2=date('Y-m-d');

  }

  public function render()
    {
        return view('livewire.reports.rep-stop');
    }
}
