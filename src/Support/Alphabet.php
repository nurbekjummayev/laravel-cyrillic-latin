<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Support;

/**
 * Single source of truth for the Uzbek Cyrillic <-> Latin letter mappings.
 *
 * Only the lowercase pairs are declared by hand; uppercase forms, ALL-CAPS
 * digraphs (CH, SH, YO, ...) and apostrophe variants are derived from them,
 * so the two directions can never drift apart.
 */
final class Alphabet
{
    /**
     * Apostrophe-like characters users type instead of the canonical
     * ‘ (U+2018, used in o‘/g‘) and ’ (U+2019, tutuq belgisi).
     */
    public const array APOSTROPHE_VARIANTS = ['‘', '’', 'ʻ', 'ʼ', "'"];

    public const string CYRILLIC_VOWELS = 'аеёиоуыэюяўАЕЁИОУЫЭЮЯЎ';

    public const string LATIN_VOWELS = 'aeiouAEIOU';

    /**
     * Lowercase Cyrillic letters and their Latin spellings.
     *
     * Ordering matters for the reverse map: when two Cyrillic letters share
     * one Latin spelling (е/э -> e, и/ы -> i, ш/щ -> sh), the first one wins.
     */
    private const array PAIRS = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'j', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'x', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sh', 'ъ' => '’', 'ы' => 'i', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'ғ' => 'g‘', 'қ' => 'q', 'ў' => 'o‘', 'ҳ' => 'h',
    ];

    /**
     * Latin spellings that exist only in one direction and therefore cannot
     * be derived by inverting PAIRS.
     */
    private const array REVERSE_EXTRAS = [
        'ye' => 'е',   // "е" at word start / after a vowel is spelled "ye": Yetti -> Етти
        'yo‘' => 'йў', // would otherwise be eaten by "yo" -> "ё": yo‘l -> йўл
    ];

    /** @var array<string, string>|null */
    private static ?array $toLatin = null;

    /** @var array<string, string>|null */
    private static ?array $toCyrillic = null;

    private function __construct() {}

    /**
     * strtr() map for Cyrillic -> Latin, both letter cases.
     *
     * @return array<string, string>
     */
    public static function toLatinMap(): array
    {
        if (self::$toLatin !== null) {
            return self::$toLatin;
        }

        $map = [];
        foreach (self::PAIRS as $cyrillic => $latin) {
            $map[$cyrillic] = $latin;
            $map[mb_strtoupper($cyrillic)] = self::capitalize($latin);
        }

        return self::$toLatin = $map;
    }

    /**
     * strtr() map for Latin -> Cyrillic: lowercase, Capitalized and ALL-CAPS
     * digraphs plus all apostrophe variants.
     *
     * @return array<string, string>
     */
    public static function toCyrillicMap(): array
    {
        if (self::$toCyrillic !== null) {
            return self::$toCyrillic;
        }

        $map = [];
        foreach (self::PAIRS as $cyrillic => $latin) {
            if ($latin !== '' && ! isset($map[$latin])) {
                $map[$latin] = $cyrillic;
            }
        }

        $map = [...$map, ...self::REVERSE_EXTRAS];
        $map = self::withApostropheVariants($map);
        $map = self::withUppercaseVariants($map);

        return self::$toCyrillic = $map;
    }

    /**
     * Duplicate every key containing an apostrophe for each variant,
     * e.g. "o‘" => "ў" also becomes "o’", "oʻ", "oʼ", "o'" => "ў".
     *
     * @param  array<string, string>  $map
     * @return array<string, string>
     */
    private static function withApostropheVariants(array $map): array
    {
        foreach ($map as $latin => $cyrillic) {
            foreach (self::APOSTROPHE_VARIANTS as $apostrophe) {
                $alias = str_replace(['‘', '’'], $apostrophe, (string) $latin);
                if (! isset($map[$alias])) {
                    $map[$alias] = $cyrillic;
                }
            }
        }

        return $map;
    }

    /**
     * Add Capitalized ("Ch" => "Ч") and ALL-CAPS ("CH" => "Ч") variants.
     *
     * @param  array<string, string>  $map
     * @return array<string, string>
     */
    private static function withUppercaseVariants(array $map): array
    {
        foreach ($map as $latin => $cyrillic) {
            $latin = (string) $latin;

            $variants = [
                self::capitalize($latin) => self::capitalize($cyrillic),
                mb_strtoupper($latin) => mb_strtoupper($cyrillic),
            ];

            foreach ($variants as $aliasKey => $aliasValue) {
                if ($aliasKey !== '' && ! isset($map[$aliasKey])) {
                    $map[$aliasKey] = $aliasValue;
                }
            }
        }

        return $map;
    }

    private static function capitalize(string $value): string
    {
        return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }
}
