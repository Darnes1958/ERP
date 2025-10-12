<?php

namespace App\Livewire\Forms;


use App\Models\Overkst;
use App\Models\Tarkst;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TarForm extends Form
{


  #[Rule('required')]
  public $main_id = '';
  #[Rule('required|date')]
  public $tar_date = '';
  #[Rule('required')]
  public $kst = '';
  public $tar_type = '';
  public $from_id ='';
  public $haf_id = '';
  public $user_id = '';

  public function SetTar(Tarkst $rec) {

    $this->main_id=$rec->main_id;
    $this->tar_date=$rec->over_date;
    $this->kst=$rec->kst;
    $this->tar_type=$rec->tar_type;
    $this->from_id=$rec->from_id;
    $this->haf_id=$rec->haf_id;
    $this->user_id=$rec->user_id;
  }
    public function SetTarFromOver($id) {
        $rec=Overkst::where('id',$id)->first();

        $this->main_id=$rec->main_id;
        $this->tar_date=date('Y-m-d');
        $this->kst=$rec->kst;
        $this->tar_type='من الفائض';
        $this->from_id=$rec->id;
        $this->haf_id=$rec->haf_id;
        $this->user_id=Auth::id();
    }
}
