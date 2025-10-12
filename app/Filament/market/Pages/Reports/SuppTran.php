<?php

namespace App\Filament\market\Pages\Reports;

use App\Exports\SuppTranExl;
use App\Livewire\Traits\PublicTrait;
use App\Models\Buy;
use App\Models\Supp_tran;
use App\Models\Supp_tran2;
use App\Models\Supplier;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class SuppTran extends Page implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    use PublicTrait;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='حركة مورد';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين';
  protected static ?int $navigationSort=7;
    protected ?string $heading="";
    protected string $view = 'filament.market.pages.reports.supp-tran';

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin')  || Auth::user()->can('تقارير موردين');
  }


  public $cust_id;
    public $repDate;
    public $formData;

    public function mount(){
        $this->repDate=now();

        $this->myForm->fill(['repDate'=>$this->repDate,'raseed'=>0,'mden'=>0,'daen'=>0]);
    }


    public function myForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('formData')
            ->components([
                Section::make()
                    ->schema([
                        Select::make('cust_id')
                            ->prefixIcon('heroicon-m-user-circle')
                            ->prefixIconColor('Fuchsia')
                            ->columnSpan(2)
                            ->options(Supplier::all()->pluck('name','id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state,Set $set){
                                $this->cust_id=$state;
                                if ($this->repDate) {
                                    $mden=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                                    $daen=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                                    $set('mden',number_format($mden, 2, '.', ','));
                                    $set('daen',number_format($daen, 2, '.', ','));
                                    $set('raseed',number_format($mden-$daen, 2, '.', ','));
                                }
                            })
                            ->label('المورد'),
                        DatePicker::make('repDate')
                            ->live()
                            ->afterStateUpdated(function ($state,Set $set){
                                $this->repDate=$state;
                                if ($this->repDate && $this->cust_id) {
                                    $mden=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                                    $daen=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                                    $set('mden',number_format($mden, 2, '.', ','));
                                    $set('daen',number_format($daen, 2, '.', ','));
                                    $set('raseed',number_format($mden-$daen, 2, '.', ','));


                                }
                            })
                            ->label('من تاريخ'),

                        TextInput::make('mden')
                            ->prefixIcon('heroicon-m-minus')
                            ->prefixIconColor('danger')
                            ->readOnly()
                            ->label('مدين'),
                        TextInput::make('daen')
                            ->prefixIcon('heroicon-m-plus')
                            ->prefixIconColor('info')
                            ->readOnly()
                            ->label('دائن'),
                        TextInput::make('raseed')
                            ->prefixIcon('heroicon-m-bars-2')
                            ->prefixIconColor('success')
                            ->readOnly()
                            ->label('الرصيد'),
                        Actions::make([
                            Action::make('printorder')
                                ->label('طباعة')
                                ->visible(function (){
                                    return $this->chkDate($this->repDate) && $this->cust_id;
                                })

                                ->button()
                                ->color('danger')
                                ->icon('heroicon-m-printer')
                                ->color('info')
                                ->action(function (Get $get){
                                    $res=$this->getTableQueryForExport()->get();
                                    $RepDate=date('Y-m-d');
                                    return Response::download(self::ret_spatie($res,
                                        'PDF.pdf-supp-tran',['RepDate'=>$RepDate,'tran_date'=>$this->repDate,
                                            'mden'=>$get('mden'),'daen'=>$get('daen'),'raseed'=>$get('raseed')],

                                    ), 'filename.pdf', self::ret_spatie_header());

                                }),

                            Action::make('excl')
                                ->label('Excel')
                                ->button()
                                ->color('success')
                                ->action(function (Get $get){
                                    return Excel::download(new SuppTranExl(Supplier::find($this->cust_id)->name,$this->repDate,
                                        $this->getTableQueryForExport()->get(),$get('mden'),$get('daen'),$get('raseed')),'cust_tran.xlsx');
                                })
                        ])->verticalAlignment(VerticalAlignment::End),
                    ])
                    ->columns(7)
            ]);
    }

    public function getTableRecordKey(Model|array $record): string
    {
        return $record->idd;
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Supp_tran2::
                where('supplier_id',$this->cust_id)
                    ->where('repDate','>=',$this->repDate);
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
            ->recordActions([
                Action::make('عرض')
                    ->visible(function (Model $record) {return $record->rec_who->value==8;})
                    ->modalHeading(false)
                    ->action(fn (Supp_tran2 $record) =>  $record->idd)
                    ->modalSubmitActionLabel('طباعة')
                    ->modalSubmitAction(
                        fn (Action $action,Supp_tran2 $record) =>
                        $action->color('blue')
                            ->icon('heroicon-o-printer')
                            ->url(function () use($record){
                                $this->order_no=$record->id;
                                return route('pdfbuy', ['id' => $record->id]);
                            })
                    )
                    ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
                    ->modalContent(fn (Supp_tran2 $record): View => view(
                        'filament.market.pages.reports.views.view-buy-tran',
                        ['record' => Buy::with('Buy_tran')->where('id',$record->id)->first()],
                    ))
                    ->icon('heroicon-o-eye')
                    ->iconButton(),
            ])
            ->columns([
                TextColumn::make('repDate')
                    ->sortable()
                    ->searchable()
                    ->label('التاريخ'),
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('الرقم الألي'),

                TextColumn::make('rec_who')
                    ->sortable()
                    ->searchable()
                    ->label('البيان'),
              TextColumn::make('price_type_name')
                ->sortable()
                ->searchable()
                ->label('طريقة الدفع'),

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
                TextColumn::make('notes')
                    ->label('ملاحظات')
            ])
            ->defaultSort('created_at')
            ->striped();
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
