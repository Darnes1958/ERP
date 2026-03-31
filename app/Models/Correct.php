<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Correct extends Model
{
    protected $connection = 'other';


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
