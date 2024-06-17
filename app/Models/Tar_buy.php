<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tar_buy extends Model
{
  protected $connection = 'other';


  public function Buy(){
    return $this->belongsTo(Buy::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }
  public function Buy_tran(){
    return $this->hasMany(Buy_tran::class);
  }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
