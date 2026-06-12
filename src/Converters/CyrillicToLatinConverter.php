<?php

declare(strict_types=1);

namespace NurbekJummayev\LaravelCyrillicLatin\Converters;

use NurbekJummayev\LaravelCyrillicLatin\Support\Alphabet;

final class CyrillicToLatinConverter extends ScriptConverter
{
    protected function wordPattern(): string
    {
        return '\p{Cyrillic}++';
    }

    /**
     * Uzbek suffix chain in Cyrillic: plural + possessive + case/particle,
     * e.g. лар, и, да, нинг, ларида, имизгача.
     */
    protected function suffixPattern(): string
    {
        return '~^(?:лар|лик|чи|ли|сиз)?(?:лар)?(?:им|инг|имиз|ингиз|си|и)?'
            .'(?:ми|ни|нинг|га|ка|қа|да|дан|гача|даги|дек|дай|ча)?$~u';
    }

    protected function convertWord(string $word): string
    {
        $result = $this->applySignBeforeERule($word);
        $result = $this->applyERule($result);
        $result = $this->applyTsRule($result);
        $result = $this->applySHSeparatorRule($result);
        $result = strtr($result, Alphabet::toLatinMap());

        // Abbreviations: ЧИРЧИҚ -> ChIRChIQ would be wrong, fix to CHIRCHIQ
        return $this->isAllUppercase($word) ? mb_strtoupper($result) : $result;
    }

    /**
     * ъ/ь before "е" is a separator, not a tutuq: the sign is dropped and
     * "е" becomes "ye": объект -> obyekt, съезд -> syezd, премьера -> premyera.
     */
    private function applySignBeforeERule(string $word): string
    {
        return (string) preg_replace_callback(
            '~[ъьЪЬ]([еЕ])~u',
            static fn (array $match): string => $match[1] === 'Е' ? 'Ye' : 'ye',
            $word
        );
    }

    /**
     * "е" at word start or after a vowel is spelled "ye": Етти -> Yetti,
     * elsewhere plain "e" (handled by the letter map).
     */
    private function applyERule(string $word): string
    {
        return (string) preg_replace_callback(
            '~(?:^|(?<=['.Alphabet::CYRILLIC_VOWELS.']))[еЕ]~u',
            static fn (array $match): string => $match[0] === 'Е' ? 'Ye' : 'ye',
            $word
        );
    }

    /**
     * "ц" at word start or after a consonant is spelled "s": цирк -> sirk,
     * акция -> aksiya; after a vowel "ts" (handled by the letter map):
     * доцент -> dotsent.
     */
    private function applyTsRule(string $word): string
    {
        return (string) preg_replace_callback(
            '~(?<!['.Alphabet::CYRILLIC_VOWELS.'])[цЦ]~u',
            static fn (array $match): string => $match[0] === 'Ц' ? 'S' : 's',
            $word
        );
    }

    /**
     * "с" + "ҳ" would read as the "sh" digraph in Latin, so an apostrophe
     * keeps them apart: Исҳоқ -> Is’hoq, асҳоб -> as’hob.
     */
    private function applySHSeparatorRule(string $word): string
    {
        return (string) preg_replace('~([сС])(?=[ҳҲ])~u', '$1’', $word);
    }
}
