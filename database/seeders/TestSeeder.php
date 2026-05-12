<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $groupDev = Group::where('name', 'Web Development')->first();

        $test1 = Test::firstOrCreate(
            ['name' => 'PHP Junior Test'],
            [
                'description'           => 'Assessment of basic PHP skills for junior profiles.',
                'eligibility_threshold' => 50,
                'talent_threshold'      => 80,
            ]
        );

        $test2 = Test::firstOrCreate(
            ['name' => 'Laravel Senior Test'],
            [
                'description'           => 'Advanced Laravel skills assessment for senior profiles.',
                'eligibility_threshold' => 60,
                'talent_threshold'      => 85,
            ]
        );

        $questions1 = Question::where('level', 1)->pluck('id');
        $test1->questions()->syncWithoutDetaching($questions1);

        $questions2 = Question::where('level', 2)->pluck('id');
        $test2->questions()->syncWithoutDetaching($questions2);
    }
}