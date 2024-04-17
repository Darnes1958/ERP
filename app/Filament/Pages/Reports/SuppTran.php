<?php

namespace App\Filament\Pages\Reports;

use App\Models\Supp_tran;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SuppTran extends Page implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='حركة مورد';
    protected static ?string $navigationGroup='زبائن وموردين';
  protected static ?int $navigationSort=7;
    protected ?string $heading="";
    protected static string $view = 'filament.pages.reports.supp-tran';

    public $cust_id;
    public $repDate;
    public $formData;

    public function mount(){
        $this->repDate=now();

        $this->myForm->fill(['repDate'=>$this->repDate,'raseed'=>0,'mden'=>0,'daen'=>0]);
    }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "myForm" => $this->makeForm()
                ->schema($this->getMyFormSchema())
                ->statePath('formData'),

        ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Supp_tran::
                where('supplier_id',$this->cust_id)
                    ->where('repDate','>=',$this->repDate);
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
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

    protected function getMyFormSchema(): array
    {
        return [
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
                                $set('raseed',number_format($daen-$mden, 2, '.', ','));


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
                                $set('raseed',number_format($daen-$mden, 2, '.', ','));


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
                ])
                ->columns(6)
        ];
    }

}
