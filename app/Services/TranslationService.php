<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    public static function translate(string $text, string $targetLang): string
    {
        try {
            return (new GoogleTranslate($targetLang))->translate($text);
        } catch (\Exception) {
            return $text;
        }
    }

    public static function translateToAll(string $text): array
    {
        return [
            'en' => self::translate($text, 'en'),
            'ar' => self::translate($text, 'ar'),
        ];
    }
}