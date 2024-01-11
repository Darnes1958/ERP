<?php

namespace App\Livewire\Traits;

use App\Models\Buy_tran;
use App\Models\BuySell;
use App\Models\Item;
use App\Models\Place_stock;

use Illuminate\Database\Eloquent\Builder;

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

    public function retSetQuant($item_id,$q1,$q2){
        $count=Item::find($item_id)->count;
        $qq=$q2+($q1*$count);
        return ['q1'=>intdiv($qq,$count),'q2' => $qq % $count];
    }



    public function decAll($sell_id,$sell_id2,$item_id,$place_id,$q1,$q2){
        $item=Item::find($item_id);
        $count=$item->count;

        $quant=$q2+($q1*$count);

        $quantItem=($item->stock2+($item->stock1*$count)) - $quant;
        $item->stock1=intdiv($quantItem,$count);
        $item->stock2=$quantItem%$count;
        $item->save();

        $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
        $quantPlace=($place->stock2+($place->stock1*$count)) - $quant;
        $place->stock1=intdiv($quantPlace,$count);
        $place->stock2=$quantPlace%$count;
        $place->save();

        $this->decQs($sell_id,$sell_id2,$item_id,$count,$quant);
    }
   public function OneToTwo($count,$quant){
       return ['q1'=>intdiv($quant,$count),'q2' => $quant % $count];
   }
    public function TwoToOne($count,$q1,$q2){
        return $q2+($q1*$count);
    }
   public function decQs($sell_id,$sell_id2,$item,$count,$quant){
      $buyTran=Buy_tran::where('item_id',$item)
          ->Where(function (Builder $query) {
              $query->where('qs1', '>',0)
                    ->orwhere('qs2', '>', 0);
          })
         ->orderBy('created_at','desc')
         ->get();
      $tank=0;
      foreach ($buyTran as $tran) {
          if ( $this->TwoToOne($count,$tran->qs1,$tran->qs2) > ($quant-$tank)) $decQuant=$quant-$tank;
          else $decQuant=$this->TwoToOne($count,$tran->qs1,$tran->qs2);

          $qs=$this->OneToTwo($count,$this->TwoToOne($count,$tran->qs1,$tran->q2)-$decQuant);

          $tran->qs1-=$qs['q1'];
          $tran->qs2-=$qs['q2'];
          $tran->save();

          BuySell::create([
              'buy_id' => $tran->buy_id,
              'sell_id' => $sell_id,
              'sell_id2' => $sell_id2,
              'item_id' => $item,
              'q1' => $this->OneToTwo($count,$decQuant)['q1'],
              'q2' => $this->OneToTwo($count,$decQuant)['q2'],
          ]);

          $tank+=$decQuant;
          if ($tank==$quant) break;

      }
   }

}
