<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Link;
use App\Models\Program;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 9 unique items
        $item1 = Item::create([
            'type' => 'Link',
            'name' => 'Google Search',
            'description' => 'Google search engine homepage',
            'duration' => 30,
        ]);
        Link::create([
            'item_id' => $item1->id,
            'url' => 'https://www.google.com',
            'animation' => 'none',
            'animation_speed' => 1,
        ]);

        $item2 = Item::create([
            'type' => 'Link',
            'name' => 'Wikipedia',
            'description' => 'Free online encyclopedia',
            'duration' => 35,
        ]);
        Link::create([
            'item_id' => $item2->id,
            'url' => 'https://www.wikipedia.org',
            'animation' => 'down',
            'animation_speed' => 1,
        ]);

        $item3 = Item::create([
            'type' => 'Link',
            'name' => 'BBC News',
            'description' => 'Latest news and current affairs',
            'duration' => 40,
        ]);
        Link::create([
            'item_id' => $item3->id,
            'url' => 'https://www.bbc.com/news',
            'animation' => 'down&up',
            'animation_speed' => 2,
        ]);

        $item4 = Item::create([
            'type' => 'Link',
            'name' => 'Filament PHP',
            'description' => 'Admin panel framework',
            'duration' => 45,
        ]);
        Link::create([
            'item_id' => $item4->id,
            'url' => 'https://filamentphp.com',
            'animation' => 'down',
            'animation_speed' => 2,
        ]);

        $item5 = Item::create([
            'type' => 'Link',
            'name' => 'GitHub',
            'description' => 'Code repository platform',
            'duration' => 50,
        ]);
        Link::create([
            'item_id' => $item5->id,
            'url' => 'https://github.com',
            'animation' => 'none',
            'animation_speed' => 1,
        ]);

        $item6 = Item::create([
            'type' => 'Link',
            'name' => 'Stack Overflow',
            'description' => 'Programming Q&A community',
            'duration' => 40,
        ]);
        Link::create([
            'item_id' => $item6->id,
            'url' => 'https://stackoverflow.com',
            'animation' => 'down&reset',
            'animation_speed' => 1,
        ]);

        $item7 = Item::create([
            'type' => 'Link',
            'name' => 'Microsoft Sway',
            'description' => 'Presentation platform',
            'duration' => 60,
        ]);
        Link::create([
            'item_id' => $item7->id,
            'url' => 'https://sway.office.com',
            'animation' => 'down&up',
            'animation_speed' => 1,
        ]);

        $item8 = Item::create([
            'type' => 'Link',
            'name' => 'YouTube',
            'description' => 'Video sharing platform',
            'duration' => 55,
        ]);
        Link::create([
            'item_id' => $item8->id,
            'url' => 'https://www.youtube.com',
            'animation' => 'down',
            'animation_speed' => 2,
        ]);

        $item9 = Item::create([
            'type' => 'Link',
            'name' => 'Netflix',
            'description' => 'Streaming service',
            'duration' => 65,
        ]);
        Link::create([
            'item_id' => $item9->id,
            'url' => 'https://www.netflix.com',
            'animation' => 'none',
            'animation_speed' => 1,
        ]);

        // Assign items to programs
        $publicProgram = Program::where('name', 'Public-Facing-1')->first();
        $internalProgram = Program::where('name', 'Internal-1')->first();
        $advertisementProgram = Program::where('name', 'Advertisement-1')->first();

        if ($publicProgram) {
            $publicProgram->items()->attach([$item1->id => ['sort_order' => 1], $item2->id => ['sort_order' => 2], $item3->id => ['sort_order' => 3]]);
        }

        if ($internalProgram) {
            $internalProgram->items()->attach([$item4->id => ['sort_order' => 1], $item5->id => ['sort_order' => 2], $item6->id => ['sort_order' => 3]]);
        }

        if ($advertisementProgram) {
            $advertisementProgram->items()->attach([$item7->id => ['sort_order' => 1], $item8->id => ['sort_order' => 2], $item9->id => ['sort_order' => 3]]);
        }
    }
}
