<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Main_arc extends Model
{
    protected $appends =['name'];


    public function tarkst()
    {
        return $this->morphMany(Tarkst::class, 'tarkstable');
    }
    public function overkstable()
    {
        return $this->morphMany(Overkst::class, 'overkstable');
    }
    public function hafitha(): MorphMany
    {
        return $this->morphMany(HafithaTran::class, 'hafithaable');
    }
    public function Bank(){
    return $this->belongsTo(Bank::class);
  }
  public function Job(): BelongsTo
  {
      return $this->belongsTo(Job::class);
  }
    public function Taj(){
        return $this->belongsTo(Taj::class);
    }
  public function Customer(){
    return $this->belongsTo(Customer::class);
  }
    public function getNameAttribute(){
        if ($this->Customer)
            return $this->Customer->name; else return null;
    }
  public function Sell(){
    return $this->belongsTo(Sell::class);
  }

  public function Trans_arc(){
    return $this->belongsTo(Trans_arc::class);
  }


  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection=Auth::user()->company;
    }
  }

}
