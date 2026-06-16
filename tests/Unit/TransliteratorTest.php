<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Tests\Unit;

use NurbekJummayev\LaravelCyrillicLatin\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TransliteratorTest extends TestCase
{
    private Transliterator $transliterator;

    protected function setUp(): void
    {
        $this->transliterator = new Transliterator();
    }

    /** @return array<string, array{string, string}> */
    public static function cyrillicToLatinProvider(): array
    {
        return [
            'greeting' => ['Ассалому алайкум, Ўзбекистон!', 'Assalomu alaykum, O‘zbekiston!'],
            'city with h' => ['Тошкент шаҳри', 'Toshkent shahri'],
            'q letter' => ['Самарқанд', 'Samarqand'],
            'g‘ letter' => ['Фарғона', 'Farg‘ona'],
            'ch letter' => ['Чирчиқ', 'Chirchiq'],
            'sh letter' => ['Шаҳрисабз', 'Shahrisabz'],
            'е at word start' => ['Етти', 'Yetti'],
            'е after vowel' => ['туен', 'tuyen'],
            'э letter' => ['экология', 'ekologiya'],
            'э after vowel' => ['поэзия', 'poeziya'],
            'ц at word start' => ['цирк', 'sirk'],
            'ц after consonant' => ['акция', 'aksiya'],
            'ц after vowel' => ['доцент', 'dotsent'],
            'tutuq belgisi' => ['маъно', 'ma’no'],
            'эъ word' => ['эълон', 'e’lon'],
            'soft sign dropped' => ['апрель', 'aprel'],
            'щ letter' => ['борщ', 'borsh'],
            'word-final ғ' => ['тоғ', 'tog‘'],
            'ў inside word' => ['йўл', 'yo‘l'],
            'all-caps abbreviation' => ['ЧИРЧИҚ', 'CHIRCHIQ'],
            'all-caps with я' => ['ЯПОНИЯ', 'YAPONIYA'],
            'mixed-case word' => ['ТошДУ', 'ToshDU'],
            'numbers kept' => ['2026-йилда 100 та мактаб қурилди.', '2026-yilda 100 ta maktab qurildi.'],
            'latin words kept' => ['Laravel — энг яхши framework', 'Laravel — eng yaxshi framework'],
            'ъ before е' => ['объект', 'obyekt'],
            'ъ before е at syllable start' => ['съезд', 'syezd'],
            'ь before е' => ['премьера', 'premyera'],
            'ью cluster' => ['интервью', 'intervyu'],
            'с before ҳ gets separator' => ['Исҳоқ', 'Is’hoq'],
            'september official spelling' => ['сентябрь', 'sentabr'],
            'october official spelling' => ['октябрь', 'oktabr'],
        ];
    }

    #[DataProvider('cyrillicToLatinProvider')]
    public function test_to_latin(string $cyrillic, string $latin): void
    {
        $this->assertSame($latin, $this->transliterator->toLatin($cyrillic));
    }

    /** @return array<string, array{string, string}> */
    public static function latinToCyrillicProvider(): array
    {
        return [
            'city with sh and h' => ['Toshkent shahri', 'Тошкент шаҳри'],
            'q letter' => ['Samarqand', 'Самарқанд'],
            'o‘ letter' => ['O‘zbekiston', 'Ўзбекистон'],
            'g‘ letter' => ['Farg‘ona', 'Фарғона'],
            'ch letter' => ['Chirchiq', 'Чирчиқ'],
            'ye at word start' => ['Yetti', 'Етти'],
            'bare e at word start' => ['ekologiya', 'экология'],
            'bare e after vowel' => ['poeziya', 'поэзия'],
            'e after consonant' => ['meva', 'мева'],
            'ts after vowel' => ['dotsent', 'доцент'],
            'tutuq belgisi' => ['ma’no', 'маъно'],
            'eъ word' => ['e’lon', 'эълон'],
            'word-final g‘' => ['tog‘', 'тоғ'],
            'yo‘ digraph' => ['yo‘l', 'йўл'],
            'yo digraph' => ['yoz', 'ёз'],
            'all-caps abbreviation' => ['CHIRCHIQ', 'ЧИРЧИҚ'],
            'all-caps yo‘' => ['YO‘L', 'ЙЎЛ'],
            'all-caps ye' => ['YEVROPA', 'ЕВРОПА'],
            's’h separator is not a tutuq' => ['Is’hoq', 'Исҳоқ'],
            's’h separator straight apostrophe' => ["as'hob", 'асҳоб'],
        ];
    }

    #[DataProvider('latinToCyrillicProvider')]
    public function test_to_cyrillic(string $latin, string $cyrillic): void
    {
        $this->assertSame($cyrillic, $this->transliterator->toCyrillic($latin));
    }

    /** @return array<string, array{string, string}> */
    public static function defaultDictionaryProvider(): array
    {
        return [
            'soft sign restored' => ['kompyuter', 'компьютер'],
            'month with soft sign' => ['oktyabr', 'октябрь'],
            'official month spelling' => ['oktabr', 'октябрь'],
            'hard sign restored' => ['obyekt', 'объект'],
            'съезд restored' => ['syezd', 'съезд'],
            'премьера restored' => ['premyera', 'премьера'],
            'й+о, not ё' => ['rayon', 'район'],
            'mayor is not маёр' => ['mayor', 'майор'],
            'capitalization preserved' => ['Kompyuter', 'Компьютер'],
            'э after consonant' => ['mer', 'мэр'],
            'э after vowel + soft sign' => ['duel', 'дуэль'],
            'ье cluster' => ['baryer', 'барьер'],
            'ью in pult' => ['pult', 'пульт'],
            'yogurt is not ёгурт' => ['yogurt', 'йогурт'],
            'shampun soft sign' => ['shampun', 'шампунь'],
            'model soft sign' => ['model', 'модель'],
            'ц after consonant' => ['aksiya', 'акция'],
            'ц in stansiya' => ['stansiya', 'станция'],
            'ц in konsert' => ['konsert', 'концерт'],
            'ц in funksiya' => ['funksiya', 'функция'],
            'ц at word start' => ['sirk', 'цирк'],
            'сц cluster' => ['ssenariy', 'сценарий'],
            'film soft sign' => ['film', 'фильм'],
            'rezultat soft sign' => ['rezultat', 'результат'],
        ];
    }

    public function test_abbreviations_with_dots_convert_correctly(): void
    {
        $this->assertSame('12 yanv. 2026', $this->transliterator->toLatin('12 янв. 2026'));
        $this->assertSame('5 fev. va 20 dek.', $this->transliterator->toLatin('5 фев. ва 20 дек.'));
    }

    /** @return array<string, array{string, string}> */
    public static function suffixedStemProvider(): array
    {
        return [
            'plural' => ['kompyuterlar', 'компьютерлар'],
            'plural + possessive + case' => ['kompyuterlarida', 'компьютерларида'],
            'film plural' => ['filmlar', 'фильмлар'],
            'final ь dropped before suffix' => ['rollar', 'роллар'],
            'final ь dropped before vowel suffix' => ['roli', 'роли'],
            'month + case' => ['oktyabrda', 'октябрда'],
            'ц stem + suffix' => ['aksiyalar', 'акциялар'],
            'stansiya + ga' => ['stansiyaga', 'станцияга'],
            'all-caps stem + suffix' => ['OKTYABRDA', 'ОКТЯБРДА'],
        ];
    }

    #[DataProvider('suffixedStemProvider')]
    public function test_dictionary_stem_with_uzbek_suffixes(string $latin, string $cyrillic): void
    {
        $this->assertSame($cyrillic, $this->transliterator->toCyrillic($latin));
    }

    public function test_stem_matching_does_not_create_false_positives(): void
    {
        // "rolik" is not роль + ik, "seriya" is not сэр + iya, "mergan" is not мэр + gan
        $this->assertSame('ролик', $this->transliterator->toCyrillic('rolik'));
        $this->assertSame('серия', $this->transliterator->toCyrillic('seriya'));
        $this->assertSame('мерган', $this->transliterator->toCyrillic('mergan'));
        $this->assertSame('пулсиз', $this->transliterator->toCyrillic('pulsiz'));
    }

    public function test_suffixed_stem_in_cyrillic_to_latin_direction(): void
    {
        $this->assertSame('sentabrda', $this->transliterator->toLatin('сентябрда'));
        $this->assertSame('oktabrgacha', $this->transliterator->toLatin('октябргача'));
    }

    #[DataProvider('defaultDictionaryProvider')]
    public function test_default_dictionary(string $latin, string $cyrillic): void
    {
        $this->assertSame($cyrillic, $this->transliterator->toCyrillic($latin));
    }

    public function test_default_dictionary_can_be_disabled(): void
    {
        $transliterator = new Transliterator(useDefaultDictionary: false);

        $this->assertSame('раён', $transliterator->toCyrillic('rayon'));
        $this->assertSame('sentyabr', $transliterator->toLatin('сентябрь'));
    }

    public function test_user_dictionary_overrides_default(): void
    {
        $transliterator = new Transliterator(cyrillicDictionary: ['rayon' => 'туман']);

        $this->assertSame('туман', $transliterator->toCyrillic('rayon'));
        $this->assertSame('компьютер', $transliterator->toCyrillic('kompyuter'));
    }

    public function test_to_cyrillic_accepts_all_apostrophe_variants(): void
    {
        foreach (["O'zbekiston", 'O‘zbekiston', 'O’zbekiston', 'Oʻzbekiston', 'Oʼzbekiston'] as $variant) {
            $this->assertSame('Ўзбекистон', $this->transliterator->toCyrillic($variant), "variant: {$variant}");
        }

        foreach (["ma'no", 'ma’no', 'maʼno'] as $variant) {
            $this->assertSame('маъно', $this->transliterator->toCyrillic($variant), "variant: {$variant}");
        }
    }

    public function test_quotes_around_words_are_not_treated_as_apostrophes(): void
    {
        $this->assertSame("'салом' деди", $this->transliterator->toCyrillic("'salom' dedi"));
    }

    public function test_urls_and_emails_are_preserved(): void
    {
        $this->assertSame(
            'Sayt https://example.uz/sahifa?q=salom manzilda',
            $this->transliterator->toLatin('Сайт https://example.uz/sahifa?q=salom манзилда')
        );

        $this->assertSame(
            'Сайтимиз www.misol.uz да',
            $this->transliterator->toCyrillic('Saytimiz www.misol.uz da')
        );

        $this->assertSame(
            'Мурожаат учун: info@misol.uz',
            $this->transliterator->toCyrillic('Murojaat uchun: info@misol.uz')
        );
    }

    public function test_html_markup_is_preserved(): void
    {
        $this->assertSame(
            '<a href="https://t.me/kanal" class="link">Toshkent</a>&nbsp;shahri',
            $this->transliterator->toLatin('<a href="https://t.me/kanal" class="link">Тошкент</a>&nbsp;шаҳри')
        );

        $this->assertSame(
            '<b>Тошкент</b> — пойтахт',
            $this->transliterator->toCyrillic('<b>Toshkent</b> — poytaxt')
        );
    }

    public function test_dictionary_overrides_word_and_preserves_case(): void
    {
        $transliterator = new Transliterator(
            latinDictionary: ['цех' => 'sex'],
            cyrillicDictionary: ['sex' => 'цех'],
        );

        $this->assertSame('sex', $transliterator->toLatin('цех'));
        $this->assertSame('Sex', $transliterator->toLatin('Цех'));
        $this->assertSame('SEX', $transliterator->toLatin('ЦЕХ'));
        $this->assertSame('цех', $transliterator->toCyrillic('sex'));
    }

    public function test_convert_with_target_script(): void
    {
        $this->assertSame('Salom', $this->transliterator->convert('Салом', 'latin'));
        $this->assertSame('Салом', $this->transliterator->convert('Salom', 'cyrillic'));
    }

    public function test_convert_defaults_to_latin(): void
    {
        $this->assertSame('Salom', $this->transliterator->convert('Салом'));
    }

    /** @return array<string, array{string, string, string}> */
    public static function convertAliasProvider(): array
    {
        return [
            'latin alias lat' => ['Салом', 'lat', 'Salom'],
            'latin alias uz' => ['Салом', 'uz', 'Salom'],
            'cyrillic alias cyr' => ['Salom', 'cyr', 'Салом'],
            'cyrillic alias kr' => ['Salom', 'kr', 'Салом'],
        ];
    }

    #[DataProvider('convertAliasProvider')]
    public function test_convert_accepts_script_aliases(string $input, string $to, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->convert($input, $to));
    }

    public function test_converts_multiline_paragraph(): void
    {
        $cyrillic = "Тошкент — Ўзбекистон пойтахти.\nБу шаҳарда 2,5 миллион киши яшайди.";
        $latin = "Toshkent — O‘zbekiston poytaxti.\nBu shaharda 2,5 million kishi yashaydi.";

        $this->assertSame($latin, $this->transliterator->toLatin($cyrillic));
        $this->assertSame($cyrillic, $this->transliterator->toCyrillic($latin));
    }

    public function test_convert_with_unknown_target_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transliterator->convert('Salom', 'greek');
    }

    public function test_swap_detects_script(): void
    {
        $this->assertSame('Salom', $this->transliterator->swap('Салом'));
        $this->assertSame('Салом', $this->transliterator->swap('Salom'));
    }

    public function test_script_detection(): void
    {
        $this->assertTrue($this->transliterator->hasCyrillic('Салом'));
        $this->assertFalse($this->transliterator->hasCyrillic('Salom'));
        $this->assertTrue($this->transliterator->hasLatin('Salom'));
        $this->assertFalse($this->transliterator->hasLatin('Салом'));
    }

    public function test_empty_string(): void
    {
        $this->assertSame('', $this->transliterator->toLatin(''));
        $this->assertSame('', $this->transliterator->toCyrillic(''));
    }

    public function test_round_trip(): void
    {
        $latin = 'Toshkent — O‘zbekiston poytaxti';

        $this->assertSame($latin, $this->transliterator->toLatin($this->transliterator->toCyrillic($latin)));
    }
}
