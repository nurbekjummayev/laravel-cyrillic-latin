<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Support;

/**
 * Word-level overrides for words that must not be transliterated
 * letter-by-letter (e.g. "цех" => "sex"). Lookup is case-insensitive and
 * apostrophe-variant-insensitive; the original capitalization is restored
 * on the replacement.
 */
final class Dictionary
{
    /** @var array<string, string> normalized word => replacement */
    private readonly array $words;

    /**
     * @param  array<string, string>  $words
     */
    public function __construct(array $words = [])
    {
        $normalized = [];
        foreach ($words as $source => $replacement) {
            $normalized[self::normalize((string) $source)] = (string) $replacement;
        }

        $this->words = $normalized;
    }

    public function lookup(string $word): ?string
    {
        if ($this->words === []) {
            return null;
        }

        $replacement = $this->words[self::normalize($word)] ?? null;

        return $replacement === null ? null : self::matchCase($word, $replacement);
    }

    /**
     * Find the longest dictionary entry that is a proper prefix of the word
     * (the stem), so suffixed forms can reuse it: kompyuterlar -> kompyuter
     * + lar. Returns [replacement, remaining suffix] or null.
     *
     * @return array{string, string}|null
     */
    public function lookupStem(string $word): ?array
    {
        if ($this->words === []) {
            return null;
        }

        for ($length = mb_strlen($word) - 1; $length >= 3; $length--) {
            $stem = mb_substr($word, 0, $length);
            $replacement = $this->words[self::normalize($stem)] ?? null;

            if ($replacement !== null) {
                return [self::matchCase($stem, $replacement), mb_substr($word, $length)];
            }
        }

        return null;
    }

    private static function normalize(string $word): string
    {
        return str_replace(Alphabet::APOSTROPHE_VARIANTS, '‘', mb_strtolower($word));
    }

    private static function matchCase(string $original, string $replacement): string
    {
        if (mb_strlen($original) > 1 && mb_strtoupper($original) === $original) {
            return mb_strtoupper($replacement);
        }

        $first = mb_substr($original, 0, 1);
        if ($first !== mb_strtolower($first)) {
            return mb_strtoupper(mb_substr($replacement, 0, 1)).mb_substr($replacement, 1);
        }

        return $replacement;
    }
}
