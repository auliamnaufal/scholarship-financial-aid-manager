<?php

namespace App\Support;

/**
 * Semesters are stored as "2026-1" (odd semester starting in 2026) or
 * "2026-2" (even semester, early the following year). Forms offer a short
 * list to pick from, so the same semester is never typed two different ways
 * and the one-application-per-semester rule cannot be dodged by a typo.
 */
class Semester
{
    /** What the database accepts: any year, odd or even. */
    public const PATTERN = '/^20\d{2}-[12]$/';

    /**
     * @param  string|null  $include  A semester already on record, kept in the list
     *                                even when it has fallen outside the window.
     * @return array<string, string> value => label, from last year to next year.
     */
    public static function options(?int $year = null, ?string $include = null): array
    {
        $year ??= (int) date('Y');
        $options = [];

        for ($y = $year - 1; $y <= $year + 1; $y++) {
            foreach ([1, 2] as $term) {
                $options["{$y}-{$term}"] = self::label("{$y}-{$term}");
            }
        }

        if ($include && preg_match(self::PATTERN, $include)) {
            $options[$include] ??= self::label($include);
            ksort($options);
        }

        return $options;
    }

    /** "2026-1" becomes "2026-1 (Ganjil)". Anything unrecognised is returned as is. */
    public static function label(string $value): string
    {
        if (! preg_match(self::PATTERN, $value)) {
            return $value;
        }

        return $value.' ('.(str_ends_with($value, '-1') ? __('Odd') : __('Even')).')';
    }

    /** The semester running today: odd from August, even from February. */
    public static function current(): string
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        return match (true) {
            $month >= 8 => "{$year}-1",
            default => ($year - 1).'-2',
        };
    }

    /** The validation both semester fields share. */
    public static function rules(): array
    {
        return ['required', 'string', 'regex:'.self::PATTERN];
    }
}
