<?php

namespace App\Filament\Ins\Pages;

use App\Enums\Haf_kst_type;
use App\Filament\Tables\MainTable;
use App\Models\Hafitha;
use App\Models\HafithaTran;

use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Tran;
use App\Models\WrongName;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;

use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;


class InpHafithaTran extends Page implements HasSchemas,HasTable,HasActions
{

    use InteractsWithSchemas,InteractsWithTable,InteractsWithActions;

    protected static bool $shouldRegisterNavigation=false;

    protected string $view = 'filament.ins.pages.inp-hafitha-tran';
    protected ?string $heading='';

    public $hafitha;
    public $main;
    public $wrong_name;
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

    public function wrongAction(): Action
    {
        return
        Action::make('openWrong')
            ->fillForm(function (){
                $this->wrong_name=WrongName::where('taj_id',$this->hafitha->taj_id)->where('acc',$this->acc)->first();
                if ($this->wrong_name) return ['name'=>$this->wrong_name->name,'ksm_date'=>now()];
                else return ['ksm_date'=>now()];
            })
            ->label('بالخطأ')
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('name')->columnSpanFull()
                            ->required()
                            ->extraAttributes(['wire:keydown.enter' => "go('ksm2')"]),
                        DatePicker::make('ksm_date')
                            ->afterStateUpdated(function ($state) {
                                $this->ksm_date=$state;
                            })
                            ->required()->id('ksm_date2')->extraAttributes(['wire:keydown.enter' => "go('ksm2')"]),
                        TextInput::make('ksm')
                            ->required()
                            ->id('ksm')
                            ->numeric()->id('ksm2')
                            ->afterStateUpdated(function ($state,Get $get) {
                                $this->ksm_date=$get('ksm_date');
                                $this->ksm=$state;
                            }) ,
                    ])->columns(2)

            ])
            ->action(function (array $data): void {
                if (!$this->wrong_name)
                    $this->wrong_name=WrongName::create(['name'=>$data['name'],'acc'=>$this->acc,'taj_id'=>$this->hafitha->taj_id,]);
                $this->wrong_name->hafitha()->create([
                    'hafitha_id'=>$this->hafitha->id,
                    'ksm'=>$this->ksm,
                    'ksm_date'=>$this->ksm_date,
                    'acc'=>$this->acc,
                    'ksm_notes'=>'بالخطأ',
                    'haf_kst_type' => Haf_kst_type::بالخطأ->value ,

                ]);

            });

    }
    public function store()
    {
            $this->main->hafitha()->create([
                'hafitha_id'=>$this->hafitha->id,
                 'ksm'=>$this->ksm,
                 'ksm_date'=>$this->ksm_date,
                 'acc'=>$this->acc,
                 'ksm_notes'=>$this->ksm_notes,
                 'haf_kst_type' =>$this->haf_kst_type  ,

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
            $this->haf_kst_type=Haf_kst_type::قائم->value;
            if (!$this->main)
            {$this->main=Main_arc::where('taj_id',$this->hafitha->taj_id)->where('acc',$this->acc)->first();
             $this->haf_kst_type=Haf_kst_type::ارشيف->value;}

            if ($this->main) {
                $this->ksm=$this->main->kst;
                $this->acc=$this->main->acc;

                $this->hafithaable_id=$this->main->id;
                $this->go('ksm');
                return;
            }
            $this->mountAction('wrongAction');
        }
    }
    public function chkId()
    {
        if ($this->hafithaable_id) {
            $this->main=Main::find($this->hafithaable_id);
            $this->haf_kst_type=Haf_kst_type::قائم->value;
            if (!$this->main)
             {
                 $this->main=Main_arc::find($this->hafithaable_id);
                $this->haf_kst_type=Haf_kst_type::ارشيف->value;
             }

            if ($this->main) {
                $this->ksm=$this->main->kst;
                $this->acc=$this->main->acc;
                $this->go('ksm');
            }
        }
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
                                    ->withData('ksm',$main->kst)
                                )
                                 ->toArray();})
                         ->onItemSelected(function(SearchResult $item,Set $set){
                           $this->hafithaable_id=$item->get('id');
                           $this->mainId=$item->get('id');
                           $this->acc=$item->get('acc');
                           $this->ksm=$item->get('ksm');

                           $this->main=Main::find($item->get('id'));
                           $this->haf_kst_type=Haf_kst_type::قائم->value;
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
                         ->belowContent(function () {
                             if ($this->main)
                                 return Schema::start([
                                     Text::make(Haf_kst_type::tryFrom($this->haf_kst_type)->name)->color(Haf_kst_type::tryFrom($this->haf_kst_type)->getColor()),
                                     Text::make($this->main->name)->color('success')->weight(FontWeight::ExtraBold),
                                 ]);
                             else return null;
                         })
                         ->columnSpan(3)
                         ->afterStateUpdated(fn($state)=>$this->hafithaable_id=$state)
                         ->extraAttributes(['wire:keydown.enter' => "chkId",])
                         ->required(),

                     ModalTableSelect::make('mainId')
                         ->hiddenLabel()

                         ->id('mainId')
                         ->relationship('Main','id')

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
                    TextColumn::make('hafithaable.name'),
                    TextColumn::make('acc')
                        ->searchable(),
                    TextColumn::make('ksm')
                        ->numeric()

                        ->summarize(Sum::make()->numeric('2','.',',')->label(' '))
                        ->sortable(),
                    TextColumn::make('ksm_date')
                        ->date('Y-m-d')
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
                ])
            ->defaultSort('updated_at','desc')
            ->recordActions([
                DeleteAction::make()->iconButton(),
            ]);
    }

}
