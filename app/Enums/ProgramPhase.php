<?php

namespace App\Enums;

/**
 * Where a scholarship is on its timeline. Worked out from the dates, never
 * stored.
 */
enum ProgramPhase: string
{
    /** Students may apply. */
    case Registration = 'registration';

    /** Registration is closed and reviewers score the applications. */
    case Review = 'review';

    /** Review is over and the recipients are being decided. */
    case Acceptance = 'acceptance';

    /** The announcement date has passed. */
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Registration => 'Pendaftaran',
            self::Review => 'Review',
            self::Acceptance => 'Penerimaan',
            self::Completed => 'Selesai',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Registration => 'bg-emerald-100 text-emerald-700',
            self::Review => 'bg-amber-100 text-amber-800',
            self::Acceptance => 'bg-indigo-100 text-indigo-700',
            self::Completed => 'bg-slate-200 text-slate-600',
        };
    }
}
