<?php

namespace App\Livewire\Forms;

use App\Models\Buy_tran;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\Sell_tran_work;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SellTranForm extends Form
{

  public $sell_id = '' ;
  public $sell_id2 = '' ;
  public $item_id = '' ;
  public $barcode_id = '' ;
  public $q1 = 0 ;
  public $q2 = 0 ;
  public $price1 = 0 ;
  public $price2 = 0 ;
  public $profit = 0;
  public $sub_tot = 0 ;
  public $user_id = '' ;
  public $place_id=0;

  public function loadForm($sell_id,$sell_id2,$rec){
    $this->sell_id=$sell_id;
    $this->sell_id2=$sell_id2;
    $this->item_id = $rec['item_id'];
    $this->barcode_id = $rec['barcode_id'];
    $this->q1 = $rec['q1'];
    $this->q2 = $rec['q2'];
    $this->price1 = $rec['price1'];
    $this->price2 = $rec['price2'];
    $this->sub_tot = $this->q1*$this->price1+$this->q2*$this->price2;
    $this->profit=0;
    $this->user_id = Auth::id();
  }
    public function copyToSave($sell_id,$sell_id2,$rec){
      $this->sell_id=$sell_id;
      $this->sell_id2=$sell_id2;
      $this->item_id = $rec->item_id;
      $this->barcode_id = $rec->barcode_id;
      $this->q1 = $rec->q1;
      $this->q2 = $rec->q2;
      $this->price1 = $rec->price1;
      $this->price2 = $rec->price2;
      $this->sub_tot = $rec->sub_tot;
      $this->profit = $rec->profit;
      $this->user_id = $rec->user_id;
    }
}
