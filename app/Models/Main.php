<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Table;

class Main extends Model
{
    use HasFactory;
  protected $connection = 'other';

  protected $appends =['name'];


    public function tarkst()
    {
        return $this->morphMany(Tarkst::class, 'tarkstable');
    }
    public function overkstable()
    {
        return $this->morphMany(Overkst::class, 'overkstable');
    }
  public function Bank(){
    return $this->belongsTo(Bank::class);
  }
    public function Taj(){
        return $this->belongsTo(Taj::class);
    }
  public function Customer(){
    return $this->belongsTo(Customer::class);
  }
    public function getNameAttribute(){
        return $this->Customer->name;
    }

  public function Sell(){
        return $this->belongsTo(Sell::class);
    }

    public function Tran(){
        return $this->hasMany(Tran::class);
    }
    public function trans(){
        return $this->hasMany(Tran::class);
    }



    public function Stop(){
        return $this->hasOne(Stop::class);
}
    public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;

    }
  }

}
