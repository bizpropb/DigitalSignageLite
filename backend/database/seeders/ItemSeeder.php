<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Link;
use App\Models\Embedding;
use App\Models\Program;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 9 items (6 links + 3 embeddings)

        // Links
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
            'name' => 'Filament PHP',
            'description' => 'Admin panel framework',
            'duration' => 45,
        ]);
        Link::create([
            'item_id' => $item3->id,
            'url' => 'https://filamentphp.com',
            'animation' => 'down',
            'animation_speed' => 2,
        ]);

        $item4 = Item::create([
            'type' => 'Link',
            'name' => 'GitHub',
            'description' => 'Code repository platform',
            'duration' => 50,
        ]);
        Link::create([
            'item_id' => $item4->id,
            'url' => 'https://github.com',
            'animation' => 'none',
            'animation_speed' => 1,
        ]);

        $item5 = Item::create([
            'type' => 'Link',
            'name' => 'React Documentation',
            'description' => 'Official React documentation',
            'duration' => 40,
        ]);
        Link::create([
            'item_id' => $item5->id,
            'url' => 'https://react.dev/',
            'animation' => 'down&reset',
            'animation_speed' => 1,
        ]);

        $item6 = Item::create([
            'type' => 'Link',
            'name' => 'Wikipedia Main Page',
            'description' => 'Wikipedia homepage',
            'duration' => 45,
        ]);
        Link::create([
            'item_id' => $item6->id,
            'url' => 'https://en.wikipedia.org/wiki/Main_Page',
            'animation' => 'down&up',
            'animation_speed' => 1,
        ]);

        // Embeddings
        $item7 = Item::create([
            'type' => 'Embedding',
            'name' => 'Microsoft Sway Sample',
            'description' => 'Sample Sway presentation',
            'duration' => 60,
        ]);
        Embedding::create([
            'item_id' => $item7->id,
            'embed_code' => '<iframe width="100%" height="100%" src="https://sway.cloud.microsoft/s/obcdiwDpiOhlRDB2/embed" frameborder="0" marginheight="0" marginwidth="0" max-width="100%" sandbox="allow-forms allow-modals allow-orientation-lock allow-popups allow-same-origin allow-scripts" scrolling="no" style="border: none; max-width: 100%; max-height: 100vh" allowfullscreen mozallowfullscreen msallowfullscreen webkitallowfullscreen></iframe>',
        ]);

        $item8 = Item::create([
            'type' => 'Embedding',
            'name' => 'YouTube, muted, with subtitles',
            'description' => 'YouTube video with captions',
            'duration' => 55,
        ]);
        Embedding::create([
            'item_id' => $item8->id,
            'embed_code' => '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/SqcY0GlETPk?autoplay=1&mute=1&controls=0&cc_load_policy=1&cc_lang_pref=en&hl=en" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
        ]);

        $item9 = Item::create([
            'type' => 'Embedding',
            'name' => 'Custom Page',
            'description' => 'Custom HTML content',
            'duration' => 30,
        ]);
        Embedding::create([
            'item_id' => $item9->id,
            'embed_code' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .fullscreen-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: \'Arial\', sans-serif;
            overflow: hidden;
        }

        #particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
            animation: float 5s ease-out infinite;
            bottom: 0;
        }

        .particle:nth-child(1) { animation-duration: 4s; animation-delay: 0s; left: 10%; }
        .particle:nth-child(2) { animation-duration: 5.5s; animation-delay: 0.5s; left: 80%; }
        .particle:nth-child(3) { animation-duration: 6s; animation-delay: 1s; left: 30%; }
        .particle:nth-child(4) { animation-duration: 4.5s; animation-delay: 1.5s; left: 70%; }
        .particle:nth-child(5) { animation-duration: 5s; animation-delay: 2s; left: 50%; }
        .particle:nth-child(6) { animation-duration: 6.5s; animation-delay: 0.3s; left: 20%; }
        .particle:nth-child(7) { animation-duration: 5.2s; animation-delay: 1.2s; left: 90%; }
        .particle:nth-child(8) { animation-duration: 4.8s; animation-delay: 0.8s; left: 40%; }
        .particle:nth-child(9) { animation-duration: 5.8s; animation-delay: 1.8s; left: 60%; }
        .particle:nth-child(10) { animation-duration: 5.3s; animation-delay: 0.6s; left: 15%; }

        .container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 60px 80px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 90vw;
            z-index: 2;
            animation: glow 8s ease-in-out infinite;
        }

        h1 {
            font-size: 4rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: none;
            animation: fadeInDown 1s ease-out;
        }

        p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .highlight {
            color: #764ba2;
            font-weight: bold;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100vh) translateX(20px) scale(0.3);
                opacity: 0;
            }
        }

        @keyframes glow {
            0% { box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4); }
            25% { box-shadow: 0 20px 60px rgba(234, 102, 126, 0.4); }
            50% { box-shadow: 0 20px 60px rgba(126, 234, 102, 0.4); }
            75% { box-shadow: 0 20px 60px rgba(234, 126, 234, 0.4); }
            100% { box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4); }
        }
    </style>
</head>
<body>
    <div class="fullscreen-wrapper">
        <div id="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        <div class="container">
            <h1>Custom Page</h1>
            <p>This is a <span class="highlight">custom HTML injection</span> with CSS animations.</p>
            <p>It renders directly in the React component using dangerouslySetInnerHTML.</p>
        </div>
    </div>
</body>
</html>',
        ]);

        // Assign items to programs
        $publicProgram = Program::where('name', 'Public-Facing-1')->first();
        $internalProgram = Program::where('name', 'Internal-1')->first();
        $advertisementProgram = Program::where('name', 'Advertisement-1')->first();

        if ($publicProgram) {
            $publicProgram->items()->attach([$item1->id => ['sort_order' => 1], $item2->id => ['sort_order' => 2], $item7->id => ['sort_order' => 3]]);
        }

        if ($internalProgram) {
            $internalProgram->items()->attach([$item3->id => ['sort_order' => 1], $item4->id => ['sort_order' => 2], $item8->id => ['sort_order' => 3]]);
        }

        if ($advertisementProgram) {
            $advertisementProgram->items()->attach([$item5->id => ['sort_order' => 1], $item6->id => ['sort_order' => 2], $item9->id => ['sort_order' => 3]]);
        }
    }
}
