<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Tests\Unit;

use NurbekJummayev\LaravelCyrillicLatin\Support\Alphabet;
use PHPUnit\Framework\TestCase;

class AlphabetTest extends TestCase
{
    public function test_to_latin_map_has_lower_and_upper_pairs(): void
    {
        $map = Alphabet::toLatinMap();

        $this->assertSame('sh', $map['ш']);
        $this->assertSame('Sh', $map['Ш']);
        $this->assertSame('o‘', $map['ў']);
        $this->assertSame('O‘', $map['Ў']);
        $this->assertSame('q', $map['қ']);
        $this->assertSame('Q', $map['Қ']);
    }

    public function test_to_latin_map_is_memoized(): void
    {
        $this->assertSame(Alphabet::toLatinMap(), Alphabet::toLatinMap());
    }

    public function test_to_cyrillic_map_inverts_pairs_first_winner(): void
    {
        $map = Alphabet::toCyrillicMap();

        // ш/щ both map to "sh"; ш was declared first, so it wins the reverse map
        $this->assertSame('ш', $map['sh']);
        // е/э both map to "e"; е wins
        $this->assertSame('е', $map['e']);
    }

    public function test_to_cyrillic_map_includes_reverse_extras(): void
    {
        $map = Alphabet::toCyrillicMap();

        $this->assertSame('е', $map['ye']);
        $this->assertSame('йў', $map['yo‘']);
    }

    public function test_to_cyrillic_map_has_all_apostrophe_variants(): void
    {
        $map = Alphabet::toCyrillicMap();

        foreach (["o'", 'o‘', 'o’', 'oʻ', 'oʼ'] as $variant) {
            $this->assertSame('ў', $map[$variant], "variant: {$variant}");
        }
    }

    public function test_to_cyrillic_map_has_capitalized_and_all_caps_digraphs(): void
    {
        $map = Alphabet::toCyrillicMap();

        $this->assertSame('Ч', $map['Ch']);
        $this->assertSame('Ч', $map['CH']);
        $this->assertSame('Ё', $map['Yo']);
        $this->assertSame('Ё', $map['YO']);
    }

    public function test_to_cyrillic_map_is_memoized(): void
    {
        $this->assertSame(Alphabet::toCyrillicMap(), Alphabet::toCyrillicMap());
    }
}
