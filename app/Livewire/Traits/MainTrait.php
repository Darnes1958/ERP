<?php
namespace App\Livewire\Traits;

use App\Livewire\Forms\MainForm;
use App\Livewire\Forms\OverForm;
use App\Livewire\Forms\TransForm;
use App\Models\Main;
use App\Models\Main_arc;
use App\Models\Overkst;
use App\Models\Overkst_arc;
use App\Models\Tran;
use App\Models\Trans_arc;
use Carbon\Carbon;
use DateTime;

trait MainTrait {



  public function EndDate($date,$card_nocount){
      return $date = date('Y-m-d', strtotime($date . "+".$card_nocount." month"));
  }

  public function RetLate($main_id,$kst_count,$nextKst){
    $toDate = Carbon::parse($nextKst);
    $fromDate = Carbon::now();

    if ($fromDate>$toDate)
      $months = $toDate->diffInMonths($fromDate);
    else $months=0;

    $count=Tran::where('main_id',$main_id)->count();
    if ($months>($kst_count-$count)) $months=$kst_count-$count;

    return intval($months);

  }
    public static function RetLate2($main_id,$kst_count,$nextKst){
        $toDate = Carbon::parse($nextKst);
        $fromDate = Carbon::now();

        if ($fromDate>$toDate)
            $months = $toDate->diffInMonths($fromDate);
        else $months=0;

        $count=Tran::where('main_id',$main_id)->count();
        if ($months>($kst_count-$count)) $months=$kst_count-$count;

        return intval($months);

    }
  public function LateChk(){
    $Main=Main::where('LastUpd','<',now())->get();
    foreach ($Main as $main)
      Main::where('id',$main->id)->
      update([
        'LastUpd'=>now(),
        'Late'=>$this->RetLate($main->id,$main->kst_count,$main->NextKst),
      ]);
  }

  public function toArc($main_id,MainForm $TmainForm,TransForm $TtransForm,OverForm $ToverForm){
      $this->MainTarseed($main_id);
      $TmainForm->reset();
      $TtransForm->reset();
      $ToverForm->reset();
      $TmainForm->SetMain($main_id);

      Main_arc::create(
          $TmainForm->all()
      );

      $res=Tran::where('main_id',$main_id)->get();

      foreach ($res as $item){
          $TtransForm->SetTrans($item);
          $TtransForm->user_id=$item->user_id;

          Trans_arc::create(
              $TtransForm->all()
          );
      }
      $res=Overkst::where('main_id',$main_id)->get();
      foreach ($res as $item){
          $ToverForm->SetOver($item);
          $ToverForm->user_id=$item->user_id;
          Overkst_arc::create(
              $ToverForm->all()
          );
      }



      Overkst::where('main_id',$main_id)->delete();
      Tran::where('main_id',$main_id)->delete();
      Main::where('id',$main_id)->delete();
  }

  public function MainTarseed($id){
    $pay=Tran::where('main_id',$id)->sum('ksm');
    $count=Tran::where('main_id',$id)->count();
    $lastksm=Tran::where('main_id',$id)->max('ksm_date');
    $nextkst=Tran::where('main_id',$id)->max('kst_date');
    $main=Main::where('id',$id)->first();
    $LastUpd=now();


    if ($nextkst)
      $NextKst= date('Y-m-d', strtotime($nextkst . "+1 month"));
    else $NextKst=$this->setMonth($main->sul_begin);

    $main=Main::find($id);
    $tar=$main->tarkst->sum('kst');
    Main::where('id',$id)->
    update([
      'pay'=>$pay,
      'raseed'=>$main->sul-$pay,
      'LastKsm'=>$lastksm,
      'LastUpd'=>$LastUpd,
      'NextKst'=>$NextKst,
      'Late'=>$this->RetLate($id,$main->kst_count,$NextKst),
      'Kst_baky'=>$main->kst_count-$count,
      'tar_kst'=>$tar,
    ]);


    $this->pay=$pay;
    $this->raseed=$main->sul-$pay;

  }
    public static function MainTarseed2($id){
        $main=Main::find($id);

        if (!$main) return false;

        $pay=Tran::where('main_id',$id)->sum('ksm');
        $count=Tran::where('main_id',$id)->count();
        $lastksm=Tran::where('main_id',$id)->max('ksm_date');
        $nextkst=Tran::where('main_id',$id)->max('kst_date');
        $main=Main::where('id',$id)->first();
        $LastUpd=now();

        if ($nextkst)
            $NextKst= date('Y-m-d', strtotime($nextkst . "+1 month"));
        else $NextKst=self::setMonth2($main->sul_begin);

        $tar=$main->tarkst->sum('kst');
        $over=$main->overkstable->sum('kst');

        Main::where('id',$id)->
        update([
            'pay'=>$pay,
            'raseed'=>$main->sul-$pay,
            'LastKsm'=>$lastksm,
            'LastUpd'=>$LastUpd,
            'NextKst'=>$NextKst,
            'Late'=>self::RetLate2($id,$main->kst_count,$NextKst),
            'Kst_baky'=>$main->kst_count-$count,
            'tar_kst'=>$tar,
            'tar_count'=>$main->tarkst->count(),
            'over_kst'=>$over,
            'over_count'=>$main->overkstable->count(),
        ]);





    }

}
