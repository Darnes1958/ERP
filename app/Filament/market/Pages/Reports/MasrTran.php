<?php

namespace App\Filament\market\Pages\Reports;

use App\Models\Masr_type;
use App\Models\Masrofat;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MasrTran extends Page  implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='حركة مصروفات';
    protected static string | \UnitEnum | null $navigationGroup='مصروفات';
    protected ?string $heading="";
    protected string $view = 'filament.market.pages.reports.masr-tran';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('مصروفات') ||  Auth::user()->can('تقارير مصروفات');
    }


    public $repDate1;
    public $repDate2;
    public $masr_id;

    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();

        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,]);
    }

    public array $data_list= [
        'calc_columns' => [
            'val',

        ],
    ];

    public function getTableRecordKey(Model|array $record): string
    {
        return uniqid();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('masr_id')
                    ->options(Masr_type::all()->pluck('name','id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->masr_id=$state;

                    })
                    ->label('نوع المصروفات'),
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


            ])->columns(6);
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Masrofat::
                where('masr_type_id',$this->masr_id)

                    ->when($this->repDate1,function ($q){
                        $q->where('masr_date','>=',$this->repDate1);
                    })
                    ->when($this->repDate2,function ($q){
                        $q->where('masr_date','<=',$this->repDate2);
                    })

                ;
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')

            ->contentFooter(function (){return view('table.footer', $this->data_list);} )
            ->columns([
                TextColumn::make('masr_date')
                    ->label('التاريخ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('Acc.name')
                    ->label('المصرف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Kazena.name')
                    ->label('الخزينة')
                    ->searchable()
                    ->sortable(),
               TextColumn::make('val')
                    ->label('المبلغ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->searchable()
                    ->sortable(),


            ])
            ->defaultSort('masr_date')
            ->striped();
    }
}
