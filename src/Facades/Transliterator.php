<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string toLatin(string $text)
 * @method static string toCyrillic(string $text)
 * @method static string convert(string $text, string $to = 'latin')
 * @method static string swap(string $text)
 * @method static bool hasCyrillic(string $text)
 * @method static bool hasLatin(string $text)
 *
 * @see \NurbekJummayev\LaravelCyrillicLatin\Transliterator
 */
class Transliterator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'transliterator';
    }
}
