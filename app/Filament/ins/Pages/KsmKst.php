<?php

namespace App\Filament\ins\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Actions\Action;
use App\Enums\KsmType;
use App\Livewire\Traits\AksatTrait;
use App\Models\aksat\kst_trans;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Operations;
use App\Models\Tran;
use App\Models\Trans_arc;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class KsmKst extends Page implements HasTable,HasForms
{
    use InteractsWithTable,InteractsWithForms;
    use AksatTrait;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.ksm-kst';
    protected ?string $heading='';
    protected static ?string $navigationLabel='أقساط';
    protected static ?int $navigationSort=3;

    public $contData;
    public $main_id;
    public $acc;
    public $ksm_date;
    public $ksm;
    public $ksm_type_id;
    public $main;
    public $accTaken=false;
    public $message;
    public $color='myRed';
    public $is_arc=false;
    public $has_baki=false;
    public $notes;


    public function mount(): void
    {
        $this->is_arc=false;
        $this->message=null;
        $this->main_id=null;
        $this->accTaken=null;
        $this->ksm_type_id=KsmType::المصرف;
        $this->ksm_date=now();
        $this->notes=null;
        $this->fillcontForm();
        $this->go('acc');
    }

    public function fillcontForm(){
        if ($this->main_id){
            if (! $this->is_arc) $this->main=Main::find($this->main_id);
            else $this->main=Main_arc::find($this->main_id);
            $this->message=null;
            if ($this->main->raseed<=0) {$this->message='خصم قسط بالفائض';$this->color='myYellow';}
            if ($this->is_arc) {$this->message='خصم قسط بالفائض من الأرشـــــــيف';$this->color='myGreen';}

            $this->contForm->fill(['ksm_type_id'=>$this->ksm_type_id,'main_id'=>$this->main_id,'acc'=>$this->acc,
                'ksm_date'=>$this->ksm_date,'ksm'=>$this->ksm,'name'=>$this->main->Customer->name,'sul'=>$this->main->sul,
                'pay'=>$this->main->pay,'raseed'=>$this->main->raseed,'bank'=>$this->main->Taj->TajName,'ksm_notes'=>$this->notes  ]);
        }

        else
          $this->contForm->fill(['ksm_type_id'=>$this->ksm_type_id,'main_id'=>$this->main_id,'acc'=>$this->acc,
                'ksm_date'=>$this->ksm_date,'ksm'=>$this->ksm,]);

    }

    public function go($who){
        $this->dispatch('gotoitem', test: $who);
    }
    public function chkacc()
    {
        $this->message=null;
        $this->is_arc=false;
            $m=Main::where('acc',$this->acc)->get();
            if ($m->count()>0) {
                if ($m->count()==1) {
                    $this->main_id=$m[0]['id'];
                    $this->main=$m->first();
                    $this->ksm=$this->main->kst;
                } else {
                    $this->message='يوجد أكثر من عقد لهذا الحساب .. يجب اختيار رقم العقد من القائمة';
                    $this->ksm=null;
                    $this->main_id=null;
                }
                $this->accTaken=true;
                $this->go('ksm_date');
            } else {
                $m=Main_arc::where('acc',$this->acc)->first();
                if ($m){
                    $this->is_arc=true;
                    $this->main_id=$m->id;
                    $this->main=$m;
                    $this->ksm=$this->main->kst;
                    $this->go('ksm_date');
                } else
                {$this->accTaken=false;
                $this->main_id=null;}
            }
        $this->fillcontForm();
    }
    public function chkmainid()
    {

            $this->message=null;
            $this->is_arc=false;

            $this->main=Main::where('id',$this->main_id)->first();
            if ($this->main){
                $this->acc=$this->main->acc;
                $this->ksm=$this->main->kst;
                $this->accTaken=true;

                $this->has_baki=Tran::where('main_id',$this->main_id)->sum('baky')>0;
                $this->fillcontForm();
                $this->go('ksm_date');
            } else {
                $this->acc=null;
                $this->ksm=null;
                $this->accTaken=false;


            }

    }



    public function contForm(Schema $schema): Schema
    {
        return $schema
            ->model(Tran::class)
            ->components([
                Section::make()
                    ->schema([
                        Radio::make('ksm_type_id')
                            ->options(KsmType::class)
                            ->live()
                            ->afterStateUpdated(function ($state){
                                $this->ksm_type_id=$state;
                            })
                            ->inline()
                            ->inlineLabel()
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('acc')->label('رقم الحساب')
                            ->live(debounce:400)
                            ->columnSpan(3)
                            ->autocomplete('off')
                            ->datalist(function (?string $state , TextInput $component,?Model $record ,
                                                         $modelsearch='\App\Models\Main' , $fieldsearch='acc') {
                                $options =[];
                                if($state!=null  and Str::length($state)>=3){
                                    $options= $modelsearch::whereRaw($fieldsearch.
                                        ' like \'%'.$state.'%\'')
                                        ->limit(20)
                                        ->pluck('acc')
                                        ->toarray();
                                }
                                return $options;
                            })
                            ->afterStateUpdated(function ($state){
                                $this->acc=$state;
                                $this->main_id=null;
                            })
                            ->extraAttributes([
                                'wire:keydown.enter'=>'chkacc',
                            ])
                            ->id('acc'),
                        Select::make('main_id')
                            ->columnSpan(3)
                            ->live()
                            ->relationship('Main','name',modifyQueryUsing: fn ($query) =>
                            $query->when($this->accTaken && $this->acc,function ($q){
                                $q->where('acc',$this->acc);
                            }),)

                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->id} {$record->Customer->name} {$record->sul} {$record->kst}")
                            ->afterStateUpdated(function ($state){
                                $this->main_id=null;
                                $this->main_id=$state;

                                $this->chkmainid();
                            })

                            ->searchable()
                            ->preload()
                            ->id('main_id')
                            ->label('رقم العقد'),

                        Text::make('notes')
                            ->content(function (){
                                return new HtmlString('<span class="'.$this->color.' ">'.$this->message.'</span>');
                            })
                            ->hidden(fn(): bool=>$this->message==null)
                            ->live()
                            ->columnSpan('full'),


                        Section::make()
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->readOnly()
                                    ->columnSpan(3)
                                    ->hiddenLabel(),
                                TextInput::make('bank')
                                    ->readOnly()
                                    ->columnSpan(3)
                                    ->hiddenLabel(),
                                TextInput::make('sul')
                                    ->readOnly()
                                    ->columnSpan(2)
                                    ->label('قيمة العقد'),
                                TextInput::make('pay')
                                    ->readOnly()
                                    ->columnSpan(2)
                                    ->label('المسدد'),
                                TextInput::make('raseed')
                                    ->readOnly()
                                    ->columnSpan(2)
                                    ->label('المتبقي'),



                            ])
                            ->columns(6),
                        TextInput::make('ksm_notes')
                            ->extraAttributes(['wire:keydown.enter'=>'$dispatch("gotoitem", {test: "ksm_date"})'])
                            ->afterStateUpdated(function ($state){$this->notes=$state;})
                            ->columnSpanFull()
                            ->label('ملاحظات'),
                        DatePicker::make('ksm_date')
                            ->label('التاريخ')
                            ->live()
                            ->afterStateUpdated(function ($state){
                                $this->ksm_date=$state;
                            })
                            ->columnSpan(3)
                            ->required()
                            ->validationMessages([
                                'required' => 'يجب ادخال التاريخ بشكل صحيح',
                            ])
                            ->extraAttributes(['wire:keydown.enter'=>'$dispatch("gotoitem", {test: "ksm"})'])
                            ->id('ksm_date'),
                        TextInput::make('ksm')
                            ->label('القسط')
                            ->columnSpan(3)
                            ->validationMessages([
                                'required' => 'يجب ادخال قيمة القسط',
                            ])
                            ->afterStateUpdated(function ($state){$this->ksm=$state;})
                            ->numeric()
                            ->required()
                            ->extraAttributes(['wire:keydown.enter'=>'store'])
                            ->id('ksm')
                    ])
                    ->columns(6)
            ])
            ->statePath('contData');
    }

    public function store(){
        if (!$this->is_arc) $this->validate(); else {if (!$this->ksm || $this->ksm<=0 || !$this->ksm_date) return; }
        if ($this->is_arc) self::StoreOver2($this->main,$this->ksm_date,$this->ksm,0);
        else {
             self::StoreKst($this->main_id,$this->ksm_date,$this->ksm,0,$this->ksm_type_id,$this->notes);
        }

        Notification::make()
            ->title('تم تحزين البانات بنجاح')
            ->success()
            ->send();

        $this->fillcontForm();
        $this->go('acc');

    }


    public  function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('لا توجد أقساط مخصومة')
                ->emptyStateDescription('لم يتم خصم أقساط بعد')
                ->defaultPaginationPageOption(12)
                ->paginationPageOptions([5,12,15,50,'all'])
                ->defaultSort('ser')
                ->query(function (){
                    if (!$this->is_arc)
                     $tran=Tran::where('main_id',$this->main_id);
                    else
                     $tran=Trans_arc::where('main_id',$this->main_id);
                    $this->has_baki=$tran->sum('baky')>0;
                    return $tran;
                })
                ->columns([
                    TextColumn::make('ser')
                        ->size(TextSize::ExtraSmall)
                        ->color('primary')
                        ->sortable()
                        ->label('ت'),
                    TextColumn::make('kst_date')
                        ->size(TextSize::ExtraSmall)
                        ->toggleable()
                        ->toggledHiddenByDefault()
                        ->sortable()
                        ->label('ت.الاستحقاق'),
                    TextColumn::make('ksm_date')
                        ->size(TextSize::ExtraSmall)
                        ->toggleable()
                        ->sortable()
                        ->label('ت.الخصم'),
                    TextColumn::make('ksm')
                        ->size(TextSize::ExtraSmall)
                        ->label('الخصم'),
                    TextColumn::make('baky')
                        ->size(TextSize::ExtraSmall)
                        ->visible(fn()=>$this->has_baki)
                        ->color('success')
                        ->label('الباقي'),
                    TextColumn::make('ksm_type_id')
                        ->size(TextSize::ExtraSmall)
                        ->toggleable()
                        ->toggledHiddenByDefault()
                        ->label('طريقة الدفع'),
                    TextColumn::make('ksm_notes')
                        ->toggleable()
                        ->size(TextSize::ExtraSmall)
                        ->label('ملاحظات'),
                ])
                ->recordActions([
                    Action::make('del')
                        ->iconButton()
                        ->visible(fn($record)=> !$this->has_baki && !$this->is_arc)
                        ->icon('heroicon-o-trash')
                        ->iconSize(IconSize::Small)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Model $record){
                           $record->delete();
                           self::MainTarseed2($this->main_id);
                           self::SortTrans2($this->main_id);
                           $this->fillcontForm();
                        }),
                    Action::make('edit')
                        ->schema([
                            Section::make([
                                Radio::make('ksm_type_id')
                                    ->options(KsmType::class)
                                    ->inline()
                                    ->inlineLabel()
                                    ->hiddenLabel()
                                    ->columnSpanFull()
                                    ->required(),
                                DatePicker::make('ksm_date')
                                    ->required()
                                    ->label('التاريح'),
                                TextInput::make('ksm')
                                    ->required()
                                    ->gt(0)
                                    ->label('القسط'),
                                TextInput::make('ksm_notes')
                                    ->columnSpan(2)
                                    ->label('ملاحظات'),
                            ]) ->columns(2)

                        ])
                        ->fillForm(fn (Tran $record): array => [
                            'ksm_date' => $record->ksm_date,'ksm'=>$record->ksm,
                            'ksm_notes'=>$record->ksm_notes,'ksm_type_id'=>$record->ksm_type_id,
                        ])
                        ->modalCancelActionLabel('عودة')
                        ->modalSubmitActionLabel('تحزين')
                        ->modalHeading('تعديل قسط')
                        ->action(function (array $data,Tran $record,){
                            $raseed=$this->main->raseed+$record->ksm;
                            if ($data['ksm']<=$raseed) {
                                $record->update(['ksm_date'=>$data['ksm_date'],'ksm'=>$data['ksm'],
                                    'ksm_notes'=>$data['ksm_notes'],'ksm_type_id'=>$data['ksm_type_id']]);
                            } else
                            {
                                $record->update(['ksm_date'=>$data['ksm_date'],'ksm'=>$raseed,
                                    'ksm_notes'=>$data['ksm_notes'],'ksm_type_id'=>$data['ksm_type_id']]);
                                self::StoreOver2($this->main,$data['ksm_date'],$data['ksm']-$raseed);
                            }
                            self::MainTarseed2($this->main_id);
                            $this->fillcontForm();

                        })
                        ->iconButton()
                        ->iconSize(IconSize::Small)
                        ->icon('heroicon-o-pencil')
                        ->visible(function (Model $record){
                            return !$this->has_baki ;
                        })
                        ->color('blue')





                ]);
    }

}
