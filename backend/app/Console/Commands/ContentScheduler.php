<?php

namespace App\Console\Commands;

use App\Services\ContentSchedulerService;
use Illuminate\Console\Command;

class ContentScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:schedule {action=start : Action to perform (start|stop|status)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage the content scheduler service for live displays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $scheduler = app(ContentSchedulerService::class);

        switch ($action) {
            case 'start':
                if ($scheduler->isRunning()) {
                    $this->warn('Content scheduler is already running.');
                    return 1;
                }

                $this->info('Starting content scheduler...');
                $scheduler->start();
                break;

            case 'stop':
                if (!$scheduler->isRunning()) {
                    $this->warn('Content scheduler is not running.');
                    return 1;
                }

                $this->info('Stopping content scheduler...');
                $scheduler->stop();
                $this->info('Content scheduler stopped.');
                break;

            case 'status':
                if ($scheduler->isRunning()) {
                    $this->info('Content scheduler is running.');
                    $this->line('Currently displaying sort_order: ' . $scheduler->getCurrentlyDisplayed());
                } else {
                    $this->warn('Content scheduler is not running.');
                }
                break;

            default:
                $this->error("Unknown action: $action. Use 'start', 'stop', or 'status'.");
                return 1;
        }

        return 0;
    }
}
