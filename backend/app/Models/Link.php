<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'item_id',
        'url',
        'animation',
        'animation_speed',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function delete()
    {
        // Delete the associated Item, which will cascade delete program_items
        $this->item()->delete();
        
        // Delete the Link itself
        return parent::delete();
    }
}
