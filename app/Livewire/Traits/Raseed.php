<?php

namespace App\Livewire\Traits;

use App\Models\Item;
use App\Models\Place_stock;

trait Raseed {
    public function retRaseedTwo($item_id,$place_id){
        $count=Item::find($item_id)->count;
        $res=Place_stock::where('item_id',$item_id)
        ->where('place_id',$place_id)->first();

        if ($res)
         return $res->stock2+($res->stock1*$count);
        else return 0;
    }
  public function retRaseedPlace($item_id,$place_id){

    $res=Place_stock::where('item_id',$item_id)
      ->where('place_id',$place_id)->first();
    

    if ($res)
      return ['q1'=>$res->stock1,'q2'=>$res->stock2];
    else return ['q1'=>0,'q2'=>0];
  }

    public function retQuant($item_id,$q1,$q2){
        return $q2+($q1*Item::find($item_id)->count);
    }

}
