<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Place_stock extends Model
{
  protected $connection = 'other';

protected $appends =['place_buy_cost'];

    public function getPlaceBuyCostAttribute(){
        return $this->stock1 * $this->Item->price_buy;
    }


    public function Place(){
    return $this->belongsTo(Place::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
