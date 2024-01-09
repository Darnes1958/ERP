<?php

namespace App\Livewire\Forms;

use App\Models\Buy;
use App\Models\Buys_work;
use App\Models\Place;
use App\Models\Price_type;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BuyForm extends Form
{

  public $order_date = '' ;
  public $supplier_id = 1 ;
  public $price_type_id = 1 ;
  public $place_id = 1 ;
  public $tot = 0 ;
  public $pay = 0 ;
  public $pay_after = 0 ;
  public $morajeh = 0 ;
  public $baky = 0 ;
  public $not_pay_date = null ;
  public $notes = '' ;
  public $user_id = '' ;

  public function mountForm(){
    $this->order_date=date('Y-m-d');
    $this->user_id=Auth::id();
  }


  public function fillForm($buy_id){
    $rec=Buys_work::find($buy_id);
    $this->order_date = $rec->order_date;
    $this->supplier_id = $rec->supplier_id;
    $this->price_type_id = $rec->price_type_id;
    $this->place_id = $rec->place_id;
    $this->tot = $rec->tot;
    $this->pay = $rec->pay;
    $this->pay_after = $rec->pay_after;
    $this->morajeh = $rec->morajeh;
    $this->baky = $rec->baky;
    $this->not_pay_date = $rec->not_pay_date;
    $this->notes = $rec->notes;
    $this->user_id = $rec->user_id;
  }

    public function copyToSave($rec){
        $this->order_date = $rec->order_date;
        $this->supplier_id = $rec->supplier_id;
        $this->price_type_id = $rec->price_type_id;
        $this->place_id = $rec->place_id;
        $this->tot = $rec->tot;
        $this->pay = $rec->pay;
        $this->pay_after = $rec->pay_after;
        $this->morajeh = $rec->morajeh;
        $this->baky = $rec->baky;
        $this->not_pay_date = $rec->not_pay_date;
        $this->notes = $rec->notes;
        $this->user_id = $rec->user_id;
    }

}
