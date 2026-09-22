<?php

namespace App\Models;

use App\Enums\RequirementKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A kind of thing a scholarship can ask for: a CV, a transcript, an essay.
 * Coordinators pick from these rather than inventing their own.
 */
class RequirementType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'kind',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'kind' => RequirementKind::class,
        ];
    }

    public function programRequirements(): HasMany
    {
        return $this->hasMany(ProgramRequirement::class);
    }

    public function applicationDocuments(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function isFile(): bool
    {
        return $this->kind === RequirementKind::File;
    }
}
