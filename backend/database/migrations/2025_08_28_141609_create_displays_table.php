<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('displays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_type');
            $table->string('location')->nullable();
            $table->string('status')->default('disconnected');
            $table->timestamp('last_seen')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->index(['display_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displays');
    }
};
