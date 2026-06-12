<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Converters;

use NurbekJummayev\LaravelCyrillicLatin\Contracts\Converter;
use NurbekJummayev\LaravelCyrillicLatin\Support\Dictionary;

/**
 * Walks the text word by word: fragments matched by SKIP_PATTERN are left
 * untouched, every word of the source script goes through the dictionary
 * first (exact, then stem + suffix) and the letter rules after.
 */
abstract class ScriptConverter implements Converter
{
    /**
     * Fragments that must never be transliterated:
     * HTML tags, HTML entities, URLs and e-mail addresses.
     */
    private const string SKIP_PATTERN = '(?:'
        .'<[A-Za-z\/!][^<>]*+>'                          // HTML tags and comments
        .'|&\#?+[A-Za-z0-9]++;'                          // HTML entities: &nbsp; &#1040;
        .'|[A-Za-z][A-Za-z0-9+.-]*+:\/\/[^\s<>"\'`]++'   // URLs with a scheme
        .'|www\.[^\s<>"\'`]++'                           // bare www. URLs
        .'|[\w.+-]++@[\w-]++(?:\.[\w-]++)++'             // e-mail addresses
        .')(*SKIP)(*FAIL)';

    public function __construct(protected readonly Dictionary $dictionary) {}

    public function convert(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $result = preg_replace_callback(
            '~'.self::SKIP_PATTERN.'|'.$this->wordPattern().'~u',
            fn (array $match): string => $this->dictionary->lookup($match[0])
                ?? $this->convertSuffixedWord($match[0])
                ?? $this->convertWord($match[0]),
            $text
        );

        // preg_* returns null on invalid UTF-8 input — return the text as is.
        return $result ?? $text;
    }

    /**
     * Dictionary stem + Uzbek suffix chain: kompyuterlarida = kompyuter
     * (dictionary) + larida (letter rules). Returns null when the word does
     * not decompose into a known stem and a valid suffix chain, so words
     * like "rolik" or "seriya" fall through to the letter rules instead of
     * matching the stems "rol"/"ser".
     */
    private function convertSuffixedWord(string $word): ?string
    {
        $stem = $this->dictionary->lookupStem($word);

        if ($stem === null) {
            return null;
        }

        [$replacement, $suffix] = $stem;

        if (! preg_match($this->suffixPattern(), $suffix)) {
            return null;
        }

        return $this->attachSuffix($replacement, $this->convertWord($suffix));
    }

    /**
     * Regex (without delimiters) matching one word of the source script.
     */
    abstract protected function wordPattern(): string;

    /**
     * Full regex matching a valid Uzbek suffix chain in the source script.
     */
    abstract protected function suffixPattern(): string;

    /**
     * Convert a single word; the dictionary has already been consulted.
     */
    abstract protected function convertWord(string $word): string;

    /**
     * Join a dictionary stem with an already-converted suffix.
     */
    protected function attachSuffix(string $replacement, string $suffix): string
    {
        return $replacement.$suffix;
    }

    protected function isAllUppercase(string $word): bool
    {
        return mb_strlen($word) > 1
            && mb_strtoupper($word) === $word
            && mb_strtolower($word) !== $word;
    }
}
