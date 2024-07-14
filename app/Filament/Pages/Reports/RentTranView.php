<?php

namespace App\Filament\Pages\Reports;

use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RentTranView extends Page implements HasTable, HasForms
{
    use InteractsWithTable,InteractsWithForms;
    protected static ?string $navigationLabel='حركة إيجار';
    protected static ?string $navigationGroup='إيجارات';
    protected static ?int $navigationSort=2;
    protected ?string $heading = '';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('إيجارات');
    }

    public $rent_id;

    protected static string $view = 'filament.pages.reports.rent-tran-view';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('rent_id')
                    ->options(Rent::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->Label('الاسم'),
            ])->columns(4);
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function (Renttran $tran)  {
                $tran= Renttran::where('rent_id',$this->rent_id);
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
                    ->state(function (Renttran $record){
                        if ($record->kazena_id)  return $record->Kazena->name;
                        if ($record->acc_id)  return $record->Acc->name;

                    })
                    ->color(function (Renttran $record){
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
            ->actions([
                Action::make('delete')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->action(function (Model $record) {
                        $record->delete();
                    })

                ]);
    }
}
