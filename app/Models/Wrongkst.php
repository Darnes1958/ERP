<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Wrongkst extends Model
{
  protected $connection = 'other';

  public function tarkst()
  {
      return $this->morphOne(Tarkst::class, 'tarkstable');
  }
  public function Taj(){
      return $this->belongsTo(Taj::class);
  }

  protected $casts = [
      'status' => Status::class,
  ];
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection = Auth::user()->company;
    }
  }
}
