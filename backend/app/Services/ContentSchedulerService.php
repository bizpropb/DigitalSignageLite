<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Link;
use App\Models\Program;
use App\Events\ProgramContentUpdate;
use Illuminate\Support\Facades\Log;

class ContentSchedulerService
{
    private static $programStates = [];
    private static $isRunning = false;

    public function start()
    {
        if (self::$isRunning) {
            Log::info("ContentScheduler: Already running, ignoring start request");
            return;
        }

        Log::info("ContentScheduler: Starting multi-program content rotation service");
        self::$isRunning = true;
        $this->runMultiProgramLoop();
    }

    public function stop()
    {
        Log::info("ContentScheduler: Stopping content rotation service");
        self::$isRunning = false;
    }

    public function isRunning()
    {
        return self::$isRunning;
    }

    private function runMultiProgramLoop()
    {
        Log::info("ContentScheduler: Starting multi-program content rotation loop");

        while (self::$isRunning) {
            try {
                // Get all programs that have items
                $programs = Program::with(['programItems.item', 'displays'])->whereHas('programItems')->get();

                if ($programs->isEmpty()) {
                    Log::warning("ContentScheduler: No programs with items found, sleeping for 10 seconds");
                    sleep(10);
                    continue;
                }

                Log::info("ContentScheduler: Processing {$programs->count()} programs");

                // Initialize program states if not set
                foreach ($programs as $program) {
                    if (!isset(self::$programStates[$program->id])) {
                        self::$programStates[$program->id] = [
                            'current_sort_order' => 1,
                            'last_update' => time(),
                            'current_duration' => 0
                        ];
                    }
                }

                // Process each program
                foreach ($programs as $program) {
                    $this->processProgramContent($program);
                }

                // Sleep for 1 second before checking again
                sleep(1);
                
                // Force garbage collection to prevent memory leaks
                if (gc_enabled()) {
                    gc_collect_cycles();
                }

            } catch (\Exception $e) {
                Log::error("ContentScheduler: Error in multi-program loop", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Sleep for a bit before trying again
                sleep(5);
            }
        }

        Log::info("ContentScheduler: Multi-program content rotation loop ended");
    }

    private function processProgramContent(Program $program)
    {
        $programState = self::$programStates[$program->id];
        $now = time();

        // Check if it's time to move to the next item
        if ($now >= $programState['last_update'] + $programState['current_duration']) {

            // Get current item for this program
            $programItem = $program->programItems()
                ->with('item')
                ->where('sort_order', $programState['current_sort_order'])
                ->first();

            if (!$programItem) {
                // Reset to beginning if we've reached the end
                Log::info("ContentScheduler: Program '{$program->name}' reached end, resetting to sort_order 1");
                $programState['current_sort_order'] = 1;

                $programItem = $program->programItems()
                    ->with('item')
                    ->where('sort_order', 1)
                    ->first();

                if (!$programItem) {
                    Log::warning("ContentScheduler: No items found for program '{$program->name}'");
                    return;
                }
            }

            $item = $programItem->item;

            Log::info("ContentScheduler: Broadcasting item for program '{$program->name}'", [
                'program_id' => $program->id,
                'sort_order' => $programState['current_sort_order'],
                'item_id' => $item->id,
                'type' => $item->type,
                'name' => $item->name,
                'duration' => $item->duration
            ]);

            // Prepare content data based on type
            $contentData = $this->prepareContentData($item, $programState['current_sort_order']);

            // Broadcast the content to all displays in this program
            if ($program->displays->isEmpty()) {
                Log::info("No displays for program {$program->id}, skipping broadcast");
            } else {
                foreach ($program->displays as $display) {
                    broadcast(new ProgramContentUpdate($contentData, $display->id));
                }
            }

            // Update program state
            self::$programStates[$program->id] = [
                'current_sort_order' => $programState['current_sort_order'] + 1,
                'last_update' => $now,
                'current_duration' => $item->duration
            ];

            Log::info("ContentScheduler: Program '{$program->name}' will show item for {$item->duration} seconds, next sort_order: " . self::$programStates[$program->id]['current_sort_order']);
        }
    }

    private function prepareContentData($item, $sortOrder)
    {
        $contentData = [
            'id' => $item->id,
            'type' => $item->type,
            'name' => $item->name,
            'description' => $item->description,
            'duration' => $item->duration,
            'sort_order' => $sortOrder
        ];

        // Add type-specific data
        switch (strtolower($item->type)) {
            case 'link':
                $link = Link::where('item_id', $item->id)->first();
                if ($link) {
                    $contentData['url'] = $link->url;
                    $contentData['animation'] = $link->animation;
                    $contentData['animation_speed'] = $link->animation_speed;
                } else {
                    Log::warning("ContentScheduler: Link data not found for item {$item->id}");
                    $contentData['url'] = 'https://example.com';
                    $contentData['animation'] = 'none';
                    $contentData['animation_speed'] = 1;
                }
                break;

            // Add other content types here in the future
            default:
                Log::warning("ContentScheduler: Unknown content type: {$item->type}");
                break;
        }

        return $contentData;
    }

    public function getCurrentlyDisplayed()
    {
        return self::$programStates;
    }

    public function getProgramStatus($programId = null)
    {
        if ($programId) {
            return self::$programStates[$programId] ?? null;
        }
        return self::$programStates;
    }

    public function setProgramSortOrder($programId, $sortOrder)
    {
        if (isset(self::$programStates[$programId])) {
            self::$programStates[$programId]['current_sort_order'] = $sortOrder;
            self::$programStates[$programId]['last_update'] = time();
            self::$programStates[$programId]['current_duration'] = 0; // Force immediate update
            Log::info("ContentScheduler: Manually set program {$programId} to sort_order: $sortOrder");
        }
    }
}
