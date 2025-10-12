<?php

namespace App\Livewire\Forms;


use App\Livewire\Traits\MainTrait;
use App\Models\Main;
use App\Models\Tran;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Form;
use App\Livewire\Traits\AksatTrait;

class MainView extends Form
{
  use MainTrait;
  use AksatTrait;
    #[Rule('required')]

    public $id = '';

    #[Rule('required')]
    public $customer_id = '';

    #[Rule('required')]
    public $bank_id = '';

    #[Rule('required')]
    public $acc = '';

    #[Rule('required')]

    public $sul_begin = '';


    public $sul_end = '';

    #[Rule('required')]
    public $sul = '';

    #[Rule('required')]
    public $kst_count = '';

    #[Rule('required')]
    public $kst = '';

    #[Rule('required')]
    public $pay = 0;
    #[Rule('required')]
    public $raseed = 0;
    public $notes = '';

    public $LastKsm;
    public $NextKst;
    public $Late;
    public $sell_id;
    public $LastUpd;
    public $kst_baky;
    public $user_id;
  public $last_cont;
  public $over_count;
  public $over_kst;
  public $tar_count;
  public $tar_kst;

    public $BankName;
    public $CusName;

    public function SetMainView($main_id){
        $rec=Main::where('id',$main_id)->first();
        $this->id=$main_id;
        $this->customer_id=$rec->customer_id;
        $this->bank_id=$rec->bank_id;
        $this->acc=$rec->acc;
        $this->sul_begin=$rec->sul_begin;
        $this->sul_end=$rec->sul_end;
        $this->sul=$rec->sul;
        $this->kst_count=$rec->kst_count;
        $this->kst=$rec->kst;
        $this->pay=$rec->pay;
        $this->raseed=$rec->raseed;
        $this->notes=$rec->notes;
        $this->user_id=$rec->user_id;
        $this->CusName=$rec->customer->name;
        $this->BankName=$rec->bank->BankName;
        $this->LastKsm=$rec->LastKsm;
        $this->NextKst=$rec->NextKst;
        $this->Late=$rec->Late;
        $this->LastUpd=$rec->LastUpd;
        $this->sell_id=$rec->sell_id;
        $this->kst_baky=$rec->kst_baky;
        $this->last_cont=$rec->last_cont;
        $this->over_count=$rec->over_count;
        $this->over_kst=$rec->over_kst;
        $this->tar_count=$rec->tar_count;
        $this->tar_kst=$rec->tar_kst;
    }
    public function Tarseed(){
        $pay=Tran::where('main_id',$this->id)->sum('ksm');
        $count=Tran::where('main_id',$this->id)->count();
        $lastksm=Tran::where('main_id',$this->id)->max('ksm_date');
        $nextkst=Tran::where('main_id',$this->id)->max('kst_date');
        $main=Main::where('id',$this->id)->first();
        $this->LastUpd=now();

        if ($nextkst)
         $this->NextKst= date('Y-m-d', strtotime($nextkst . "+1 month"));
        else $this->NextKst=$this->setMonth($main->sul_begin);

        Main::where('id',$this->id)->
        update([
            'pay'=>$pay,
            'raseed'=>$main->sul-$pay,
            'LastKsm'=>$lastksm,
            'LastUpd'=>$this->LastUpd,
            'NextKst'=>$this->NextKst,
            'Late'=>$this->RetLate($this->id,$main->kst_count,$this->NextKst),
            'Kst_baky'=>$main->kst_count-$count,
        ]);


        $this->pay=$pay;
        $this->raseed=$main->sul-$pay;

    }

}
