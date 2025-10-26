<?php

namespace App\Filament\Ins\Pages;

use App\Enums\Haf_kst_type;
use App\Filament\Tables\MainTable;
use App\Models\Hafitha;
use App\Models\HafithaTran;

use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Tran;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;

use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class InpHafithaTran extends Page implements HasSchemas,HasTable
{

    use InteractsWithSchemas,InteractsWithTable;

    protected static bool $shouldRegisterNavigation=false;

    protected string $view = 'filament.ins.pages.inp-hafitha-tran';
    protected ?string $heading='';

    public $hafitha;
    public $main;
    public $wrong;
    public $ksm,$ksm_date,$acc,$hafithaable_id,$haf_kst_type,$ksm_notes;
    public $mainId;

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
        $this->form->fill(['acc'=>$this->acc,'hafithaable_id'=>$this->hafithaable_id,
            'ksm'=>$this->ksm,'ksm_date'=>$this->ksm_date,
            'mainId'=>$this->mainId,'ksm_notes'=>$this->ksm_notes,]);



    }

    public function store()
    {

            $this->main->hafitha()->create([
                'hafitha_id'=>$this->hafitha->id,
                 'ksm'=>$this->ksm,
                 'ksm_date'=>$this->ksm_date,
                 'acc'=>$this->acc,
                 'ksm_notes'=>$this->ksm_notes,
                 'haf_kst_type' =>1  ,

            ]);


      $this->acc=null;
      $this->mainId=null;
      $this->hafithaable_id=null;
      $this->ksm=null;
      $this->ksm_notes=null;
      $this->main=null;
        $this->form->fill(['acc'=>$this->acc,'hafithaable_id'=>$this->hafithaable_id,
            'ksm'=>$this->ksm,'ksm_date'=>$this->ksm_date,
            'mainId'=>$this->mainId,'ksm_notes'=>$this->ksm_notes,]);
      $this->go('acc');

    }

    public function chkAcc()
    {
        if ($this->acc) {

            $this->main=Main::where('taj_id',$this->hafitha->taj_id)->where('acc',$this->acc)->first();
            if (!$this->main)
            $this->main=Main_arc::where('taj_id',$this->hafitha->taj_id)->where('acc',$this->acc)->first();

            if ($this->main) {
                $this->ksm=$this->main->kst;
                $this->acc=$this->main->acc;
                $this->hafithaable_id=$this->main->id;
                $this->go('ksm');
                return;
            }




        }
    }
    public function chkMainId(Get $get)
    {
      if (!$get('hafithaable_id')) {return false;}

      $this->main=Main::find($get('hafithaable_id'))->first();

      if (!$this->main) return false;

      return true;
    }
    public function chkMainArcId(Get $get)
    {
        if (!$get('hafithaable_id')) {return false;}
        $this->main=Main_arc::find($get('hafithaable_id'))->first();
        if (!$this->main) return false;
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(Tran::class)
            ->components([
                Section::make()
                 ->schema([
                     TextEntry::make('hafitha_id')->color('info')
                      ->state($this->hafitha->id),
                     TextEntry::make('created_at')
                     ->state($this->hafitha->created_at)->date('ال'.'D d-m-Y'),
                     TextEntry::make('taj_name')
                         ->columnSpanFull()

                         ->color('primary')
                         ->weight(FontWeight::ExtraBold)
                         ->size(TextSize::Large)
                     ->state($this->hafitha->Taj->TajName),
                 ])->columns(2),
                Section::make()
                 ->schema([
                     SearchableInput::make('acc')
                         ->searchUsing(function(string $search){
                            return Main::query()
                                ->join('customers','customers.id','=','mains.customer_id',)
                                ->where('acc', 'like', "%$search%")
                                ->orWhere('customers.name', 'like', "%$search%")
                                ->limit(15)
                                ->select('mains.*','customers.name')
                                ->get()
                                ->map(fn(Main $main) => SearchResult::make($main->acc, "[$main->name]  $main->acc")
                                    ->withData('id', $main->id)
                                    ->withData('acc',$main->acc)
                                    ->withData('sul', $main->sul)
                                    ->withData('pay', $main->pay)
                                    ->withData('raseed', $main->raseed)
                                )
                                 ->toArray();})
                         ->onItemSelected(function(SearchResult $item,Set $set){
                           $this->hafithaable_id=$item->get('id');
                           $this->mainId=$item->get('id');
                           $this->acc=$item->get('acc');

                           $this->main=Main::find($item->get('id'));
                           $this->dispatch('gotoitem', test: 'ksm');
                       })
                         ->live()
                         ->autofocus()
                         ->columnSpan(3)
                         ->afterStateUpdated(fn($state)=>$this->acc=$state)
                         ->extraAttributes(['wire:keydown.enter' => "chkAcc",])
                         ->id('acc')
                         ->required(),
                     TextInput::make('hafithaable_id')
                         ->extraAttributes(['wire:keydown.enter' => "chkMainId",])
                         ->columnSpan(3)
                         ->extraAttributes(['wire:input.change' => "go('ksm')",])
                         ->required(),

                     ModalTableSelect::make('mainId')
                         ->hiddenLabel()
                         ->id('mainId')
                         ->relationship('Main','id')
                         ->live()
                         ->selectAction(
                             fn (Action $action) => $action
                                 ->label('بحث متقدم')
                                 ->modalHeading('البحث عن عقد')
                                 ->modalSubmitActionLabel('تأكيد الإختيار'),
                         )
                         ->tableConfiguration(MainTable::class)
                         ->getOptionLabelFromRecordUsing(fn (Main $record) => "{$record->Customer->name} ({$record->acc})")
                         ->afterStateUpdated(function ($state,Set $set) {
                            $this->main=Main::where('id',$state)->first();
                            $this->ksm=$this->main->kst;
                            $this->acc=$this->main->acc;
                            $this->hafithaable_id=$this->main->id;
                            $this->go('ksm');
                         })
                         ->columnSpan('full'),
                     TextEntry::make('sul')->columnSpan(2)
                         ->state(fn()=> $this->main ? $this->main->sul : null)
                         ->visible(fn()=>$this->main)
                         ->color('info'),
                     TextEntry::make('pay')->columnSpan(2)->visible(fn()=>$this->main)
                         ->state(fn()=> $this->main ? $this->main->pay : null)
                         ->color('info'),
                     TextEntry::make('raseed')->columnSpan(2)->visible(fn()=>$this->main)
                         ->state(fn()=> $this->main ? $this->main->raseed : null)
                         ->color('danger'),
                     TextInput::make('ksm_notes')
                         ->afterStateUpdated(function ($state,Set $set) {
                             $this->ksm_notes=$state;
                         })->columnSpanFull(),
                     DatePicker::make('ksm_date')->columnSpan(2)
                         ->afterStateUpdated(function ($state,Set $set) {
                             $this->ksm_date=$state;
                         })
                         ->required(),
                     TextInput::make('ksm')
                         ->columnSpan(2)
                         ->required()
                         ->id('ksm')
                         ->numeric()
                         ->afterStateUpdated(function ($state,Get $get) {
                             $this->ksm_date=$get('ksm_date');
                             $this->ksm=$state;
                         })
                         ->extraAttributes(['wire:keydown.enter' => "store"]),

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
                    TextColumn::make('hafithaable_id')
                        ->searchable(),
                    TextColumn::make('hafithaable.Customer.name'),
                    TextColumn::make('acc')
                        ->searchable(),
                    TextColumn::make('ksm')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('ksm_date')
                        ->date()
                        ->sortable(),
                    TextColumn::make('haf_kst_type'),
                    TextColumn::make('ksm_notes')
                        ->toggleable()
                        ->toggledHiddenByDefault()
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
