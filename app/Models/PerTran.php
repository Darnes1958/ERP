<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PerTran extends Model
{
    protected $connection = 'other';

    public function Per()
    {
        return $this->belongsTo(Per::class);
    }
    public function Item()
    {
        return $this->belongsTo(Item::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;

        }
    }
}
