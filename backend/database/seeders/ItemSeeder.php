<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Link;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Google Search item
        $googleItem = Item::create([
            'type' => 'Link',
            'name' => 'Google Search',
            'description' => 'Google search engine homepage and search functionality',
            'duration' => 30,
        ]);

        Link::create([
            'item_id' => $googleItem->id,
            'url' => 'https://www.google.com',
            'animation' => 'none',
            'animation_speed' => 1,
        ]);

        // Create Filament PHP item
        $filamentItem = Item::create([
            'type' => 'Link', 
            'name' => 'Filament PHP',
            'description' => 'Modern admin panel framework for Laravel applications',
            'duration' => 45,
        ]);

        Link::create([
            'item_id' => $filamentItem->id,
            'url' => 'https://filamentphp.com',
            'animation' => 'down',
            'animation_speed' => 2,
        ]);

        // Create Microsoft Sway item
        $swayItem = Item::create([
            'type' => 'Link',
            'name' => 'Microsoft Sway',
            'description' => 'Cloud-based presentation and storytelling application',
            'duration' => 60,
        ]);

        Link::create([
            'item_id' => $swayItem->id,
            'url' => 'https://sway.office.com',
            'animation' => 'down&up',
            'animation_speed' => 1,
        ]);
    }
}
