<?php

namespace App\Models;

use App\Enums\DisplayType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Display extends Model
{
    protected $fillable = [
        'name',
        'display_type',
        'location',
        'status',
        'last_seen',
        'config',
        'auth_token',
    ];

    protected $casts = [
        'display_type' => DisplayType::class,
        'config' => 'array',
        'last_seen' => 'datetime',
    ];

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value,
            set: fn (string $value) => strtolower($value),
        );
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected' && 
               $this->last_seen && 
               $this->last_seen->diffInMinutes(now()) < 5;
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected')
                    ->where('last_seen', '>=', now()->subMinutes(5));
    }

    public function scopeByType($query, DisplayType $type)
    {
        return $query->where('display_type', $type);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($display) {
            if (empty($display->auth_token)) {
                $display->auth_token = Str::random(32);
            }
        });
    }

    public function regenerateAuthToken()
    {
        $this->auth_token = Str::random(32);
        $this->save();
        return $this->auth_token;
    }
}
