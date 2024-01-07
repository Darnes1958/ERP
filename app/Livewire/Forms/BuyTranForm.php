<?php

namespace App\Livewire\Forms;

use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BuyTranForm extends Form
{
  public $sort = '' ;
  public $buy_id = '' ;
  public $item_id = '' ;
  public $barcode_id = '' ;
  public $q1 = 0 ;
  public $q2 = 0 ;
  public $qs1 = 0 ;
  public $qs2 = 0 ;
  public $price_input = 0 ;
  public $price_avg = 0 ;
  public $sub_input = 0 ;
  public $sub_avg = 0 ;

  public $exp_date = '' ;
  public $user_id = '' ;

  public function loadForm($buy_id,$rec){
    $this->buy_id=$buy_id;
    $this->sort = Buy_tran_work::where('buy_id',$buy_id)->max('sort')+1;

    $this->item_id = $rec['item_id'];
    $this->barcode_id = $rec['barcode_id'];
    $this->q1 = $rec['q1'];

    $this->price_input = $rec['price_input'];

  //  $this->price_avg = $rec->price_avg;
   $this->sub_input = $this->q1*$this->price_input;

  //  $this->sub_avg = $rec->sub_avg;
  //  $this->tar_buy_id = $rec->tar_buy_id;
  //  $this->exp_date = $rec->exp_date;
    $this->user_id = Auth::id();

  }
}
