<?php

namespace App\Livewire\Traits;

use App\Enums\TwoUnit;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Buy_tran_work;
use App\Models\Buys_work;
use App\Models\BuySell;
use App\Models\Item;
use App\Models\Place_stock;

use App\Models\Price_buy;
use App\Models\Price_sell;
use App\Models\Price_type;
use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;
use App\Models\Sell_tran;
use App\Models\Setting;
use App\Services\FifoReconcileService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Raseed {
    public function tarseedBuys_work($buy_id){
      $buy=Buys_work::find($buy_id);
      $tot=Buy_tran_work::where('buy_id',$buy_id)->sum('sub_input');
      $baky=$tot-$this->buy->pay;
      $this->buy->tot=$tot;
      $this->buy->baky=$baky;
      $this->buy->save();
    }
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
    public function setPriceSell($item_id,$price_type_id,$single,$price1,$price2){
        if ($single) {$type1='price1';$type2='price2';}
        else {$type1='pricej1';$type2='pricej2';}

        if ($price_type_id==1) Item::find($item_id)->update([$type1=>$price1,$type2=>$price2,]);

        $price_sell=Price_sell::where('item_id',$item_id)->where('price_type_id',$price_type_id)->first();
        if ($price_sell) $price_sell->update([$type1=>$price1,$type2=>$price2,]);
        else Price_sell::create(['item_id'=>$item_id,'price_type_id'=>$price_type_id
            ,'price1'=>$price1,'price2'=>$price2,'pricej1'=>$price1,'pricej2'=>$price2,]);


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
        $res=Item::find($item_id);
        $count=$res->count;
        $qq=$q2+($q1*$count);
        if ($res->two_unit->value==1)
         return ['q1'=>intdiv($qq,$count),'q2' => $qq % $count];
        else return ['q1'=>$q1,'q2' => 0];
    }
    public function chkRaseed($item_id,$place_id,$q1,$q2){
      $quant=$this->retSetQuant($item_id,$q1,$q2);
      return $this->retRaseedTwo($item_id,$place_id) - $this->retQuant($item_id,$quant['q1'],$quant['q2']) >=0;
    }

    public function retFifoAvailable(int $item_id): float
    {
        $service = app(FifoReconcileService::class);

        return $service->theoreticalFifoRemaining($item_id);
    }

    public function syncFifoItem(int $item_id, ?int $place_id = null): array
    {
        return app(FifoReconcileService::class)->syncItem($item_id, $place_id);
    }

    public function syncFifoItems(iterable $item_ids, ?int $place_id = null): array
    {
        $service = app(FifoReconcileService::class);
        $results = [];
        foreach ($item_ids as $item_id) {
            $results[$item_id] = $service->syncItem((int) $item_id, $place_id);
        }

        return $results;
    }

    public function decAll($sell_tran_id,$sell_id,$item_id,$place_id,$q1,$q2){
        $item=Item::find($item_id);
        $count=$item->count;
        $quant = ($item->two_unit->value == 1)
            ? $q2 + ($q1 * $count)
            : $q1;

        if ($item->two_unit->value==1) {
            $quantItem = ($item->stock2 + ($item->stock1 * $count)) - $quant;
            $item->stock1 = intdiv($quantItem, $count);
            $item->stock2 = $quantItem % $count;
        } else {
            $item->stock1 -= $q1;
        }

        $item->save();

        $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
        if (! $place) {
            throw new \RuntimeException("رصيد المكان غير موجود للصنف رقم {$item_id}");
        }

        if ($item->two_unit->value==1) {
            $quantPlace = ($place->stock2 + ($place->stock1 * $count)) - $quant;
            $place->stock1 = intdiv($quantPlace, $count);
            $place->stock2 = $quantPlace % $count;
        } else {
            $place->stock1 -= $q1;
        }
        $place->save();

        $result = $this->syncFifoItem($item_id, $place_id);
        $this->notifyFifoOpeningIfCreated($item_id, $result);

        return $result;
    }

    protected function notifyFifoOpeningIfCreated(int $item_id, array $syncResult): void
    {
        if (empty($syncResult['opening']['created'])) {
            return;
        }

        $item = Item::find($item_id);
        $qty = $syncResult['opening']['quantity'];
        $buyId = $syncResult['opening']['buy_id'];

        Notification::make()
            ->title('رصيد افتتاحي FIFO')
            ->body('الصنف ('.($item?->name ?? $item_id)."): تمت إضافة كمية {$qty} في فاتورة شراء رقم {$buyId}")
            ->warning()
            ->send();
    }
    public function incAll($sell_id,$item_id,$place_id,$q1,$q2, ?int $sell_tran_id = null){

    $item=Item::find($item_id);
    $count=$item->count;
    $two_unit=$item->two_unit;

    if ($two_unit->value==1){
        $quant=$q2+($q1*$count);
        $quantItem=($item->stock2+($item->stock1*$count)) + $quant;
        $item->stock1=intdiv($quantItem,$count);
        $item->stock2=$quantItem%$count;
    } else $item->stock1+=$q1;
    $item->save();

    $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
    if (! $place) {
        throw new \RuntimeException("رصيد المكان غير موجود للصنف رقم {$item_id}");
    }

    if ($two_unit->value==1) {
        $quantPlace = ($place->stock2 + ($place->stock1 * $count)) + $quant;
        $place->stock1 = intdiv($quantPlace, $count);
        $place->stock2 = $quantPlace % $count;
    } else $place->stock1+=$q1;
    $place->save();
  }
    public function incAllBuy($item_id,$place_id,$q1,$price_type,$price_input){

        $item=Item::find($item_id);
        $item->stock1+=$q1;
        if ($price_type==1)   $item->price_buy=$price_input;
        $item->save();

        $price_buy=Price_buy::where('price_type_id',$price_type)
                 ->where('item_id',$item_id)->first();
        if ($price_buy) {
            $price_buy->price=$price_input;
            $price_buy->save();
        }else
            Price_buy::create([
                'price_type_id' => $price_type,
                'item_id' => $item_id,
                'price' => $price_input,
            ]);


        $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();

        if ($place) {

            $place->stock1+=$q1;
            $place->save();
        }
        else Place_stock::create([
           'place_id'=>$place_id,
            'item_id'=>$item_id,
           'stock1'=>$q1,
           'stock2'=>0,
        ]);

        $this->syncFifoItem($item_id, $place_id);
    }
    public function decAllBuy($item_id,$place_id,$q1){

        $item=Item::find($item_id);
        $item->stock1-=$q1;
        $item->save();

        $place=Place_stock::where('place_id',$place_id)->where('item_id',$item_id)->first();
        $place->stock1-=$q1;

        $place->save();

        $this->syncFifoItem($item_id, $place_id);
    }
   public function OneToTwo($count,$quant){
       return ['q1'=>intdiv($quant,$count),'q2' => $quant % $count];
   }
   public function TwoToOne($count,$q1,$q2){
        return $q2+($q1*$count);
    }
   public function decQs($sell_tran_id,$sell_id,$item,$count,$quant){
      $buyTran=Buy_tran::where('item_id',$item)
          ->where('qs1', '>', 0)
         ->orderBy('created_at','asc')
         ->get();
      $tank=0;
      $sell_tran=Sell_tran::find($sell_tran_id);
      $profit=0;
      foreach ($buyTran as $tran) {
        $qs=$tran->qs1;

        if ( $qs > ($quant-$tank)) $decQuant=$quant-$tank;
        else $decQuant=$qs;

        $tran->qs1 = $qs - $decQuant;
        $tran->save();

        BuySell::create([
            'buy_id' => $tran->buy_id,
            'sell_id' => $sell_id,
            'sell_tran_id' => $sell_tran_id,
            'item_id' => $item,
            'q1' => $decQuant,
            'q2' => 0,
        ]);
        $sub_input = $tran->price_input * $decQuant;
        $sub_tot = $sell_tran->price1 * $decQuant;

          $profit+=$sub_tot-$sub_input;
          $tank+=$decQuant;
          if ($tank==$quant) break;

      }

      if ($tank < $quant) {
          $itemName = Item::find($item)?->name ?? (string) $item;
          throw FifoStockException::forItem($itemName, $quant, $tank);
      }

      Sell_tran::find($sell_tran_id)->update(['profit'=>$profit]);
   }
    public function decQs2($sell_tran_id,$sell_id,$item,$count,$quant){
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
        $two_unit=Item::find($item)->two_unit;
        foreach ($buyTran as $tran) {

            if ($two_unit->value==1)
                $qs=$this->TwoToOne($count,$tran->qs1,$tran->qs2);
            else $qs=$tran->qs1;

            if ( $qs > ($quant-$tank)) $decQuant=$quant-$tank;
            else $decQuant=$qs;

            if ($two_unit->value==1)  {
                $qs=$this->OneToTwo($count,$qs-$decQuant) ;
                $tran->qs1=$qs['q1'];
                $tran->qs2=$qs['q2'];

            }else {
                $qs =  $qs - $decQuant;
                $tran->qs1 = $qs;
            }
            $tran->save();

            if ($two_unit->value==1) {
                $decQ = $this->OneToTwo($count, $decQuant);
                BuySell::create([
                    'buy_id' => $tran->buy_id,
                    'sell_id' => $sell_id,
                    'item_id' => $item,
                    'q1' => $decQ['q1'],
                    'q2' => $decQ['q2'],
                ]);
                $sub_input=($tran->price_input*$decQ['q1']) + (($tran->price_input/$count)*$decQ['q2']);
                $sub_tot=($sell_tran->price1*$decQ['q1']) + ($sell_tran->price2*$decQ['q2']);
            } else {
                BuySell::create([
                    'buy_id' => $tran->buy_id,
                    'sell_id' => $sell_id,
                    'item_id' => $item,
                    'q1' => $decQuant,
                    'q2' => 0,
                ]);
                $sub_input=($tran->price_input*$decQuant) ;
                $sub_tot=($sell_tran->price1*$decQuant);
            }


            $profit+=$sub_tot-$sub_input;
            $tank+=$decQuant;
            if ($tank==$quant) break;

        }
//        Sell_tran::find($sell_tran_id)->update(['profit'=>$profit]);
    }
    public static function decQsInv($item,$quant){
        $buyTran=Buy_tran::where('item_id',$item)
            ->Where(function (Builder $query) {
                $query->where('qs1', '>',0);
            })
            ->orderBy('created_at','asc')
            ->get();
        $tank=0;
        foreach ($buyTran as $tran) {

            if ( $tran->qs1 > ($quant-$tank)) $decQuant=$quant-$tank;
            else $decQuant=$tran->qs1;

            $tran->qs1 -= $decQuant;
            $tran->save();

            $tank+=$decQuant;
            if ($tank==$quant) break;

        }

    }
    public static function incQsInv($item,$quant){
        $buyTran=Buy_tran::where('item_id',$item)
            ->Where(function (Builder $query) {
                $query->where('qs1', 0)
                      ->orWhereColumn('qs1','!=','q1');
            })
            ->orderBy('created_at','desc')
            ->get();
        $tank=0;
        foreach ($buyTran as $tran) {
            if (($quant-$tank)>$tran->q1-$tran->qs1) $incQuant=$tran->q1-$tran->qs1;
            else $incQuant=$quant-$tank;

            $tran->qs1 += $incQuant;
            $tran->save();

            $tank+=$incQuant;
            if ($tank==$quant) break;
        }
    }
   public function incQs($sell_id,$item,$count, ?int $sell_tran_id = null){

   $query = BuySell::where('sell_id', $sell_id)->where('item_id', $item);
   if ($sell_tran_id !== null) {
       $query->where('sell_tran_id', $sell_tran_id);
   }
   $buysell = $query->get();

   if ($buysell->isEmpty() && $sell_tran_id !== null) {
       $buysell = BuySell::where('sell_id', $sell_id)
           ->where('item_id', $item)
           ->whereNull('sell_tran_id')
           ->get();
   }

    foreach ($buysell as $tran) {
      $q = $tran->q1;

      $Buy=Buy_tran::where('buy_id',$tran->buy_id)
                ->where('item_id',$item)->first();

      if ($Buy) {
          $Buy->qs1 = $q + $Buy->qs1;
          $Buy->save();
      }
    }

    $deleteQuery = BuySell::where('sell_id', $sell_id)->where('item_id', $item);
    if ($sell_tran_id !== null) {
        $deleteQuery->where(function ($q) use ($sell_tran_id) {
            $q->where('sell_tran_id', $sell_tran_id)->orWhereNull('sell_tran_id');
        });
    }
    $deleteQuery->delete();
  }
    public function TarseedTrans(){
        $res=Salary::all();
        foreach ($res as $item)
            Salary::find($item->id)->update([
                'raseed'=>
                    Salarytran::where('salary_id',$item->id)->where('tran_type','سحب')->sum('val')+
                    Salarytran::where('salary_id',$item->id)->where('tran_type','خصم')->sum('val')-
                    Salarytran::where('salary_id',$item->id)->where('tran_type','مرتب')->sum('val')-
                    Salarytran::where('salary_id',$item->id)->where('tran_type','اضافة')->sum('val')

            ]);
    }
    public function TarseedRents(){
        $res=Rent::all();
        foreach ($res as $item)
            Rent::find($item->id)->update([
                'raseed'=>
                    Renttran::where('rent_id',$item->id)->where('tran_type','سحب')->sum('val')-
                    Renttran::where('rent_id',$item->id)->where('tran_type','إيجار')->sum('val')
                ,
            ]);
    }



}
