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
        // Drop existing constraint
        DB::statement('ALTER TABLE links DROP CONSTRAINT IF EXISTS links_animation_check');
        
        // Update existing data to match new constraint
        DB::table('links')->whereNotIn('animation', ['none', 'down', 'down&reset', 'down&up'])->update(['animation' => 'none']);
        
        // Add new constraint with updated values
        DB::statement("ALTER TABLE links ADD CONSTRAINT links_animation_check CHECK (animation IN ('none', 'down', 'down&reset', 'down&up'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original constraint (assuming it had different values)
        DB::statement('ALTER TABLE links DROP CONSTRAINT IF EXISTS links_animation_check');
    }
};
