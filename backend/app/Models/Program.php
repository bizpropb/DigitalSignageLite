<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_at',
    ];

    public function displays()
    {
        return $this->hasMany(Display::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'program_items');
    }
}
