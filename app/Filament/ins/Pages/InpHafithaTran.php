<?php

namespace App\Filament\Ins\Pages;

use App\Enums\Haf_kst_type;
use App\Filament\ins\Resources\HafithaResource;
use App\Filament\Tables\MainArcTable;
use App\Filament\Tables\MainTable;
use App\Livewire\Traits\AksatTrait;
use App\Models\Fromexcel;
use App\Models\Hafitha;
use App\Models\HafithaTran;

use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Tran;
use App\Models\WrongName;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Exception;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;


class InpHafithaTran extends Page implements HasSchemas,HasTable,HasActions
{

    use InteractsWithSchemas,InteractsWithTable,InteractsWithActions;
    use AksatTrait;

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
                    $this->wrong_name=WrongName::create([
                        'name'=>$data['name'],
                        'acc'=>$this->acc,
                        'taj_id'=>$this->hafitha->taj_id,
                        'user_id'=>Auth::id(),]);
                $this->wrong_name->hafitha()->create([
                    'hafitha_id'=>$this->hafitha->id,
                    'ksm'=>$this->ksm,
                    'ksm_date'=>$this->ksm_date,
                    'acc'=>$this->acc,
                    'ksm_notes'=>'بالخطأ',
                    'haf_kst_type' => Haf_kst_type::بالخطأ->value ,
                    'user_id'=>Auth::id(),
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
                 'user_id'=>Auth::id(),

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
            } else  Notification::make()->title('هذا الرقم غير مخزون')->danger()->send();
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
                      ->columnSpan(3)
                      ->state($this->hafitha->id),
                     TextEntry::make('created_at')
                         ->columnSpan(3)
                     ->state($this->hafitha->created_at)->date('ال'.'D d-m-Y'),
                     TextEntry::make('taj_name')
                         ->columnSpan(5)

                         ->color('primary')
                         ->weight(FontWeight::ExtraBold)
                         ->size(TextSize::Large)
                     ->state($this->hafitha->Taj->TajName),
                     Action::make('tarheel')
                      ->visible(fn()=>HafithaTran::where('hafitha_id',$this->hafitha->id)->exists())
                      ->label('ترحيل')
                         ->action(function (){
                             DB::connection(Auth()->user()->company)->beginTransaction();
                             try {
                                 $hafitha_trans=HafithaTran::query()->where('hafitha_id',$this->hafitha->id)->get();
                                 if ($hafitha_trans->count()>0){
                                     $this->hafitha->from_date=$hafitha_trans->min('ksm_date');
                                     $this->hafitha->to_date=$hafitha_trans->max('ksm_date');
                                     $this->hafitha->status=1;
                                 } else return;

                                 foreach ($hafitha_trans as $item){
                                     if ($item->haf_kst_type==Haf_kst_type::قائم)
                                         if (!Main::find($item->hafithaable_id))
                                         {
                                             Notification::make()->title('العقد رقم '.$item->hafithaable_id.' غير موجود فالعقود القائمة .. يرجي المراجعة')
                                                 ->send()->danger();
                                             DB::connection(Auth()->user()->company)->rollback();
                                             return;
                                             break;

                                         }
                                     if ($item->haf_kst_type==Haf_kst_type::ارشيف)
                                         if (!Main_arc::find($item->hafithaable_id))
                                         {
                                             Notification::make()->title('العقد رقم '.$item->hafithaable_id.' غير موجود فالارشيف .. يرجي المراجعة')
                                                 ->send()->danger();
                                             DB::connection(Auth()->user()->company)->rollback();
                                             return;
                                             break;

                                         }

                                     $type=$this->Fill_From_Tran($item);
                                     $item->haf_kst_type=$type;
                                     $item->save();

                                 }



                                 $this->hafitha->tot=$hafitha_trans->sum('ksm');
                                 $this->hafitha->morahel=$hafitha_trans->where('haf_kst_type',1)->sum('ksm');
                                 $this->hafitha->over_kst_arc=$hafitha_trans->where('haf_kst_type',2)->sum('ksm');
                                 $this->hafitha->over_kst=$hafitha_trans->where('haf_kst_type',3)->sum('ksm');
                                 $this->hafitha->half=$hafitha_trans->where('haf_kst_type',4)->sum('ksm');
                                 $this->hafitha->wrong_kst=$hafitha_trans->where('haf_kst_type',5)->sum('ksm');

                                 $this->hafitha->status=1;
                                 $this->hafitha->save();

                                 DB::connection(Auth()->user()->company)->commit();
                                 Notification::make()
                                     ->title('تم الترحيل بنجاح')
                                     ->color('success')
                                     ->icon('heroicon-o-check-circle')
                                     ->success()
                                     ->send();
                                 redirect()->to(HafithaResource::getUrl('index'));
                             }
                             catch (Exception $e) {
                                 Notification::make()
                                     ->title('حدث خطأ !!')
                                     ->color('danger')
                                     ->icon('heroicon-o-x-circle')
                                     ->danger()
                                     ->send();
                                 info($e);
                                 DB::connection(Auth()->user()->company)->rollback();
                             }

                         })
                 ])->columns(6),
                Section::make()
                 ->schema([
                     SearchableInput::make('acc')
                         ->placeholder('بحث برقم الحساب او الاسم')
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
                         ->placeholder('او رقم العقد ثم انتر')
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
                            $this->haf_kst_type=1;
                            $this->go('ksm');
                         })
                         ->columnSpan(3),
                     ModalTableSelect::make('mainId2')
                         ->hiddenLabel()

                         ->id('mainId2')
                         ->relationship('Main_arc','id')

                         ->selectAction(
                             fn (Action $action) => $action
                                 ->label('بحث متقدم فالأرشيف')
                                 ->modalHeading('البحث عن عقد lمن الأرشيف')
                                 ->modalSubmitActionLabel('تأكيد الإختيار'),
                         )
                         ->tableConfiguration(MainArcTable::class)
                         ->getOptionLabelFromRecordUsing(fn (Main_arc $record) => "{$record->Customer->name} ({$record->acc})")
                         ->afterStateUpdated(function ($state,Set $set) {
                             $this->main=Main_arc::where('id',$state)->first();
                             $this->ksm=$this->main->kst;
                             $this->acc=$this->main->acc;
                             $this->hafithaable_id=$this->main->id;
                             $this->haf_kst_type=2;
                             $this->go('ksm');
                         })
                         ->columnSpan(3),
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
                     DatePicker::make('ksm_date')->columnSpan(3)
                         ->afterStateUpdated(function ($state,Set $set) {
                             $this->ksm_date=$state;
                         })
                         ->required(),
                     TextInput::make('ksm')
                         ->columnSpan(3)
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
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('hafithaable.name')->searchable(),
                    TextColumn::make('acc')
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('ksm')
                        ->numeric()
                        ->summarize(Sum::make()->numeric('2','.',',')->label(' ')),
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
            ->filters([
                SelectFilter::make('haf_kst_type')
                 ->options(Haf_kst_type::class)
                 ->searchable()
                 ->preload()
            ])
            ->recordActions([
                DeleteAction::make()->iconButton(),
            ]);
    }

}
