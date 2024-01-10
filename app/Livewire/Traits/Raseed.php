<?php

namespace App\Livewire\Traits;

use App\Models\Item;
use App\Models\Place_stock;

trait Raseed {
    public function retRaseed($item_id,$place_id){
        $count=Item::find($item_id)->count;
        $res=Place_stock::where('item_is',$item_id)
        ->where('place_id',$place_id)->first();
        if ($res)
         return $res->stock2+($res->stock1*$count);
        else return 0;
    }
    public function retQuant($item_id,$q1,$q2){
        return $q1+($q1*Item::find($item_id)->count);
    }

}
