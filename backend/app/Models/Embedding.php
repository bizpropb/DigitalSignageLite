<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Embedding extends Model
{
    protected $fillable = [
        'item_id',
        'embed_code',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function delete()
    {
        $this->item()->delete();
        return parent::delete();
    }
}
