<?php

namespace App\Support;

use App\Models\Program;
use App\Models\StudentProfile;

/**
 * How well a student's biodata fits a scholarship, judged on the same two
 * thresholds the application form enforces (minimum GPA, maximum family
 * income) so the badge on the listing never promises what the form refuses.
 */
class Eligibility
{
    public const ELIGIBLE = 'eligible';

    public const INELIGIBLE = 'ineligible';

    /** The biodata lacks a figure the scholarship needs to judge by. */
    public const INCOMPLETE = 'incomplete';

    public const APPLIED = 'applied';

    /** The biodata fits, but the application rules (ApplyRules) stand in the way. */
    public const BLOCKED = 'blocked';

    /**
     * @return array{state: string, label: string, checks: list<array{ok: ?bool, text: string}>}
     *               `ok` is null for a check that cannot be made yet.
     */
    public static function check(Program $program, ?StudentProfile $profile, bool $alreadyApplied = false, ?string $blockedReason = null): array
    {
        $checks = [];

        if ($profile === null) {
            $checks[] = ['ok' => null, 'text' => 'Biodata Anda belum diisi.'];
        } else {
            if ($program->min_gpa !== null) {
                $gpa = (float) $profile->gpa;
                $minimum = (float) $program->min_gpa;

                $checks[] = $gpa >= $minimum
                    ? ['ok' => true, 'text' => 'IPK Anda '.self::gpa($gpa).' memenuhi syarat minimal '.self::gpa($minimum).'.']
                    : ['ok' => false, 'text' => 'IPK Anda '.self::gpa($gpa).' belum mencapai syarat minimal '.self::gpa($minimum).'.'];
            }

            if ($program->max_family_income !== null) {
                $maximum = (float) $program->max_family_income;

                if ($profile->family_income === null) {
                    $checks[] = ['ok' => null, 'text' => 'Isi penghasilan keluarga Anda di biodata untuk diperiksa.'];
                } else {
                    $income = (float) $profile->family_income;

                    $checks[] = $income <= $maximum
                        ? ['ok' => true, 'text' => 'Penghasilan keluarga Anda '.Money::rupiah($income).' berada dalam batas '.Money::rupiah($maximum).'.']
                        : ['ok' => false, 'text' => 'Penghasilan keluarga Anda '.Money::rupiah($income).' melebihi batas '.Money::rupiah($maximum).'.'];
                }
            }
        }

        $state = match (true) {
            $alreadyApplied => self::APPLIED,
            collect($checks)->contains(fn ($check) => $check['ok'] === null) => self::INCOMPLETE,
            collect($checks)->contains(fn ($check) => $check['ok'] === false) => self::INELIGIBLE,
            $blockedReason !== null => self::BLOCKED,
            default => self::ELIGIBLE,
        };

        if ($blockedReason !== null && ! $alreadyApplied) {
            $checks[] = ['ok' => false, 'text' => $blockedReason];
        }

        return [
            'state' => $state,
            'label' => self::label($state),
            'checks' => $checks,
        ];
    }

    public static function label(string $state): string
    {
        return match ($state) {
            self::ELIGIBLE => 'Cocok untuk Anda',
            self::INELIGIBLE => 'Belum memenuhi syarat',
            self::INCOMPLETE => 'Lengkapi biodata',
            self::APPLIED => 'Sudah Anda daftar',
            self::BLOCKED => 'Belum bisa mendaftar',
        };
    }

    private static function gpa(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}
