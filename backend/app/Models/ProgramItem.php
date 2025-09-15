<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProgramItem extends Pivot
{
    protected $table = 'program_items';

    protected $fillable = ['program_id', 'item_id', 'sort_order'];

    public $timestamps = false;

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}