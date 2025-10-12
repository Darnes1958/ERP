<?php

namespace App\Filament\Resources\FromexcelResource\Pages;

use App\Filament\Resources\FromexcelResource;
use App\Imports\FromExcelImport;
use App\Livewire\Traits\AksatTrait;
use App\Models\Dateofexcel;
use App\Models\Fromexcel;
use App\Models\Hafitha;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Taj;
use App\Models\Tran;
use App\Models\User;
use EightyNine\ExcelImport\ExcelImportAction;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ListFromexcels extends ListRecords
{
    use AksatTrait;
    protected static string $resource = FromexcelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Tarseed')
                ->action(function (){

                    $mains=Main::query()
                        ->get();
                         foreach ($mains as $main){
                             $this->SortTrans($main->id);
                             $this->SortKstDate($main->id);
                             self::MainTarseed2($main->id);
                         }


                    Notification::make('ok')->title('Ok')->success()->send();
                })
                ->visible(true)
             ->label('tarseed'),
            Actions\Action::make('Convert')
                ->action(function (){

                    $mains=Main::query()
                        ->where('raseed','<',0)
                        ->get();
                    foreach ($mains as $main){
                        $trans=Tran::query()
                            ->where('main_id',$main->id)
                            ->orderBy('ser','desc')
                            ->get();
                        $raseed=abs($main->raseed);
                        foreach ($trans as $tran){
                            if ($raseed > $tran->ksm){

                                $raseed=round($raseed-$tran->ksm,3);
                                self::StoreOver2($main,$tran->ksm_date,$tran->ksm);
                                $tran->delete();
                            }
                            else {
                                self::StoreOver2($main,$tran->ksm_date,$raseed);
                                $tran->ksm-=$raseed;
                                $tran->save();
                                $raseed=0;
                            }
                            if ($raseed==0) break;
                        }

                        //                      $main->pay=Tran::where('main_id',$main->id)->sum('ksm');
                        //                      $main->save();
                        //  $this->SortKstDate($main->id);
                        self::MainTarseed2($main->id);
                    }
                    Notification::make('ok')->title('Ok')->success()->send();
                })
                ->visible(true)
                ->label('over kst'),
            Actions\Action::make('Do')
                ->color('success')
                ->schema([
                    Select::make('taj')
                        ->label('المصرف التجميعي')
                        ->options(Taj::all()->pluck('TajName','id'))
                        ->searchable()
                        ->preload()
                        ->default(2)
                        ->required(),
                    TextInput::make('headerrow')
                        ->default(10)
                        ->label('رقم سطر العنوان')
                        ->required(),
                ])
                ->action(function (array $data){
                    Fromexcel::truncate();
                    User::find(Auth::id())->update(['headerrow'=>$data['headerrow'],'taj'=>$data['taj']]);

                }),

            ExcelImportAction::make()
                ->slideOver()
                ->color('danger')
                ->use(FromExcelImport::class),
            Actions\Action::make('check')
                ->action(function (array $data){
                    $beginDate=Fromexcel::min('ksm_date');
                    $endDate=Fromexcel::max('ksm_date');
                    $res=Dateofexcel::where('taj_id',Auth::user()->taj)
                        ->whereBetween('date_begin',[$beginDate,$endDate])->first();
                    if ($res){
                        Fromexcel::truncate();
                        Notification::make()
                            ->title('يوجد تداخل في تاريخ الحافظة مع حافظة سابقة لنفس المصرف ')
                            ->send();
                        return false;

                    }

                    Dateofexcel::create([
                            'taj_id'=>Auth::user()->taj,
                            'date_begin'=>Fromexcel::min('ksm_date'),
                            'date_end'=>Fromexcel::max('ksm_date'),
                        ]
                    );
                }),
            Actions\Action::make('link')
             ->label('ربط بالعقود')
            ->action(function (){
                DB::connection(Auth()->user()->company)->beginTransaction();
                try {
                    $fromexcel=Fromexcel::query()->where('haf_id',null)->get();
                    if ($fromexcel->count()>0){
                        $haf=Hafitha::create([
                            'taj_id'=>Auth::user()->taj,
                            'from_date'=>$fromexcel->min('ksm_date'),
                            'to_date'=>$fromexcel->max('ksm_date'),
                        ]);
                    } else return;

                    foreach ($fromexcel as $item){
                        $mains=Main::where('taj_id',$item->taj_id)->where('acc',$item->acc)->get();

                        if ($mains->count()>0){
                            if ($mains->count()==1)
                                $main=$mains->first();
                            else
                            {
                                $main=$mains->where('kst',$item->ksm)->first();
                                if (!$main) $main=$mains->first();
                            }
                            $type=$this->Fill_From_Excel($main->id,$item->ksm,$item->ksm_date,$haf->id,$item->id);
                            $item->main_id=$main->id;
                            $item->main_name=$main->Customer->name;
                            $item->kst_type=$type;
                            $item->save();
                        } else
                        {
                            $mainArc=Main_arc::where('taj_id',$item->taj_id)->where('acc',$item->acc)->first();
                            if ($mainArc)
                            {
                                self::StoreOver2($mainArc,$item->ksm_date,$item->ksm,$haf->id);
                                $item->kst_type='over_arc';
                                $item->save();


                            } else
                            {
                                $this->StoreWrong($item->taj_id,$item->acc,$item->name,$item->ksm_date,$item->ksm,$haf->id);
                                $item->kst_type='wrong';
                                $item->save();
                            }

                        }
                    }

                    Fromexcel::where('haf_id',null)->update(['haf_id'=>$haf->id]);

                    $haf->tot=Fromexcel::where('haf_id',$haf->id)->sum('ksm');
                    $haf->morahel=Fromexcel::where('haf_id',$haf->id)->where('kst_type','normal')->sum('ksm');
                    $haf->over_kst=Fromexcel::where('haf_id',$haf->id)->where('kst_type','over')->sum('ksm');
                    $haf->over_kst_arc=Fromexcel::where('haf_id',$haf->id)->where('kst_type','over_arc')->sum('ksm');
                    $haf->wrong_kst=Fromexcel::where('haf_id',$haf->id)->where('kst_type','wrong')->sum('ksm');
                    $haf->half=Fromexcel::where('haf_id',$haf->id)->where('kst_type','half')->sum('ksm');
                    $haf->save();

                    DB::connection(Auth()->user()->company)->commit();
                    Notification::make()
                        ->title('تم الترحيل بنجاح')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->success()
                        ->send();
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

            }),


        ];
    }

}
