<?php

namespace App\Livewire\Forms;

use App\Models\Sell;
use App\Models\Sell_tran_work;
use App\Models\Sell_work;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class SellForm extends Form
{
  public $id = '';
  public $order_date = '' ;
  public $customer_id = 1 ;
  public $price_type_id = 1 ;
  public $place_id = 1 ;
  public $tot = 0 ;
  public $pay = 0 ;
  public $pay_after = 0 ;
  public $morajeh = 0 ;
  public $baky = 0 ;
  public $not_pay_date = null ;
  public $notes = '' ;
  public $single = 1;
  public $user_id = '' ;

  public function mountForm(){
    $this->reset();
    $this->id=Auth::id();
    $this->order_date=date('Y-m-d');
    $this->user_id=Auth::id();
  }

  public function loadForm($id){
    $rec=Sell::find($id);
    $this->id=$id;
    $this->order_date = $rec->order_date;
    $this->customer_id = $rec->customer_id;
    $this->price_type_id = $rec->price_type_id;
    $this->place_id = $rec->place_id;
    $this->tot = $rec->tot;
    $this->pay = $rec->pay;
    $this->pay_after = $rec->pay_after;
    $this->morajeh = $rec->morajeh;
    $this->baky = $rec->baky;
    $this->not_pay_date = $rec->not_pay_date;
    $this->notes = $rec->notes;
    $this->single = $rec->single;
    $this->user_id = $rec->user_id;
  }

  public function fillForm($id){
      $tot = Sell_tran_work::where('sell_id', $id)->sum('sub_tot');
      $baky = $tot - Sell_work::find($id)->pay;
      Sell_work::find($id)->update([
          'tot' => $tot,
          'baky' => $baky,
      ]);
    $rec=Sell_work::find($id);
    $this->order_date = $rec->order_date;
    $this->customer_id = $rec->customer_id;
    $this->price_type_id = $rec->price_type_id;
    $this->place_id = $rec->place_id;
    $this->tot = $rec->tot;
    $this->pay = $rec->pay;
    $this->pay_after = $rec->pay_after;
    $this->morajeh = $rec->morajeh;
    $this->baky = $rec->baky;
    $this->not_pay_date = $rec->not_pay_date;
    $this->notes = $rec->notes;
    $this->single = $rec->single;
    $this->user_id = $rec->user_id;
  }

    public function copyToSave($rec){
        $this->order_date = $rec->order_date;
        $this->customer_id = $rec->customer_id;
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
