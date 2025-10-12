<?php

namespace App\Livewire\Reports;


use App\Livewire\Forms\MainForm;
use App\Livewire\Forms\OverForm;
use App\Livewire\Forms\TarForm;
use App\Livewire\Forms\TransForm;
use App\Livewire\Traits\PublicTrait;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Main;

use App\Models\Main_arc;
use App\Models\Overkst;
use App\Models\Overkst_arc;
use App\Models\Taj;
use App\Models\Tran;
use App\Models\Trans_arc;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;


use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;

use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;
use Livewire\Component;



class   MainInfo extends Component implements HasInfolists,HasForms,HasTable,HasActions
{
  use InteractsWithInfolists,InteractsWithForms,InteractsWithTable,InteractsWithActions;

  use PublicTrait;

  public $main_id;
  public $mainId;


  public Main $mainRec;
  public $montahy=false;
  public MainForm $mainForm;
  public TransForm $transForm;
  public OverForm $overForm;
  public $showArc=false;

  public function mount()
  {
      $this->mainId=Main::min('id');
      $this->main_id=$this->mainId;
      $this->mainRec=Main::find($this->mainId);
      $this->montahy=$this->mainRec->raseed<=0;
      $this->form->fill([]);
  }

  public function Do(Get $get,Set $set)
  {
      if (!Main::find($this->mainId))
      Notification::make()
          ->title('هذا الرقم غير مخزون')->color('danger')->danger()->send();
  }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(Tran::class)
            ->schema([
                Select::make('main_id')
                    ->columnSpan(2)
                    ->relationship('Main', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Main $record) => "{$record->Customer->name} {$record->acc}")
                    ->live()
                    ->searchable()
                    ->preload()
                    ->Label('بحث')
                    ->afterStateUpdated(function (Get $get,Set $set) {

                        if (Main::where('id',$get('main_id'))->exists())
                        {
                            $this->main_id=$get('main_id');

                            $this->mainRec=Main::find($this->main_id);
                            $this->dispatch('Take_Main_Id',main_id: $this->main_id);
                            $set('mainId',$this->main_id);
                            $this->montahy=$this->mainRec->raseed<=0;

                            $this->showArc=(Main_arc::where('customer_id', $this->mainRec->customer_id)->exists());
                        }

                        else $this->main_id=null;
                    }),
                TextInput::make('mainId')
                    ->label('رقم العقد')
                    ->columnSpan(1)
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
                ])->columnSpan(3)->verticalAlignment(VerticalAlignment::End),
            ])
            ->columns(6)  ;
    }

    public function mainInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->mainRec)
            ->schema([

                TextEntry::make('Customer.name')
                    ->label(new HtmlString('<div class="text-primary-400 text-lg font-extrabold">اسم الزبون</div>'))
                    ->color('info')->size(TextSize::Large)
                    ->weight(FontWeight::ExtraBold)
                    ->columnSpan(3),
                TextEntry::make('Bank.BankName')
                    ->label('المصرف')
                    ->columnSpan(3)
                    ->color('info'),
                TextEntry::make('acc')->label('رقم الحساب')
                    ->columnSpan(2)
                    ->color('info'),
                TextEntry::make('id')
                    ->columnSpan(2)
                    ->label(new HtmlString('<div class="text-primary-400 text-lg">رقم العقد</div>'))
                    ->color('info')
                    ->weight(FontWeight::ExtraBold)
                    ->size(TextSize::Large),
                TextEntry::make('sul_begin')->label('تاريخ العقد')->columnSpan(2),
                TextEntry::make('sul')->label('قيمة العقد')->color('info')->columnSpan(2),

                TextEntry::make('kst_count')->label('عدد الأقساط')->columnSpan(2),
                TextEntry::make('kst')->label('القسط')->columnSpan(2),
                TextEntry::make('pay')->label('المدفوع')->columnSpan(2),
                TextEntry::make('raseed')->label('المتبقي')->color('danger')
                    ->weight(FontWeight::ExtraBold)->columnSpan(2),


                TextEntry::make('LastKsm')->label('تاريخ اخر خصم')
                    ->visible(fn(): bool=>filled($this->mainRec->LastKsm))->columnSpan(2),

                TextEntry::make('over_count')->label('اقساط بالفائض')->color('danger')
                    ->weight(FontWeight::ExtraBold)
                    ->visible(fn(): bool=>$this->mainRec->overkstable()->exists())->columnSpan(2),
                TextEntry::make('over_kst')->label('قيمتها')
                    ->visible(fn(): bool=>$this->mainRec->overkstable()->exists())->columnSpan(2),
                TextEntry::make('tar_count')->label('اقساط مرجعة')->color('danger')
                    ->weight(FontWeight::ExtraBold)
                    ->visible(fn(): bool=>$this->mainRec->tarkst()->exists())->columnSpan(2),
                TextEntry::make('tar_kst')->label('قيمتها')
                    ->visible(fn(): bool=>$this->mainRec->tarkst()->exists())->columnSpan(2),
                TextEntry::make('notes')->label('ملاحظات')
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

  public function render()
    {

        return view('livewire.reports.main-info');
    }
}
