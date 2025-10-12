<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Fromexcel extends Model
{
    protected $connection = 'other';
    public $timestamps=false;

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
