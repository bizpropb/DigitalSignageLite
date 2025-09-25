<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Display;
use App\Models\Program;

class DebugDisplays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:displays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug display and program assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DISPLAYS ===');
        $displays = Display::with('program')->get();
        
        foreach ($displays as $display) {
            $this->line("ID: {$display->id}");
            $this->line("Name: {$display->name}");
            $this->line("Program: " . ($display->program ? $display->program->name : 'NULL'));
            $this->line("Program ID: {$display->program_id}");
            $this->line("Access Token: {$display->access_token}");
            $this->line("Initialized: " . ($display->initialized ? 'YES' : 'NO'));
            $this->line("---");
        }

        $this->info('=== PROGRAMS ===');
        $programs = Program::all();
        
        foreach ($programs as $program) {
            $this->line("ID: {$program->id}");
            $this->line("Name: {$program->name}");
            $this->line("---");
        }
    }
}
