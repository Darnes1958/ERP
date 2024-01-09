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

class BuyFormEdit extends Form
{
  public $id = '';
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
  public $Price_name = '';
  public $Place_name = '';
  public $Supplier_name = '';


  public function mountForEdit(){
    $this->supplier_id='';
    $this->price_type_id='';
    $this->place_id='';
  }


    public function loadFromBuy($buy_id){
        $rec=Buy::find($buy_id);
        $this->id = $rec->id;
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
        $this->Place_name=Place::find($this->place_id)->name;
        $this->Price_name=Price_type::find($this->price_type_id)->name;
        $this->Supplier_name=Supplier::find($this->supplier_id)->name;
    }


}
