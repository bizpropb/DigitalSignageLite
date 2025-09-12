<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'type',
        'name',
        'description',
        'duration',
        'created_at',
    ];

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_items');
    }

    public function morphable()
    {
        return $this->morphTo();
    }
}
