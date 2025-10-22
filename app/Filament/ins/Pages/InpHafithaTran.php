<?php

namespace App\Filament\Ins\Pages;

use App\Filament\Tables\MainTable;
use App\Models\Hafitha;
use App\Models\HafithaTran;

use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Tran;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Panel;

use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class InpHafithaTran extends Page implements HasSchemas,HasTable
{

    use InteractsWithSchemas,InteractsWithTable;

    protected static bool $shouldRegisterNavigation=false;

    protected string $view = 'filament.ins.pages.inp-hafitha-tran';

    public $hafitha;
    public $main;
    public $ksm,$ksm_date,$acc,$hafithaable_id;
    public static function getSlug(?Panel $panel = null): string
    {
        return 'inp-hafitha-tran/{record}'; // {record} can be any name
    }
    public function go($who){
        $this->dispatch('gotoitem', test: $who);
    }

    public function mount($record) :void{

        $this->hafitha = Hafitha::find($record);
        $this->ksm_date=now();
        $this->form->fill(['acc'=>$this->acc,'hafithaable_id'=>$this->hafithaable_id,'ksm'=>$this->ksm,'ksm_date'=>$this->ksm_date]);

    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(Tran::class)
            ->components([
                Section::make()
                 ->schema([
                     TextInput::make('acc')
                         ->columnSpan(3)
                         ->required(),
                     TextInput::make('hafithaable_id')
                         ->extraAttributes(['wire:keydown.enter' => "\$dispatch('gotoitem', { test: 'ksm' })",])
                         ->columnSpan(3)
                         ->required(),

                     ModalTableSelect::make('mainId')
                         ->hiddenLabel()
                         ->id('mainId')
                         ->relationship('Main','id')
                         ->live()
                         ->selectAction(
                             fn (Action $action) => $action
                                 ->label('بحث عن العقد')
                                 ->modalHeading('البحث عن عقد')
                                 ->modalSubmitActionLabel('تأكيد الإختيار'),
                         )
                         ->tableConfiguration(MainTable::class)
                         ->getOptionLabelFromRecordUsing(fn (Main $record) => "{$record->Customer->name} ({$record->acc})")
                         ->extraAttributes(['wire:change' => "\$dispatch('gotoitem', { test: 'ksm' })",])
                         ->afterStateUpdated(function ($state,Set $set) {

                            $this->main=Main::where('id',$state)->first();
                            $this->ksm=$this->main->kst;
                            $this->acc=$this->main->acc;
                            $this->hafithaable_id=$this->main->id;

                             $this->go('ksm');
                            //$set('acc',$this->main->acc);
                            //$set('ksm',$this->main->kst);
                         })
                         ->columnSpan('full'),
                     TextInput::make('ksm_notes')->columnSpanFull(),
                     DatePicker::make('ksm_date')->columnSpan(2)
                         ->required(),
                     TextInput::make('ksm')
                         ->columnSpan(2)
                         ->required()
                         ->id('ksm')
                         ->numeric(),

                 ])->columns(6),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                return HafithaTran::where('hafitha_id',$this->hafitha->id);
            })
            ->columns([
                    TextColumn::make('main_id')
                        ->searchable(),
                    TextColumn::make('hafithaable.Customer.name'),
                    TextColumn::make('acc')
                        ->searchable(),
                    TextColumn::make('kst')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('ksm_date')
                        ->date()
                        ->sortable(),
                    TextColumn::make('ksm_notes')
                        ->searchable(),
                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]

            );
    }

}
