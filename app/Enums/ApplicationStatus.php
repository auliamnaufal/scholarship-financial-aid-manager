<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Submitted => 'bg-slate-100 text-slate-700',
            self::UnderReview => 'bg-amber-100 text-amber-800',
            self::Approved => 'bg-emerald-100 text-emerald-700',
            self::Rejected => 'bg-red-100 text-red-700',
        };
    }
}
