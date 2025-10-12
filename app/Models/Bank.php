<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Bank extends Model
{
  protected $connection = 'other';


  public function Main(){
    return $this->hasMany(Main::class);
  }


  public function Taj(){
    return $this->belongsTo(Taj::class);
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
