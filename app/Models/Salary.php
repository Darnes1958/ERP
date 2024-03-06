<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Salary extends Model
{
    protected $connection = 'other';
  protected $casts = [
    'status' => 'boolean',
  ];
    public function Salarytran(){
        return $this->hasMany(Salarytran::class);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }
}
