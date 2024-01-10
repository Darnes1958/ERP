<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sell_tran extends Model
{
  protected $connection = 'other';

  public function Sell(){
    return $this->belongsTo(Sell::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }
  public function Tar_sell(){
    return $this->belongsTo(Tar_sell::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
