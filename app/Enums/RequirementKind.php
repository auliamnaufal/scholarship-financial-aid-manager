<?php

namespace App\Enums;

/**
 * How an applicant satisfies a requirement: by uploading something, or by
 * typing it into the form.
 */
enum RequirementKind: string
{
    case File = 'file';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::File => __('File upload'),
            self::Text => __('Written answer'),
        };
    }
}
