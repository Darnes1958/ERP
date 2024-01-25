<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Buys_work extends Model
{
  protected $connection = 'other';

  public function worksupplier(){
    return $this->belongsTo(Supplier::class);
  }
  public function Price_type(){
    return $this->belongsTo(Price_type::class);
  }
  public function Place(){
    return $this->belongsTo(Place::class);
  }

  public function Buy_tran_work(){
    return $this->hasMany(Buy_tran_work::class,'buy_id');
  }
  public function Tar_Buy(){
    return $this->hasMany(Tar_buy::class);
  }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
