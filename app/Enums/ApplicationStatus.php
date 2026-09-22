<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Withdrawn by the student rather than decided on, so it leaves the
     * reviewer pool and can no longer be approved or rejected.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview], true);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Submitted => 'bg-slate-100 text-slate-700',
            self::UnderReview => 'bg-amber-100 text-amber-800',
            self::Approved => 'bg-emerald-100 text-emerald-700',
            self::Rejected => 'bg-red-100 text-red-700',
            self::Cancelled => 'bg-slate-200 text-slate-600',
        };
    }
}
