<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Built-in exception dictionary
    |--------------------------------------------------------------------------
    |
    | The package ships with a curated list of words whose spelling cannot
    | be derived from the letter rules — mostly Russian loanwords where the
    | Cyrillic soft/hard sign (ь/ъ) is not written in Latin script:
    | kompyuter => компьютер, oktyabr => октябрь, rayon => район, ...
    |
    | See \NurbekJummayev\LaravelCyrillicLatin\Support\DefaultDictionary.
    | Your own entries below always take precedence over the built-ins.
    |
    */

    'use_default_dictionary' => true,

    /*
    |--------------------------------------------------------------------------
    | Word-level dictionaries
    |--------------------------------------------------------------------------
    |
    | Exceptional words that should not be transliterated letter-by-letter.
    | Keys are matched case-insensitively; the original word's capitalization
    | is restored automatically. Whole words only — suffixed forms fall back
    | to the letter rules.
    |
    | 'latin'    is used when converting Cyrillic -> Latin.
    | 'cyrillic' is used when converting Latin -> Cyrillic.
    |
    */

    'dictionaries' => [

        'latin' => [
            // 'цех' => 'sex', // example: Cyrillic word => Latin output
        ],

        'cyrillic' => [
            // 'sex' => 'цех', // example: Latin word => Cyrillic output
        ],

    ],

];
