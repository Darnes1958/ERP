<?php

namespace App\Filament\Pages\Reports;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use App\Exports\SalaryTranExl;
use App\Exports\SuppTranExl;
use App\Livewire\Traits\AksatTrait;
use App\Models\Salary;
use App\Models\Salarytran;
use App\Models\Supplier;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class SalaryTranView extends Page implements HasTable, HasForms
{
  use InteractsWithTable,InteractsWithForms;
  use AksatTrait;
  protected static ?string $navigationLabel='حركة مرتب';
  protected static string | \UnitEnum | null $navigationGroup='مرتبات';
  protected static ?int $navigationSort=7;
  protected ?string $heading = '';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'filament.pages.reports.salary-tran-view';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مرتبات') || Auth::user()->can('تقارير مرتبات') ;
    }

    public $salary_id;

    public function mount(){
      $this->form->fill([]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('salary_id')
                    ->options(Salary::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->Label('الاسم'),
                Placeholder::make('raseed')
                 ->label('الرصيد')
                  ->content(function (Get $get) {
                    if ($get('salary_id')) {
                      $raseed=Salary::find($get('salary_id'))->raseed;
                      if ($raseed<0) return new HtmlString('<span class="text-danger-600"> '.$raseed .'</span>');
                      else return new HtmlString('<span class="text-indigo-700"> '.$raseed .'</span>');
                    }


                    return 0 ;

                  }),
                Actions::make([

                    Action::make('excl')
                        ->label('Excel')
                        ->button()
                        ->color('success')
                        ->action(function (Get $get){
                            $sal=Salary::find($this->salary_id);
                            return Excel::download(new SalaryTranExl($sal->name,$sal->raseed,
                                $this->getTableQueryForExport()->get(),'sal'),'cust_tran.xlsx');
                        })
                ])->verticalAlignment(VerticalAlignment::End),

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
            ])
          ->recordActions([
            Action::make('delete')
             ->requiresConfirmation()
             ->icon('heroicon-o-trash')
             ->iconButton()
            ->action(function (Model $record){
              $record->delete();
              $this->TarseedTrans();
            })
          ])

          ;
    }

}
