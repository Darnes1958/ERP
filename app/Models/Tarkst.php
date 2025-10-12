<?php

namespace App\Models;

use App\Enums\Tar_type;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Tarkst extends Model
{
  protected $connection = 'other';
  protected $casts=[
      'tar_type'=>Tar_type::class,
  ];
    public function tarkstable(): MorphTo
    {
        return $this->morphTo();
    }

  public function Main(){
    return $this->belongsTo(Main::class);
  }
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    if (Auth::check()) {
      $this->connection = Auth::user()->company;
    }
  }
}
