<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Display extends Model
{
    protected $fillable = [
        'name',
        'program_id',
        'location',
        'status',
        'last_seen',
        'config',
        'auth_token',
        'access_token',
        'initialized',
    ];

    protected $casts = [
        'config' => 'array',
        'last_seen' => 'datetime',
        'initialized' => 'boolean',
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

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($display) {
            if (empty($display->auth_token)) {
                $display->auth_token = Str::random(32);
            }
            if (empty($display->access_token)) {
                $display->access_token = strtoupper(Str::random(6));
            }
        });
    }

    public function regenerateAuthToken()
    {
        $this->auth_token = Str::random(32);
        $this->save();
        return $this->auth_token;
    }

    public function regenerateAccessToken()
    {
        $this->access_token = strtoupper(Str::random(6));
        $this->save();
        return $this->access_token;
    }
}
