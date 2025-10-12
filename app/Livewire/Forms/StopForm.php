<?php

namespace App\Livewire\Forms;

use App\Models\Main;
use App\Models\Stop;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class StopForm extends Form
{
  #[Rule('required|date',message: 'يجب ادخال التاريخ')]
  public $stop_date = '';
  public $main_id = '';
  public $user_id='';

  public function SetTar(Stop $rec) {
    $this->main_id=$rec->main_id;
    $this->stop_date_date=$rec->over_date;
    $this->user_id=$rec->user_id;
  }
  public function SetStopFromMain($id,$date) {
    $this->main_id=$id;
    $this->stop_date=$date;
    $this->user_id=Auth::id();
  }
  public function Save($id,$date){
    $this->reset();
    $this->SetStopFromMain($id,$date);
    return Stop::create($this->all());
  }


}
