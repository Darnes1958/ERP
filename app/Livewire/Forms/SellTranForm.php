<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\Raseed;
use App\Models\Buy_tran;

use App\Models\Buys_work;
use App\Models\Item;
use App\Models\Sell_tran;
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


  public $item_id = '' ;
  public $barcode_id = '' ;
  public $q1 = 0 ;
  public $q2 = 0 ;
  public $price1 = 0 ;
  public $price2 = 0 ;
  public $profit = 0;
  public $sub_tot = 0 ;
  public $user_id = '' ;


    public function loadForm($sell_id,$rec){
        $this->sell_id=$sell_id;

        $this->item_id = $rec['item_id'];
        $this->barcode_id = $rec['barcode_id'];
        $this->q1 = $rec['q1'];
        $this->q2 = $rec['q2'];
        $this->price1 = $rec['price1'];
        $this->price2 = $rec['price2'];
        $this->sub_tot = ($this->q1*$this->price1)+($this->q2*$this->price2);
        $this->user_id = Auth::id();

    }

    public function copyToSave($sell_id,$rec){
      $this->sell_id=$sell_id;

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
    public function prices($single,$price_type){
     $this->retPrice($this->item_id,$single,$price_type);
    }
    public function raseedplace($place_id){
      return $this->retRaseedPlace($this->item_id,$place_id);
    }
    public function raseedTwo($place_id){

      return $this->retRaseedTwo($this->item_id,$place_id);
    }
    public function quantTwo(){
     return $this->retQuant($this->item_id,$this->q1,$this->q2);
    }
    public function chkRaseed($place_id): bool{

     return ($this->raseedTwo($place_id)-$this->quantTwo())>=0;
    }
    public function chkRaseedEdit($place_id,$q1,$q2): bool{

          $raseed=$this->TwoToOne(Item::find($this->item_id)->count, $q1 ,$q2) + $this->raseedTwo($place_id);

        return $raseed-$this->quantTwo()  >= 0;
    }


    public function chkData($place_id){
      $has_two=Setting::find(Auth::user()->company)->has_two && Item::find($this->item_id)->two_unit;
      if ($this->item_id=='') return 'يجب ادخال الصنف';

      if (!$has_two && $this->q1<=0) return 'يجب ادخال الكمية';
      if ($has_two &&  $this->q2<=0 && $this->q1<=0) return 'يجب ادخال الكمية';

      if (!$this->chkRaseed($place_id)) return 'الرصيد لا يسمح !!';
      return 'ok';
    }
    public function chkDataEdit($place_id){
        $has_two=Setting::find(Auth::user()->company)->has_two && Item::find($this->item_id)->two_unit;
        if ($this->item_id=='') return 'يجب ادخال الصنف';

        if (!$has_two && $this->q1<=0) return 'يجب ادخال الكمية';
        if ($has_two &&  $this->q2<=0 && $this->q1<=0) return 'يجب ادخال الكمية';
        $res=Sell_tran::where('sell_id',$this->sell_id)->where('item_id',$this->item_id)->first();
        if ($res) {
            info($this->chkRaseedEdit($place_id,$res->q1,$res->q2));
            if (!$this->chkRaseedEdit($place_id,$res->q1,$res->q2)) return 'الرصيد لا يسمح !!';
        }

        else {
          if (!$this->chkRaseed($place_id)) return 'الرصيد لا يسمح !!';}

        return 'ok';
    }
    public function SetQuant() {
      $q=$this->retSetQuant($this->item_id,$this->q1,$this->q2);
      $this->q1=$q['q1'];
      $this->q2=$q['q2'];
      $this->sub_tot = ($this->q1*$this->price1)+($this->q2*$this->price2);
    }

    public function DoDecALl($place_id)
    {
        $this->decAll($this->sell_id,$this->item_id,$place_id,$this->q1,$this->q2);
    }
  public function DoIncALl($place_id)
  {
    $this->incAll($this->sell_id,$this->item_id,$place_id,$this->q1,$this->q2);
  }

}
