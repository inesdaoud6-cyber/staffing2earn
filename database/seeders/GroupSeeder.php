<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $block = Block::where('name', 'Technical Skills')->first();

        if (!$block) return;

        Group::firstOrCreate(['name' => 'Web Development'], ['block_id' => $block->id, 'order' => 1]);
        Group::firstOrCreate(['name' => 'Database'],        ['block_id' => $block->id, 'order' => 2]);
        Group::firstOrCreate(['name' => 'Soft Skills'],     ['block_id' => $block->id, 'order' => 3]);
    }
}