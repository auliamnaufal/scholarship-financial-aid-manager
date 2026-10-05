<?php

namespace App\Enums;

enum ProgramType: string
{
    case NeedBased = 'need_based';
    case MeritBased = 'merit_based';

    public function label(): string
    {
        return match ($this) {
            self::NeedBased => __('Need-based'),
            self::MeritBased => __('Merit-based'),
        };
    }
}
