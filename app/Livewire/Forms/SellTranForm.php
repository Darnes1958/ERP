<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\Raseed;
use App\Models\Buy_tran;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\Item;
use App\Models\Sell_tran_work;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SellTranForm extends Form
{
 use Raseed;
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

    $this->sub_tot = ($this->q1*$this->price1)+($this->q2*$this->price2);

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
    public function raseedplace(){
      return $this->retRaseedPlace($this->item_id,$this->place_id);
    }
    public function raseedTwo(){
      return $this->retRaseedTwo($this->item_id,$this->place_id);
    }
    public function quantTwo(){
     return $this->retQuant($this->item_id,$this->q1,$this->q2);
    }
    public function chkRaseed(): bool{
     return $this->raseedTwo()-$this->quantTwo()>=0;
    }
    public function chkData(){

    if ($this->item_id=='') return 'يجب ادخال الصنف';

      if ($this->q1==null || $this->q1<=0) return 'يجب ادخال الكمية الكبري';
      if (Setting::find(Auth::user()->company)->has_two && Item::find($this->item_id)->two_unit && ($this->q2 == null || $this->q2<=0)) return 'يجب ادخال الكمية';
      if (!$this->chkRaseed()) return 'الرصيد لا يسمح !!';
      return 'ok';
    }
}
