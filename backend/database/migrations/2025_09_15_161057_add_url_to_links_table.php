<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->string('url')->nullable()->after('item_id');
        });
        
        // Update existing records with placeholder URL
        DB::table('links')->whereNull('url')->update(['url' => 'https://example.com']);
        
        // Make the column non-nullable
        Schema::table('links', function (Blueprint $table) {
            $table->string('url')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};
