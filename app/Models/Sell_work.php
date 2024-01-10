<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Thiagoprz\CompositeKey\HasCompositeKey;


class Sell_work extends Model
{
  use HasCompositeKey;
  protected $connection = 'other';

  protected $primaryKey = ['id', 'id2'];

  public function Customer(){
    return $this->belongsTo(Customer::class);
  }
  public function Price_type(){
    return $this->belongsTo(Price_type::class);
  }
  public function Place(){
    return $this->belongsTo(Place::class);
  }

  public function Sell_tran_work(){
    return $this->hasMany(Sell_tran_work::class);
  }
  public function Tar_Sell(){
    return $this->hasMany(Tar_Sell::class);
  }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
