<?php

namespace App\Filament\ins\Pages;

use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use App\Livewire\Forms\StopForm;
use App\Models\Main;
use App\Models\Stop;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InpStop extends Page implements HasForms,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-no-symbol';
    protected static ?string $navigationLabel='ايقاف خصم';
    protected static ?int $navigationSort = 7;
    protected ?string $heading = '';
    public static function getNavigationBadge(): ?string
    {
        return Main::where('raseed','<=',0)
            ->whereNotIn('id',function ($q) {
                $q->select('main_id')->from('Stops');
            })->count();
    }
    protected string $view = 'filament.ins.pages.inp-stop';
    public $stop_date;


    public function mount()
    {
     $this->stop_date=now();
     $this->form->fill(['stop_date'=>$this->stop_date]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('stop_date')
                    ->label('تاريخ الإيقاف')
                    ->required()
                    ->inlineLabel()
            ])->columns(4);
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function ()  {
                $main=Main::where('raseed','<=',0)
                    ->whereNotIn('id',function ($q) {
                        $q->select('main_id')->from('Stops');
                    });
                return  $main;
            })
            ->emptyStateHeading('لا توجد عقود منتهية لايقافها')
            ->columns([
                TextColumn::make('id')
                    ->label('رقم العقد')
                    ->sortable(),
                TextColumn::make('Customer.name')->sortable()->searchable()
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('acc')->sortable()->searchable()
                    ->label('رقم الحساب'),
                TextColumn::make('raseed')
                    ->label('الرصيد'),
            ])

            ->toolbarActions([

                BulkAction::make('إيقاف')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {

                        foreach ($records as  $item)
                            Stop::create(['main_id'=>$item->id,'stop_date'=>$this->stop_date,'user_id'=>auth()->id()]);

                    }),
            ])
            ->striped();
    }


}
