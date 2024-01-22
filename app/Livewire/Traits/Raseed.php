<?php

namespace App\Livewire\Traits;

use App\Models\Buy_tran;
use App\Models\BuySell;
use App\Models\Item;
use App\Models\Place_stock;

use App\Models\Price_sell;
use App\Models\Price_type;
use App\Models\Sell_tran;
use Illuminate\Database\Eloquent\Builder;

trait Raseed {
    public function retPrice($item,$single,$price_type){

      $Item=Item::find($item);
      $Price_type=Price_type::find($price_type);

      if ($Price_type->inc_dec->value==0)
      {

       $rec=Price_sell::where('item_id',$item)->where('price_type_id',$price_type)->first();

       if ($rec) {
         if ($single) return ['price1'=>$rec->price1,'price2'=>$rec->price2];
         else return ['price1'=>$rec->pricej1,'price2'=>$rec->pricej2];
       } else {
         if ($single) return  ['price1'=>$Item->price1,'price2'=>$Item->price2];
         else return  ['price1'=>$Item->pricej1,'price2'=>$Item->pricej2];
       }
      }
      if ($Price_type->inc_dec->value==1)
      {
        if ($Price_type->val!=0) {
          if ($single) return [
            'price1'=>$Item->price1+$Price_type->val,
            'price2'=>$Item->price2+$Price_type->val];
          else return [
            'price1'=>$Item->pricej1+$Price_type->val,
            'price2'=>$Item->pricej2+$Price_type->val,];
        } else {
          if ($single) return  [
            'price1'=>$Item->price1+(($Price_type->rate*$Item->price1)/100),
            'price2'=>$Item->price2+(($Price_type->rate*$Item->price2)/100),];
          else return  [
            'price1'=>$Item->pricej1+(($Price_type->rate*$Item->pricej1)/100),
            'price2'=>$Item->pricej2+(($Price_type->rate*$Item->pricje2)/100),];
        }
      }
      if ($Price_type->inc_dec->value==2)
      {
        if ($Price_type->val!=0) {
          if ($single) return [
            'price1'=>$Item->price1-$Price_type->val,
            'price2'=>$Item->price2-$Price_type->val];
          else return [
            'price1'=>$Item->pricej1-$Price_type->val,
            'price2'=>$Item->pricej2-$Price_type->val,];
        } else {
          if ($single) return  [
            'price1'=>$Item->price1-(($Price_type->rate*$Item->price1)/100),
            'price2'=>$Item->price2-(($Price_type->rate*$Item->price2)/100),];
          else return  [
            'price1'=>$Item->pricej1-(($Price_type->rate*$Item->pricej1)/100),
            'price2'=>$Item->pricej2-(($Price_type->rate*$Item->pricje2)/100),];
        }
      }

    }
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



    public function decAll($sell_tran_id,$sell_id,$item_id,$place_id,$q1,$q2){
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

        $this->decQs($sell_tran_id,$sell_id,$item_id,$count,$quant);
    }
    public function incAll($sell_id,$item_id,$place_id,$q1,$q2){

    $item=Item::find($item_id);
    $count=$item->count;

    $quant=$q2+($q1*$count);

    $quantItem=($item->stock2+($item->stock1*$count)) + $quant;
    $item->stock1=intdiv($quantItem,$count);
    $item->stock2=$quantItem%$count;
    $item->save();

    $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
    $quantPlace=($place->stock2+($place->stock1*$count)) + $quant;
    $place->stock1=intdiv($quantPlace,$count);
    $place->stock2=$quantPlace%$count;
    $place->save();

    $this->incQs($sell_id,$item_id,$count);
  }
    public function incAllBuy($item_id,$place_id,$q1,$q2){

        $item=Item::find($item_id);
        $count=$item->count;
        $quant=$q2+($q1*$count);
        $quantItem=($item->stock2+($item->stock1*$count)) + $quant;
        $item->stock1=intdiv($quantItem,$count);
        $item->stock2=$quantItem%$count;
        $item->save();


        $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
        info($place);
        if ($place) {
            $quantPlace=($place->stock2+($place->stock1*$count)) + $quant;
            $place->stock1=intdiv($quantPlace,$count);
            $place->stock2=$quantPlace%$count;
            $place->save();
        }
        else Place_stock::create([
           'place_id'=>$place_id,
            'item_id'=>$item_id,
           'stock1'=>$q1,
           'stock2'=>$q2,
        ]);
    }
    public function decAllBuy($item_id,$place_id,$q1,$q2){

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
    }
   public function OneToTwo($count,$quant){
       return ['q1'=>intdiv($quant,$count),'q2' => $quant % $count];
   }
   public function TwoToOne($count,$q1,$q2){
        return $q2+($q1*$count);
    }
   public function decQs($sell_tran_id,$sell_id,$item,$count,$quant){
      $buyTran=Buy_tran::where('item_id',$item)
          ->Where(function (Builder $query) {
              $query->where('qs1', '>',0)
                    ->orwhere('qs2', '>', 0);
          })
         ->orderBy('created_at','asc')
         ->get();
      $tank=0;
      $sell_tran=Sell_tran::find($sell_tran_id);
      $profit=0;
      foreach ($buyTran as $tran) {
        $qs=$this->TwoToOne($count,$tran->qs1,$tran->qs2);
        if ( $qs > ($quant-$tank)) $decQuant=$quant-$tank;
          else $decQuant=$qs;

          $qs=$this->OneToTwo($count,$qs-$decQuant) ;
          $tran->qs1=$qs['q1'];
          $tran->qs2=$qs['q2'];
          $tran->save();

          $decQ=$this->OneToTwo($count,$decQuant);
          BuySell::create([
              'buy_id' => $tran->buy_id,
              'sell_id' => $sell_id,

              'item_id' => $item,
              'q1' => $decQ['q1'],
              'q2' => $decQ['q2'],
          ]);

          $sub_input=($tran->price_input*$decQ['q1']) + (($tran->price_input/$count)*$decQ['q2']);
          $sub_tot=($sell_tran->price1*$decQ['q1']) + ($sell_tran->price2*$decQ['q2']);
          $profit+=$sub_tot-$sub_input;
          $tank+=$decQuant;
          if ($tank==$quant) break;

      }
      Sell_tran::find($sell_tran_id)->update(['profit'=>$profit]);
   }
   public function incQs($sell_id,$item,$count){

   $buysell= BuySell::where('sell_id',$sell_id)
             ->where('item_id',$item)
             ->get();
    foreach ($buysell as $tran) {
      $q=$this->TwoToOne($count,$tran->q1,$tran->q2);

      $Buy=Buy_tran::where('buy_id',$tran->buy_id)
                ->where('item_id',$item)->first();

      $qs=$q+$this->TwoToOne($count,$Buy->qs1,$Buy->qs2);


        Buy_tran::where('buy_id',$tran->buy_id)
            ->where('item_id',$item)->update([
               'qs1'=>$this->OneToTwo($count,$qs)['q1'],
               'qs2'=>$this->OneToTwo($count,$qs)['q2'],
            ]);

    }
     BuySell::where('sell_id',$sell_id)
       ->where('item_id',$item)
       ->delete();
  }

}
