<?php

namespace App\Support;

class ReadingTime
{
    public static function estimate(?string $html): int
    {
        $words = str_word_count(strip_tags((string) $html));

        return max(1, (int) ceil($words / 200));
    }
}
