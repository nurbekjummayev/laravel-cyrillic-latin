<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Tests\Unit;

use NurbekJummayev\LaravelCyrillicLatin\Support\Dictionary;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    public function test_empty_dictionary_returns_null(): void
    {
        $dictionary = new Dictionary();

        $this->assertNull($dictionary->lookup('kompyuter'));
        $this->assertNull($dictionary->lookupStem('kompyuterlar'));
    }

    public function test_exact_lookup(): void
    {
        $dictionary = new Dictionary(['kompyuter' => 'компьютер']);

        $this->assertSame('компьютер', $dictionary->lookup('kompyuter'));
        $this->assertNull($dictionary->lookup('boshqa'));
    }

    public function test_lookup_is_case_insensitive_but_restores_case(): void
    {
        $dictionary = new Dictionary(['kompyuter' => 'компьютер']);

        $this->assertSame('компьютер', $dictionary->lookup('kompyuter'));
        $this->assertSame('Компьютер', $dictionary->lookup('Kompyuter'));
        $this->assertSame('КОМПЬЮТЕР', $dictionary->lookup('KOMPYUTER'));
    }

    public function test_lookup_is_apostrophe_variant_insensitive(): void
    {
        $dictionary = new Dictionary(['oʻrik' => 'ўрик']);

        foreach (["o'rik", 'o‘rik', 'o’rik', 'oʻrik', 'oʼrik'] as $variant) {
            $this->assertSame('ўрик', $dictionary->lookup($variant), "variant: {$variant}");
        }
    }

    public function test_stem_lookup_returns_replacement_and_suffix(): void
    {
        $dictionary = new Dictionary(['kompyuter' => 'компьютер']);

        $this->assertSame(['компьютер', 'lar'], $dictionary->lookupStem('kompyuterlar'));
        $this->assertSame(['компьютер', 'larida'], $dictionary->lookupStem('kompyuterlarida'));
    }

    public function test_stem_lookup_picks_longest_stem(): void
    {
        $dictionary = new Dictionary(['rol' => 'рол', 'roli' => 'роли']);

        // longest proper prefix wins: "rolida" -> "roli" (4) + "da", not "rol" (3) + "ida"
        $this->assertSame(['роли', 'da'], $dictionary->lookupStem('rolida'));
    }

    public function test_stem_lookup_requires_minimum_stem_length(): void
    {
        $dictionary = new Dictionary(['ce' => 'це']);

        // stems shorter than 3 characters are never matched
        $this->assertNull($dictionary->lookupStem('celar'));
    }

    public function test_stem_lookup_ignores_exact_match(): void
    {
        $dictionary = new Dictionary(['kompyuter' => 'компьютер']);

        // a whole-word match is the job of lookup(), not lookupStem()
        $this->assertNull($dictionary->lookupStem('kompyuter'));
    }

    public function test_numeric_keys_are_coerced_to_strings(): void
    {
        $dictionary = new Dictionary(['100' => 'юз']);

        // an all-digit key counts as "all uppercase", so the replacement is
        // uppercased too — the point here is that the integer key is coerced
        // to a string in the constructor and is therefore found at all.
        $this->assertSame('ЮЗ', $dictionary->lookup('100'));
    }
}
