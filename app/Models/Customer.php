<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
  protected $connection = 'other';

  public function Customer_type()
  {
      return $this->belongsTo(Customer_type::class);
  }
  public function Sell(){
    return $this->hasMany(Sell::class);
  }
  public function Sells_work(){
    return $this->hasMany(Sell_work::class);
  }
    public function Receipt(){
        return $this->hasMany(Receipt::class);
    }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
