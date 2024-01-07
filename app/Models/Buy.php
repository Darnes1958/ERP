<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Buy extends Model
{
  protected $connection = 'other';

  public function Supplier(){
    return $this->belongsTo(Supplier::class);
  }
  public function Price_type(){
    return $this->belongsTo(Price_type::class);
  }
  public function Place(){
    return $this->belongsTo(Place::class);
  }

  public function Buy_tran(){
    return $this->hasMany(Buy_tran::class);
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
