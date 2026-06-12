<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Support;

/**
 * Built-in exception words whose spelling cannot be derived from the letter
 * rules. The word lists live in resources/dictionaries/*.php — plain PHP
 * arrays so they are compiled and cached by opcache (unlike JSON, which
 * would be parsed on every request).
 *
 * Users can override or extend these via the config file; their entries
 * always win. The dictionary matches whole words only; suffixed forms
 * (kompyuterlar, oktyabrda, ...) fall back to the letter rules.
 */
final class DefaultDictionary
{
    /** @var array<string, string>|null */
    private static ?array $toLatin = null;

    /** @var array<string, string>|null */
    private static ?array $toCyrillic = null;

    private function __construct() {}

    /**
     * Cyrillic words whose official Latin spelling differs from the rules.
     *
     * @return array<string, string>
     */
    public static function toLatin(): array
    {
        return self::$toLatin ??= require dirname(__DIR__, 2).'/resources/dictionaries/to_latin.php';
    }

    /**
     * Latin words whose Cyrillic spelling contains ь/ъ, й+о or э that the
     * letter rules cannot reconstruct.
     *
     * @return array<string, string>
     */
    public static function toCyrillic(): array
    {
        return self::$toCyrillic ??= require dirname(__DIR__, 2).'/resources/dictionaries/to_cyrillic.php';
    }
}
