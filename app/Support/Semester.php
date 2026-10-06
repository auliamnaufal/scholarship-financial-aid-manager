<?php

namespace App\Support;

use App\Models\StudentProfile;
use Illuminate\Support\Carbon;

/**
 * A semester is the student's own semester number, 1 to 7, stored as the
 * plain number ("5") and shown as "Semester 5". Forms offer the numbers in a
 * dropdown, so the same semester is never typed two different ways and the
 * one-application-per-semester rule cannot be dodged by a typo.
 */
class Semester
{
    /** The highest semester a form offers. */
    public const MAX = 7;

    /** What new records may hold. */
    public const PATTERN = '/^[1-7]$/';

    /**
     * @param  string|null  $include  A value already on record, kept in the list
     *                                even when it is not one of the numbers above.
     * @return array<string, string> value => label
     */
    public static function options(?string $include = null): array
    {
        $options = [];

        for ($n = 1; $n <= self::MAX; $n++) {
            $options[(string) $n] = self::label((string) $n);
        }

        if ($include !== null && $include !== '') {
            $options[$include] ??= self::label($include);
        }

        return $options;
    }

    /** "5" becomes "Semester 5", also for a number from before the limit. Anything else is returned as is. */
    public static function label(string $value): string
    {
        return preg_match('/^\d{1,2}$/', $value) ? __('Semester').' '.$value : $value;
    }

    /**
     * The semester a student is probably in now, counted from the year they
     * enrolled: the odd semester starts in August, the even one in February.
     * Used as the default choice on the apply form.
     */
    public static function forProfile(?StudentProfile $profile, ?Carbon $now = null): string
    {
        $now ??= now();

        if (! $profile?->year_enrolled) {
            return '1';
        }

        $semester = ($now->year - (int) $profile->year_enrolled) * 2 + ($now->month >= 8 ? 1 : 0);

        return (string) max(1, min(self::MAX, $semester));
    }

    /** The validation both semester fields share. */
    public static function rules(): array
    {
        return ['required', 'string', 'regex:'.self::PATTERN];
    }
}
