<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    public function run(): void
    {
        Block::firstOrCreate(
            ['name' => 'Technical Skills'],
            ['title' => 'Main Block', 'order' => 1]
        );
    }
}