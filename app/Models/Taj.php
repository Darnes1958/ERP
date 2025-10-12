<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Taj extends Model
{
    use HasFactory;
  protected $connection = 'other';
    public function WrongKst()
    {
        return $this->hasMany(Wrongkst::class);
    }

  public function Bank(){
    return $this->hasMany(Bank::class);
  }
  public function BankMain(){
        return $this->belongsTo(BankMain::class);
  }
  public function main()
  {
    return $this->hasManyThrough('App\Models\main', 'App\Models\bank');
  }

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;
    }
  }

}
