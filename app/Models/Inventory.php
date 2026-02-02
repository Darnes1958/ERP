<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\In;

class Inventory extends Model
{
    public function Place_stock()
    {
        return $this->belongsTo(Place_stock::class);
    }
    public function InventoryData()
    {
        return $this->belongsTo(InventoryData::class);
    }
    public function Place()
    {
        return $this->belongsTo(Place::class);
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
