<?php

namespace App\Models;

use App\Enums\Active;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InventoryData extends Model
{
    protected $table='inventory_datas';
    protected $casts=['active'=>Active::class];
    public function Inventory()
    {
        return $this->hasMany(Inventory::class);
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (Auth::check()) {
            $this->connection=Auth::user()->company;
        }
    }
}
