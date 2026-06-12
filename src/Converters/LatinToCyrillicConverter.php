<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Converters;

use NurbekJummayev\LaravelCyrillicLatin\Support\Alphabet;

final class LatinToCyrillicConverter extends ScriptConverter
{
    /**
     * A word starts with a letter; an apostrophe belongs to the word when it
     * is followed by a letter (tutuq: ma’no, digraphs: o‘zbek) or preceded
     * by o/g (word-final digraph: tog‘) — so quotes around words stay quotes.
     */
    protected function wordPattern(): string
    {
        $apostrophes = preg_quote(implode('', Alphabet::APOSTROPHE_VARIANTS), '~');

        return '\p{Latin}(?:\p{Latin}|['.$apostrophes.'](?=\p{Latin})|(?<=[ogOG])['.$apostrophes.'])*+';
    }

    /**
     * Uzbek suffix chain in Latin: plural + possessive + case/particle,
     * e.g. lar, i, da, ning, larida, imizgacha.
     */
    protected function suffixPattern(): string
    {
        return '~^(?:lar|lik|chi|li|siz)?(?:lar)?(?:im|ing|imiz|ingiz|si|i)?'
            .'(?:mi|ni|ning|ga|ka|qa|da|dan|gacha|dagi|dek|day|cha)?$~u';
    }

    protected function convertWord(string $word): string
    {
        $result = $this->applySHSeparatorRule($word);
        $result = $this->applyERule($result);

        return strtr($result, Alphabet::toCyrillicMap());
    }

    /**
     * A word-final soft sign is dropped before a suffix in Uzbek Cyrillic:
     * роль + лар = роллар, октябрь + да = октябрда. Internal soft signs
     * stay: компьютер + да = компьютерда.
     */
    protected function attachSuffix(string $replacement, string $suffix): string
    {
        return (string) preg_replace('~[ьЬ]$~u', '', $replacement).$suffix;
    }

    /**
     * Bare "e" at word start or after a vowel becomes "э" (ekologiya ->
     * экология, poeziya -> поэзия); "ye" and "e" after a consonant become
     * "е" via the letter map.
     */
    private function applyERule(string $word): string
    {
        return (string) preg_replace_callback(
            '~(?:^|(?<=['.Alphabet::LATIN_VOWELS.']))[eE]~u',
            static fn (array $match): string => $match[0] === 'E' ? 'Э' : 'э',
            $word
        );
    }

    /**
     * The apostrophe in "s’h" separates the letters so they don't read as
     * the "sh" digraph — it is not a tutuq: Is’hoq -> Исҳоқ, as’hob -> асҳоб.
     */
    private function applySHSeparatorRule(string $word): string
    {
        $apostrophes = preg_quote(implode('', Alphabet::APOSTROPHE_VARIANTS), '~');

        return (string) preg_replace_callback(
            '~([sS])['.$apostrophes.']([hH])~u',
            static fn (array $match): string => ($match[1] === 'S' ? 'С' : 'с').($match[2] === 'H' ? 'Ҳ' : 'ҳ'),
            $word
        );
    }
}
