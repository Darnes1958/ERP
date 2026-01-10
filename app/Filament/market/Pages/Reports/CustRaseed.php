<?php

namespace App\Filament\market\Pages\Reports;

use App\Exports\CustRaseedExl;
use App\Models\Cust_tran;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;


class CustRaseed extends Page implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='أرصدة الزبائن';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين';
    protected static ?int $navigationSort=6;
    protected ?string $heading="";


    protected string $view = 'filament.market.pages.reports.cust-raseed';

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin') || Auth::user()->can('تقارير زبائن');
  }

    public $repDate1;
    public $repDate2;
    public $withZero=false;
    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();
        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);
    }
    public function getTableRecordKey(Model|array $record): string
    {
        return uniqid();
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                Checkbox::make('withZero')
                    ->live()
                    ->label('إظهار الأرصدة الصفرية'),
                Actions::make([
                    Action::make('excl')
                        ->label('Excel')
                        ->button()
                        ->color('success')
                        ->action(function (){
                            $report=Cust_tran::
                            selectRaw('sum(mden) mden,sum(daen) daen,sum(mden-daen) raseed')
                                ->when($this->repDate1,function ($q){
                                    $q->where('repDate','>=',$this->repDate1);
                                })
                                ->when($this->repDate2,function ($q){
                                    $q->where('repDate','<=',$this->repDate2);
                                })->first();
                            return Excel::download(new CustRaseedExl('ارصدة الزبائن من تاريخ '.$this->repDate1.' إلي تاريخ '.$this->repDate2,
                                $this->getTableQueryForExport()->get(),$report),'cust_tran.xlsx');
                        })
            ])->verticalAlignment(VerticalAlignment::End),

            ])->columns(6);
    }

    public function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('الزبائن')
            ->query(function (){
                $report=Cust_tran::
                    selectRaw('customer_id,name,sum(mden) mden,sum(daen) daen,sum(mden-daen) raseed')
                    ->when($this->repDate1,function ($q){
                    $q->where('repDate','>=',$this->repDate1);
                })
                    ->when($this->repDate2,function ($q){
                        $q->where('repDate','<=',$this->repDate2);
                    })
                    ->when(!$this->withZero,function ($q){
                        $q->havingRaw("sum(mden-daen) != 0");
                    })
                    ->groupBy('customer_id','name')
                ;
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
            ->columns([
                TextColumn::make('customer_id')
                    ->sortable()
                    ->searchable()
                    ->label('الرقم الألي'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('اسم الزبون'),
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
                TextColumn::make('raseed')

                    ->color(function($state){
                        if($state>=0) return 'green'; else return 'danger';
                    })

                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('الرصيد'),

            ])
            ->defaultSort('name')
            ->striped();
    }
}
