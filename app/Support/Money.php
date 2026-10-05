<?php

namespace App\Support;

class Money
{
    /** Formats an amount the Indonesian way: "Rp 1.250.000". */
    public static function rupiah(float|int|string|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    /** Short form for headline figures: "Rp 1,19 miliar", "Rp 250 juta". */
    public static function compact(float|int|string|null $amount): string
    {
        $value = (float) $amount;

        return match (true) {
            $value >= 1_000_000_000_000 => 'Rp '.rtrim(rtrim(number_format($value / 1_000_000_000_000, 2, ',', '.'), '0'), ',').' triliun',
            $value >= 1_000_000_000 => 'Rp '.rtrim(rtrim(number_format($value / 1_000_000_000, 2, ',', '.'), '0'), ',').' miliar',
            $value >= 1_000_000 => 'Rp '.rtrim(rtrim(number_format($value / 1_000_000, 1, ',', '.'), '0'), ',').' juta',
            default => self::rupiah($value),
        };
    }
}
