<?php

namespace App\Enums;

enum ProgramType: string
{
    case NeedBased = 'need_based';
    case MeritBased = 'merit_based';

    public function label(): string
    {
        return match ($this) {
            self::NeedBased => 'Need-based',
            self::MeritBased => 'Merit-based',
        };
    }
}
