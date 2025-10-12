<?php

namespace App\Livewire\Forms;

use App\Models\Wrongkst;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class WrongForm extends Form
{
    #[Rule('required',message: 'يجب ادخال التاريخ')]
    public $wrong_date = '';
    #[Rule('required',message: 'يجب ادخال الخصم')]
    public $kst = '';
    #[Rule('required',message: 'يجب ادخال المصرف')]
    public $bank_id='';
    public $acc='';
    public $user_id='';
    public $tar_id=0;
    public $status='غير مرجع';
    public $haf_id=0;

    public function FillAny(){
      $this->wrong_date=date('Y-m-d');
      $this->kst=1;
      $this->bank_id=1;
    }

  public function store()
  {
    $this->validate();
    $this->user_id=auth()->id();
    Wrongkst::create($this->all());
    $this->reset();
  }

}
