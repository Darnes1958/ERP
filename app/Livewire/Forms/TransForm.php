<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\AksatTrait;
use App\Models\Main;
use App\Models\Overkst;
use App\Models\Tran;
use App\Models\Trans_arc;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Form;

class TransForm extends Form
{
use AksatTrait;
    #[Rule('required')]
    public $main_id = '';

    public $ser = '';

    #[Rule('required')]
    public $ksm = '';

    #[Rule('required|date')]
    public $ksm_date = '';

    public $kst_date ='';



    public $ksm_type_id = 2;

    public $ksm_notes = '';
    public $haf_id=0;
    public $over_id=0;
    public $baky=0;


    public $user_id = '';


    public function SetTrans(Tran $rec){
      $this->main_id=$rec->main_id;
      $this->ser=$rec->ser;
      $this->ksm=$rec->ksm;
      $this->ksm_date=$rec->ksm_date;
      $this->kst_date=$rec->kst_date;
      $this->ksm_type_id=$rec->ksm_type_id;
      $this->ksm_notes=$rec->ksm_notes;
      $this->haf_id=$rec->haf_id;
      $this->over_id=$rec->over_id;
      $this->baky=$rec->baky;
      $this->user_id=Auth::user()->id;

    }
  public function SetTransArc(Trans_arc $rec){
    $this->main_id=$rec->main_id;
    $this->ser=$rec->ser;
    $this->ksm=$rec->ksm;
    $this->ksm_date=$rec->ksm_date;
    $this->kst_date=$rec->kst_date;
    $this->ksm_type_id=$rec->ksm_type_id;
    $this->ksm_notes=$rec->ksm_notes;
    $this->haf_id=$rec->haf_id;
    $this->over_id=$rec->over_id;
    $this->baky=$rec->baky;
    $this->user_id=Auth::user()->id;

  }

    public function FillTrans($main_id){
        $this->main_id=$main_id;
        $this->ser=Tran::where('main_id',$main_id)->max('ser')+1;
        $this->kst_date=$this->getKst_date($main_id);
        $this->user_id=Auth::user()->id;
    }
    public function TransDelete($id){

      Tran::where('id',$id)->delete();
      $this->SortTrans($this->main_id);
      $this->SortKstDate($this->main_id);
    }
    public function DoOver($main_id){
        $this->StoreOver($main_id,$this->ksm_date,$this->ksm);
    }
    public function DoBaky($main_id,$raseed){

        $this->over_id=$this->StoreOver($main_id,$this->ksm_date,$this->ksm-$raseed);
        $this->baky=$this->ksm-$raseed;
        $this->ksm=$raseed;

    }
}
