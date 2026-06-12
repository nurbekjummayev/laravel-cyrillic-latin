<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin;

use InvalidArgumentException;
use NurbekJummayev\LaravelCyrillicLatin\Converters\CyrillicToLatinConverter;
use NurbekJummayev\LaravelCyrillicLatin\Converters\LatinToCyrillicConverter;
use NurbekJummayev\LaravelCyrillicLatin\Support\DefaultDictionary;
use NurbekJummayev\LaravelCyrillicLatin\Support\Dictionary;

class Transliterator
{
    private ?CyrillicToLatinConverter $cyrillicToLatin = null;

    private ?LatinToCyrillicConverter $latinToCyrillic = null;

    /**
     * @param  array<string, string>  $latinDictionary  word overrides used when converting to Latin
     * @param  array<string, string>  $cyrillicDictionary  word overrides used when converting to Cyrillic
     * @param  bool  $useDefaultDictionary  apply the built-in exception words (user entries always win)
     */
    public function __construct(
        private readonly array $latinDictionary = [],
        private readonly array $cyrillicDictionary = [],
        private readonly bool $useDefaultDictionary = true,
    ) {}

    /**
     * Convert Uzbek Cyrillic text to Latin script.
     */
    public function toLatin(string $text): string
    {
        $this->cyrillicToLatin ??= new CyrillicToLatinConverter(new Dictionary(
            $this->useDefaultDictionary
                ? [...DefaultDictionary::toLatin(), ...$this->latinDictionary]
                : $this->latinDictionary
        ));

        return $this->cyrillicToLatin->convert($text);
    }

    /**
     * Convert Uzbek Latin text to Cyrillic script.
     */
    public function toCyrillic(string $text): string
    {
        $this->latinToCyrillic ??= new LatinToCyrillicConverter(new Dictionary(
            $this->useDefaultDictionary
                ? [...DefaultDictionary::toCyrillic(), ...$this->cyrillicDictionary]
                : $this->cyrillicDictionary
        ));

        return $this->latinToCyrillic->convert($text);
    }

    /**
     * Convert text to the given script: "latin" or "cyrillic".
     */
    public function convert(string $text, string $to = 'latin'): string
    {
        return match ($to) {
            'latin', 'lat', 'uz' => $this->toLatin($text),
            'cyrillic', 'cyr', 'kr' => $this->toCyrillic($text),
            default => throw new InvalidArgumentException(
                "Unsupported target script [{$to}]. Use \"latin\" or \"cyrillic\"."
            ),
        };
    }

    /**
     * Detect the dominant script and convert to the opposite one.
     */
    public function swap(string $text): string
    {
        return $this->hasCyrillic($text) ? $this->toLatin($text) : $this->toCyrillic($text);
    }

    public function hasCyrillic(string $text): bool
    {
        return (bool) preg_match('~\p{Cyrillic}~u', $text);
    }

    public function hasLatin(string $text): bool
    {
        return (bool) preg_match('~\p{Latin}~u', $text);
    }
}
