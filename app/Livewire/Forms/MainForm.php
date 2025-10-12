<?php

namespace App\Livewire\Forms;


use App\Models\Main;
use App\Models\Main_arc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\Rule;
use Livewire\Form;


class MainForm extends Form
{


    #[Rule('required')]

    public $id = '';

    #[Rule('required')]
    public $customer_id = '';

    #[Rule('required')]
    public $bank_id = '';
    #[Rule('required')]
    public $taj_id = '';

    #[Rule('required',message: 'يجب ادخال رقم الحساب')]
    public $acc = '';

    #[Rule('required',message: 'يجب ادخال تاريخ ')]
    #[Rule('date',message: 'يجب ادخال تاريخ صحيح')]
    public $sul_begin = '';


    public $sul_end = '';

    #[Rule('required',message: 'يجب ادخال قيمة العقد')]
    #[Rule('numeric',message: 'يجب ادخال قيمة العقد')]
    #[Rule('min:1',message: 'يجب ادخال قيمة العقد صحيحة')]
    public $sul = '';

    #[Rule('required',message: ' يجب ادخال عدد الاقساط')]
    #[Rule('numeric',message:   'يجب ادخال رقم')]
    #[Rule('min:1',message: 'يجب ادخال عدد الاقساط صحيح')]
    public $kst_count = '';

    #[Rule('required',message: ' يجب ادخال قيمة القسط')]
    #[Rule('numeric',message:   'يجب ادخال رقم')]
    #[Rule('min:1',message: 'يجب ادخال قيمة القسط صحيحة')]

    public $kst = '';

    public $pay = 0;

    public $raseed = 0;

    public $LastKsm;
    public $NextKst;
    public $Late=0;
    public $sell_id=1;
    public $LastUpd;
    public $kst_baky;
    public $last_cont;
    public $over_count=0;
    public $over_kst=0;
    public $tar_count=0;
    public $tar_kst=0;
    public $notes = '';

    public $user_id;


    public function FillRec($rec){

      $this->customer_id=$rec->customer_id;
      $this->bank_id=$rec->bank_id;
      $this->taj_id=$rec->taj_id;
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
    public function SetMain($id){
        $rec=Main::where('id',$id)->first();
        $this->id=$id;
        $this->FillRec($rec);
    }
  public function SetMain_arc($id){
    $rec=Main_arc::where('id',$id)->first();
    $this->id=$id;
    $this->FillRec($rec);
  }

}
