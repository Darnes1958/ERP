<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Salarytran extends Model
{
    protected $connection = 'other';

    public function Salary(){
        return $this->belongsTo(Salary::class);
    }
  public function Acc(){
    return $this->belongsTo(Acc::class);
  }

  public function Kazena(){
    return $this->belongsTo(Kazena::class);
  }

  public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }

}
