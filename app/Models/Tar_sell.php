<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tar_sell extends Model
{
  protected $connection = 'other';


  public function Sell(){
    return $this->belongsTo(Sell::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }

  public function Sell_tran(){
    return $this->hasMany(Sell_tran::class);
  }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
