<?php

namespace App\Filament\Pages\Reports;

use App\Models\Salary;
use App\Models\Salarytran;
use Filament\Forms\Components\Select;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class SalaryTranView extends Page implements HasTable, HasForms
{
  use InteractsWithTable,InteractsWithForms;
  protected static ?string $navigationLabel='حركة مرتب';
  protected static ?string $navigationGroup='مرتبات';
  protected static ?int $navigationSort=7;
  protected ?string $heading = '';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.reports.salary-tran-view';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مرتبات');
    }

    public $salary_id;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('salary_id')
                    ->options(Salary::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->Label('الاسم'),
            ])->columns(4);
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function (Salarytran $tran)  {
                $tran= Salarytran::where('salary_id',$this->salary_id);
                return  $tran;
            })
            ->columns([
                TextColumn::make('tran_date')
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('tran_type')
                    ->sortable()
                    ->label('البيان'),
              TextColumn::make('pay_type')
                ->state(function (Salarytran $record){
                  if ($record->kazena_id)  return $record->Kazena->name;
                  if ($record->acc_id)  return $record->Acc->name;

                })
                ->color(function (Salarytran $record){
                  if ($record->kazena_id)  return 'success';
                  if ($record->acc_id)  return 'info';

                })
                ->label('دفعت من '),

                TextColumn::make('month')
                    ->sortable()
                    ->label('عن شهر'),
                TextColumn::make('val')
                    ->label('المبلغ'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ]);
    }

}
