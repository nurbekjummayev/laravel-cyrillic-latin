# Laravel Cyrillic ⇄ Latin

Uzbek Cyrillic ⇄ Latin transliteration package for Laravel.

- Context-aware rules, not blind character mapping:
  - `е → ye` at word start / after vowels (`Етти → Yetti`), `e` elsewhere
  - `ц → s` at word start / after consonants (`акция → aksiya`), `ts` after vowels (`доцент → dotsent`)
  - `e → э` at word start / after vowels (`ekologiya → экология`, `poeziya → поэзия`)
  - `ъ/ь` before `е` is a separator: `объект → obyekt`, `съезд → syezd`, `премьера → premyera`
  - `с + ҳ` is kept apart from the `sh` digraph: `Исҳоқ ⇄ Is’hoq`
  - digraphs `o‘ g‘ ch sh ya yo yu ts` in both directions, including word-final `tog‘ → тоғ`
  - tutuq belgisi: `ma’no ⇄ маъно`, `e’lon ⇄ эълон`
  - abbreviations stay ALL-CAPS: `ЧИРЧИҚ → CHIRCHIQ` (not `ChIRChIQ`)
- Accepts every common apostrophe variant: `‘ ’ ʻ ʼ '`
- **URLs, e-mail addresses, HTML tags and entities are never touched**
- Built-in exception dictionary with 200+ stems that break the rules:
  `kompyuter → компьютер`, `aksiya → акция`, `rayon → район` (not `раён`), ...
- **Suffix-aware stems**: one entry covers every Uzbek suffix chain —
  `kompyuterlarida → компьютерларида`, `rollar → роллар` (final `ь` dropped),
  effectively tens of thousands of word forms
- Word-level dictionary for your own exception words, with automatic case preservation
- Facade, `Str` macros, and a framework-free core

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require nurbekjummayev/laravel-cyrillic-latin
```

The service provider is auto-discovered.

Publish the config (optional, for custom dictionaries):

```bash
php artisan vendor:publish --tag=laravel-cyrillic-latin-config
```

## Usage

### Facade

```php
use NurbekJummayev\LaravelCyrillicLatin\Facades\Transliterator;

Transliterator::toLatin('Ассалому алайкум, Ўзбекистон!');
// "Assalomu alaykum, O‘zbekiston!"

Transliterator::toCyrillic("O'zbekiston — buyuk davlat");
// "Ўзбекистон — буюк давлат"

Transliterator::convert('Салом', 'latin');   // "Salom"
Transliterator::swap('Salom');               // "Салом" (auto-detects the script)
Transliterator::hasCyrillic('Салом');        // true
```

> Note: always import the facade with `use`. The package intentionally does not
> register a global `Transliterator` alias because PHP's `intl` extension
> already defines a global `\Transliterator` class.

### Str macros

```php
use Illuminate\Support\Str;

Str::toLatin('Тошкент шаҳри');   // "Toshkent shahri"
Str::toCyrillic('Samarqand');    // "Самарқанд"
```

### Dependency injection

```php
use NurbekJummayev\LaravelCyrillicLatin\Transliterator;

public function show(Transliterator $transliterator)
{
    return $transliterator->toLatin($post->title_cyrillic);
}
```

### Without Laravel

The core has no framework dependencies:

```php
use NurbekJummayev\LaravelCyrillicLatin\Transliterator;

$transliterator = new Transliterator();
$transliterator->toLatin('Фарғона');     // "Farg‘ona"
$transliterator->toCyrillic('Chirchiq'); // "Чирчиқ"
```

### HTML and URLs

Markup, links and e-mails are preserved as-is:

```php
Transliterator::toCyrillic('<b>Toshkent</b> haqida: https://example.uz yoki info@misol.uz');
// '<b>Тошкент</b> ҳақида: https://example.uz ёки info@misol.uz'
```

## Dictionary (exception words)

### Built-in exceptions

The Cyrillic soft/hard signs (`ь`/`ъ`) and the letter `ц` are not written in
Latin script, so the Cyrillic spelling of many loanwords cannot be
reconstructed by letter rules (`kompyuter` would become `компютер`, `aksiya`
would become `аксия`). The package ships a curated list of 200+ stems —
months, `ц`-words and common loanwords — in
[resources/dictionaries](resources/dictionaries) (plain PHP arrays, cached by
opcache):

```php
Transliterator::toCyrillic('kompyuter'); // "компьютер" (not "компютер")
Transliterator::toCyrillic('aksiya');    // "акция"     (not "аксия")
Transliterator::toCyrillic('konsert');   // "концерт"   (not "консерт")
Transliterator::toCyrillic('rayon');     // "район"     (not "раён")
Transliterator::toCyrillic('mer');       // "мэр"       (not "мер")
```

Every stem is **suffix-aware** — the engine recognizes Uzbek suffix chains
(`lar`, `i`, `ning`, `da`, `gacha`, ...) and converts them by the letter
rules while taking the stem from the dictionary. A word-final `ь` is dropped
before a suffix, as Uzbek Cyrillic orthography requires:

```php
Transliterator::toCyrillic('kompyuterlarida'); // "компьютерларида"
Transliterator::toCyrillic('aksiyalarga');     // "акцияларга"
Transliterator::toCyrillic('rollar');          // "роллар" (роль + лар)
```

Lookalike words are protected: a stem only matches when the remainder is a
valid suffix chain, so `rolik → ролик` (not роль + ik) and
`seriya → серия` (not сэр + iya).

Disable the built-ins with `'use_default_dictionary' => false` in the config.

Abbreviations need no special handling — the letter rules already produce
the correct result and dots are left untouched:

```php
Transliterator::toLatin('12 янв. 2026'); // "12 yanv. 2026"
```

### Your own exceptions

Define them in `config/cyrillic-latin.php` (keys are matched
case-insensitively; the original word's capitalization is restored
automatically). Your entries always override the built-ins:

```php
'dictionaries' => [
    'latin' => [
        'цех' => 'sex',
    ],
    'cyrillic' => [
        'sex' => 'цех',
    ],
],
```

```php
Transliterator::toLatin('Цех');  // "Sex"
Transliterator::toLatin('ЦЕХ');  // "SEX"
```

## Architecture

```
src/
├── Transliterator.php                ← public API: toLatin, toCyrillic, convert, swap
├── CyrillicLatinServiceProvider.php  ← spatie/laravel-package-tools provider
├── Facades/Transliterator.php
├── Contracts/
│   └── Converter.php                 ← convert(string $text): string
├── Support/
│   ├── Alphabet.php                  ← single source of truth for letter mappings
│   └── Dictionary.php                ← exception-word lookup with case preservation
└── Converters/
    ├── ScriptConverter.php           ← word walker; skips HTML/URLs/e-mails
    ├── CyrillicToLatinConverter.php
    └── LatinToCyrillicConverter.php
```

Both directions are derived from one lowercase letter table in
`Support/Alphabet.php` — uppercase forms, ALL-CAPS digraphs and apostrophe
variants are generated from it, so the two directions can never drift apart.

## Testing

```bash
composer test
```

## License

MIT
