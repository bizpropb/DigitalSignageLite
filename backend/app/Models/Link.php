<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'item_id',
        'animation',
        'animation_speed',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
