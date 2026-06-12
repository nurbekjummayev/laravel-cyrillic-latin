<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Contracts;

interface Converter
{
    /**
     * Convert every word of the source script in the given text,
     * leaving everything else (markup, URLs, numbers, ...) untouched.
     */
    public function convert(string $text): string;
}
