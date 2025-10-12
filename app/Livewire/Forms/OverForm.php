<?php

namespace App\Livewire\Forms;

use App\Models\Overkst;
use App\Models\Overkst_arc;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OverForm extends Form
{


  #[Rule('required')]
  public $main_id = '';
  #[Rule('required|date')]
  public $over_date = '';
  #[Rule('required')]
  public $kst = '';

  public $status = 'غير مرجع';
  public $tar_id =0;
  public $haf_id = 0;
  public $tran_id = 0;
  public $user_id = '' ;



  public function SetOver(Overkst $rec) {
    $this->main_id=$rec->main_id;
    $this->over_date=$rec->over_date;
    $this->kst=$rec->kst;
    $this->status=$rec->status;
    $this->tar_id=$rec->tar_id;
    $this->haf_id=$rec->haf_id;
    $this->tran_id=$rec->tran_id;
    $this->user_id=Auth::id();
  }
  public function SetOverArc(Overkst_arc $rec) {
    $this->main_id=$rec->main_id;
    $this->over_date=$rec->over_date;
    $this->kst=$rec->kst;
    $this->status=$rec->status;
    $this->tar_id=$rec->tar_id;
    $this->haf_id=$rec->haf_id;
    $this->tran_id=$rec->tran_id;
    $this->user_id=Auth::id();
  }

  public function FillAny(){
    $this->over_date=date('Y-m-d');
    $this->kst=1;
    $this->main_id=1;
  }
  public function storeArc()
  {
    $this->validate();
    $this->user_id=auth()->id();
    Overkst_arc::create($this->all());
    $this->reset();
  }

}
