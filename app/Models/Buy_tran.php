<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Buy_tran extends Model
{
  protected $connection = 'other';

  public function Buy(){
    return $this->belongsTo(Buy::class);
  }
  public function Item(){
    return $this->belongsTo(Item::class);
  }
  public function Tar_buy(){
    return $this->belongsTo(Tar_buy::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }
}
