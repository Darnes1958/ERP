<?php

namespace App\Filament\ins\Pages\Reports;

use App\Filament\Tables\MainTable;

use App\Livewire\Traits\PublicTrait;
use App\Models\Bank;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Overkst;

use App\Models\Taj;
use App\Models\Tran;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\ModalTableSelect;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;

use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;

class mainRep extends Page implements HasSchemas,HasTable
{
    use InteractsWithSchemas,InteractsWithTable;

    use PublicTrait;

    public $main_id;
    public $mainId;


  public Main $mainRec;
  public $montahy=false;

  public $showArc=false;

  protected ?string $heading = '';
  public function getBreadcrumbs(): array
  {
    return [""];
  }
  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->can('تقرير عن عقد');
  }
  public static function getNavigationBadge(): ?string
  {
    return Main::count();
  }

    protected string $view = 'filament.ins.pages.reports.main-rep';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    public static ?string $title = 'تقرير عن عقد';
    protected static string | \UnitEnum | null $navigationGroup='تقارير';
    protected static ?int $navigationSort=1;

    public function mount()
    {
        if (!$this->main_id) {
            $this->mainId=Main::min('id');
            $this->main_id=$this->mainId;
            $this->mainRec=Main::find($this->mainId);
            $this->montahy=$this->mainRec->raseed<=0;
            $this->form->fill(['main_id'=>$this->main_id,'mainId'=>$this->mainId]);

        }
    }

    public function Do()
    {
        if (!Main::find($this->mainId))
            Notification::make()
                ->title('هذا الرقم غير مخزون')->color('danger')->danger()->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(Tran::class)
            ->components([
                ModalTableSelect::make('main_id')
                    ->label('بحث عن العقد')
                    ->relationship('Main','id')
                    ->live()
                    ->selectAction(
                        fn (Action $action) => $action
                            ->label('إضغط هنا لبحث')
                            ->modalHeading('البحث عن عقد')
                            ->modalSubmitActionLabel('تأكيد الإختيار'),
                    )
                    ->tableConfiguration(MainTable::class)
                    ->getOptionLabelFromRecordUsing(fn (Main $record) => "{$record->Customer->name} ({$record->acc})")
                    ->afterStateUpdated(function ($state,Set $set) {

                        if (Main::where('id',$state)->exists())
                        {
                            $this->main_id=$state;
                            $set('mainId',$this->main_id);
                            $this->mainRec=Main::find($state);
                            $this->dispatch('Take_Main_Id',main_id: $this->main_id);

                            $this->montahy=$this->mainRec->raseed<=0;

                            $this->showArc=(Main_arc::where('customer_id', $this->mainRec->customer_id)->exists());
                        }

                        else $this->main_id=null;
                    })
                    ->columnSpan(3),

                TextInput::make('mainId')
                    ->label('رقم العقد')
                    ->live(onBlur: true)
                    ->extraAttributes(['wire:keydown.enter' => 'Do',])
                    ->afterStateUpdated(function ($state,Set $set){
                        if (Main::where('id',$state)->exists()){
                            $this->main_id=$state;
                            $this->mainId=$this->main_id;
                            $this->mainRec=Main::find($this->main_id);

                            $set('mainId',$state);
                            $this->dispatch('Take_Main_Id',main_id: $this->main_id);
                            $this->montahy=$this->mainRec->raseed<=0;
                            $this->showArc=(Main_arc::where('customer_id', $this->mainRec->customer_id)->exists());
                        }


                    }),
                Actions::make([
                    Action::make('print')
                        ->label('طباعة')
                        ->button()
                        ->color('info')
                        ->icon('heroicon-m-printer')
                        ->color('info')
                        ->action(function (){
                            $res=Main::find($this->mainId);
                            $res2=Tran::where('main_id',$res->id)->orderBy('ser')->get();
                            return Response::download(self::ret_spatie($res,
                                'PrnView.Pdf-main',[
                                    'res2'=>$res2,
                                ]
                            ), 'filename.pdf', self::ret_spatie_header());
                        })
                    ,
                    Action::make('print2')
                        ->label('طباعة نموذج العقد')
                        ->button()
                        ->color('info')
                        ->icon('heroicon-m-printer')
                        ->color('info')
                        ->action(function (){
                            $res=Main::find($this->main_id);



                            $mindate=$res->sul_begin;
                            $mdate=Carbon::parse($mindate) ;
                            $mmdate=$mdate->month.'-'.$mdate->year;

                            $maxdate=$res->sul_end;
                            $xdate=Carbon::parse($maxdate) ;
                            $xxdate=$xdate->month.'-'.$xdate->year;

                            $taj=Taj::find(Bank::find($res->bank_id)->taj_id);

                            $BankName=$taj->TajName;
                            $TajAcc=$taj->TajAcc;
                            return Response::download(self::ret_spatie($res,
                                'PrnView.Pdf-main-Cont',[
                                    'res' => $res,  'TajAcc' => $TajAcc,'BankName'=>$BankName,'mindate'=>$mmdate,'maxdate'=>$xxdate,]
                            ), 'filename.pdf', self::ret_spatie_header());
                        })
                    ,
                    Action::make('retrieve')
                        ->color('primary')
                        ->visible(fn():bool=>$this->montahy)
                        ->requiresConfirmation()
                        ->action(function (){
                            $this->DoArc();
                        })
                        ->label('نقل الأرشيف')
                ])->columnSpan(2)->verticalAlignment(VerticalAlignment::End),
            ])
            ->columns(6)  ;
    }

    public function mainInfolist(Schema $schema): Schema
    {
        return $schema

            ->components([
                TextEntry::make('Customer.name')
                    ->state(function (){
                        return $this->mainRec->Customer->name;
                    })

                    ->label(new HtmlString('<div class="text-primary-400 text-lg font-extrabold">اسم الزبون</div>'))
                    ->color('info')->size(TextSize::Large)
                    ->weight(FontWeight::ExtraBold)
                    ->columnSpan(3),
                TextEntry::make('Bank.BankName')
                    ->state(function (){
                        return $this->mainRec->Bank->BankName;
                    })

                    ->label('المصرف')
                    ->columnSpan(3)
                    ->color('info'),
                TextEntry::make('acc')->label('رقم الحساب')
                    ->state(function (){
                        return $this->mainRec->acc;
                    })

                    ->columnSpan(2)
                    ->color('info'),
                TextEntry::make('id')
                    ->state(function (){
                        return $this->mainRec->id;
                    })

                    ->columnSpan(2)
                    ->label(new HtmlString('<div class="text-primary-400 text-lg">رقم العقد</div>'))
                    ->color('info')
                    ->weight(FontWeight::ExtraBold)
                    ->size(TextSize::Large),
                TextEntry::make('sul_begin')                    ->state(function (){
                    return $this->mainRec->sul_begin;
                })
                    ->label('تاريخ العقد')->columnSpan(2),
                TextEntry::make('sul')                    ->state(function (){
                    return $this->mainRec->sul;
                })
                    ->label('قيمة العقد')->color('info')->columnSpan(2),

                TextEntry::make('kst_count')                    ->state(function (){
                    return $this->mainRec->kst_count;
                })
                    ->label('عدد الأقساط')->columnSpan(2),
                TextEntry::make('kst')                    ->state(function (){
                    return $this->mainRec->kst;
                })
                    ->label('القسط')->columnSpan(2),
                TextEntry::make('pay')                    ->state(function (){
                    return $this->mainRec->pay;
                })
                    ->label('المدفوع')->columnSpan(2),
                TextEntry::make('raseed')                    ->state(function (){
                    return $this->mainRec->raseed;
                })
                    ->label('المتبقي')->color('danger')
                    ->weight(FontWeight::ExtraBold)->columnSpan(2),


                TextEntry::make('LastKsm')                    ->state(function (){
                    return $this->mainRec->LastKsm;
                })
                    ->label('تاريخ اخر خصم')
                    ->visible(fn(): bool=>filled($this->mainRec->LastKsm))->columnSpan(2),

                TextEntry::make('over_count')                    ->state(function (){
                    return $this->mainRec->over_count;
                })
                    ->label('اقساط بالفائض')->color('danger')
                    ->weight(FontWeight::ExtraBold)
                    ->visible(fn(): bool=>$this->mainRec->overkstable()->exists())->columnSpan(2),
                TextEntry::make('over_kst')                    ->state(function (){
                    return $this->mainRec->over_kst;
                })
                    ->label('قيمتها')
                    ->visible(fn(): bool=>$this->mainRec->overkstable()->exists())->columnSpan(2),
                TextEntry::make('tar_count')                    ->state(function (){
                    return $this->mainRec->tar_count;
                })
                    ->label('اقساط مرجعة')->color('danger')
                    ->weight(FontWeight::ExtraBold)
                    ->visible(fn(): bool=>$this->mainRec->tarkst()->exists())->columnSpan(2),
                TextEntry::make('tar_kst')                    ->state(function (){
                    return $this->mainRec->tar_kst;
                })
                    ->label('قيمتها')
                    ->visible(fn(): bool=>$this->mainRec->tarkst()->exists())->columnSpan(2),
                TextEntry::make('notes')                    ->state(function (){
                    return $this->mainRec->notes;
                })
                    ->label('ملاحظات')
                    ->visible(fn(): bool=>filled($this->mainRec->notes))->columnSpanFull(),


            ])->columns(8);
    }

    public function table(Table $table):Table
    {
        return $table
            ->query(function ()  {
                $tran=Tran::where('main_id',$this->main_id);
                return  $tran;
            })
            ->columns([
                TextColumn::make('ser')

                    ->color('primary')
                    ->sortable()
                    ->label('ت'),
                TextColumn::make('kst_date')->sortable()
                    ->toggleable()
                    ->label('تاريخ القسط'),
                TextColumn::make('ksm_date')->sortable()
                    ->label('تاريخ الخصم'),
                TextColumn::make('ksm')
                    ->label('الخصم'),
                TextColumn::make('ksm_type_id')
                    ->size(TextSize::ExtraSmall)
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->label('طريقة الدفع'),
                TextColumn::make('ksm_notes')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->size(TextSize::ExtraSmall)
                    ->label('ملاحظات'),
            ])
            ->striped();
    }

    public function DoArc(){
        DB::connection(Auth()->user()->company)->beginTransaction();
        try {
            $record=Main::find($this->main_id);
            $oldRecord= $record;
            $newRecord = $oldRecord->replicate();

            $newRecord->setTable('main_arcs');
            $newRecord->id=$record->id;

            $newRecord->save();
            Overkst::where('overkstable_type','App\Models\Main')
                ->where('overkstable_id',$record->id)
                ->update(['overkstable_type'=>'App\Models\Main_arc']);

            Tran::query()
                ->where('main_id', $record->id)
                ->each(function ($oldTran) {
                    $newTran = $oldTran->replicate();
                    $newTran->setTable('trans_arcs');
                    $newTran->save();
                    $oldTran->delete();
                });
            $record->delete();
            $this->mainRec=Main::first();
            $this->mainId=$this->mainRec->id;
            $this->main_id=$this->mainId;
            $this->dispatch('Take_Main_Id',main_id: $this->main_id);
            $this->montahy=$this->mainRec->raseed<=0;

            Notification::make()
                ->title('تم النقل بنجاح')
                ->success()
                ->send();

            DB::connection(Auth()->user()->company)->commit();
        } catch (\Exception $e) {
            info($e);
            DB::connection(Auth()->user()->company)->rollback();
        }


    }



}
