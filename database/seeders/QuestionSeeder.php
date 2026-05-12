<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Block;
use App\Models\Group;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $block    = Block::where('name', 'Technical Skills')->first();
        $groupDev = Group::where('name', 'Web Development')->first();
        $groupBdd = Group::where('name', 'Database')->first();
        $groupSoft = Group::where('name', 'Soft Skills')->first();

        if (!$block) return;

        $q1 = Question::firstOrCreate(
            ['question_fr' => 'Quelle est la difference entre == et === en PHP ?'],
            [
                'block_id'         => $block->id,
                'group_id'         => $groupDev?->id,
                'question_en'      => 'What is the difference between == and === in PHP?',
                'question_ar'      => 'ما الفرق بين == و === في PHP؟',
                'component'        => 'radio',
                'level'            => 1,
                'obligatory'       => true,
                'scorable'         => true,
                'classification'   => 'primary',
                'max_note'         => 10,
                'possible_answers' => [
                    '== compares value and type, === compares only value',
                    '== compares only value, === compares value and type',
                    'There is no difference',
                    '== is for integers, === for strings',
                ],
            ]
        );

        Answer::firstOrCreate(['question_id' => $q1->id, 'text' => '== compares value and type, === compares only value'],  ['is_correct' => false, 'order' => 1]);
        Answer::firstOrCreate(['question_id' => $q1->id, 'text' => '== compares only value, === compares value and type'],  ['is_correct' => true,  'order' => 2]);
        Answer::firstOrCreate(['question_id' => $q1->id, 'text' => 'There is no difference'],                               ['is_correct' => false, 'order' => 3]);
        Answer::firstOrCreate(['question_id' => $q1->id, 'text' => '== is for integers, === for strings'],                  ['is_correct' => false, 'order' => 4]);

        $q2 = Question::firstOrCreate(
            ['question_fr' => 'Qu\'est-ce qu\'une cle etrangere en SQL ?'],
            [
                'block_id'         => $block->id,
                'group_id'         => $groupBdd?->id,
                'question_en'      => 'What is a foreign key in SQL?',
                'question_ar'      => 'ما هو المفتاح الأجنبي في SQL؟',
                'component'        => 'radio',
                'level'            => 1,
                'obligatory'       => true,
                'scorable'         => true,
                'classification'   => 'primary',
                'max_note'         => 10,
                'possible_answers' => [
                    'A column uniquely identifying each row',
                    'A column referencing the primary key of another table',
                    'An index on multiple columns',
                    'A constraint for NULL values',
                ],
            ]
        );

        Answer::firstOrCreate(['question_id' => $q2->id, 'text' => 'A column uniquely identifying each row'],                ['is_correct' => false, 'order' => 1]);
        Answer::firstOrCreate(['question_id' => $q2->id, 'text' => 'A column referencing the primary key of another table'], ['is_correct' => true,  'order' => 2]);
        Answer::firstOrCreate(['question_id' => $q2->id, 'text' => 'An index on multiple columns'],                          ['is_correct' => false, 'order' => 3]);
        Answer::firstOrCreate(['question_id' => $q2->id, 'text' => 'A constraint for NULL values'],                          ['is_correct' => false, 'order' => 4]);

        $q3 = Question::firstOrCreate(
            ['question_fr' => 'Decrivez votre methode de travail en equipe.'],
            [
                'block_id'       => $block->id,
                'group_id'       => $groupSoft?->id,
                'question_en'    => 'Describe your teamwork approach.',
                'question_ar'    => 'صف أسلوبك في العمل الجماعي.',
                'component'      => 'text',
                'level'          => 1,
                'obligatory'     => true,
                'scorable'       => false,
                'classification' => 'secondary',
                'max_note'       => 0,
            ]
        );
    }
}