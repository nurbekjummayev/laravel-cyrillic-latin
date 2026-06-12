<?php

/*
 * Cyrillic words whose official Latin spelling differs from the letter
 * rules. This list is short on purpose: Cyrillic is the richer script, so
 * the rules almost always produce the correct Latin form — even for
 * abbreviations ("12 янв. 2026" -> "12 yanv. 2026", "сент." -> "sent.").
 *
 * Keys are lowercase; capitalization of the original word is restored
 * automatically. Whole words only.
 */

return [

    // Official 1995 Latin orthography drops the я in these month names
    'сентябрь' => 'sentabr',
    'сентябр' => 'sentabr',
    'октябрь' => 'oktabr',
    'октябр' => 'oktabr',

];
