<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'New',
                'description' => 'Newly added, no programming'
            ],
            [
                'name' => 'Inactive',
                'description' => 'Inactive, no programming'
            ],
            [
                'name' => 'Public-Facing-1',
                'description' => 'Public Facing Programming Placeholder'
            ],
            [
                'name' => 'Internal-1',
                'description' => 'Internal Programming Placeholder'
            ],
            [
                'name' => 'Advertisement-1',
                'description' => 'Advertisement Programming Placeholder'
            ]
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
