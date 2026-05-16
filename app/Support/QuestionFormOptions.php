<?php

namespace App\Support;

class QuestionFormOptions
{
    /**
     * @return array<string, string>
     */
    public static function componentOptions(): array
    {
        return [
            'radio' => __('admin.qcu'),
            'checkbox' => __('admin.qcm'),
            'list' => __('admin.list_dropdown'),
            'text' => __('admin.free_text'),
            'date' => __('admin.date'),
            'photo' => __('admin.photo'),
        ];
    }

    public static function isMcqComponent(?string $component): bool
    {
        return in_array($component, ['radio', 'list', 'checkbox'], true);
    }

    public static function isSingleChoiceComponent(?string $component): bool
    {
        return in_array($component, ['radio', 'list'], true);
    }

    public static function isMultipleChoiceComponent(?string $component): bool
    {
        return $component === 'checkbox';
    }
}
