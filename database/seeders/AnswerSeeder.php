<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        $q4 = Question::firstOrCreate(
            ['question_fr' => 'Quelle commande Artisan cree un middleware dans Laravel ?'],
            [
                'question_en'      => 'Which Artisan command creates a middleware in Laravel?',
                'question_ar'      => 'ما هو أمر Artisan الذي ينشئ middleware في Laravel؟',
                'component'        => 'radio',
                'level'            => 1,
                'obligatory'       => true,
                'scorable'         => true,
                'classification'   => 'primary',
                'max_note'         => 15,
                'possible_answers' => [
                    'php artisan make:middleware',
                    'php artisan create:middleware',
                    'php artisan middleware:make',
                    'php artisan generate:middleware',
                ],
            ]
        );

        Answer::firstOrCreate(['question_id' => $q4->id, 'text' => 'php artisan make:middleware'],     ['is_correct' => true,  'order' => 1]);
        Answer::firstOrCreate(['question_id' => $q4->id, 'text' => 'php artisan create:middleware'],   ['is_correct' => false, 'order' => 2]);
        Answer::firstOrCreate(['question_id' => $q4->id, 'text' => 'php artisan middleware:make'],     ['is_correct' => false, 'order' => 3]);
        Answer::firstOrCreate(['question_id' => $q4->id, 'text' => 'php artisan generate:middleware'], ['is_correct' => false, 'order' => 4]);
    }
}