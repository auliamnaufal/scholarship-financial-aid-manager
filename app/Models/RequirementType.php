<?php

namespace App\Models;

use App\Enums\RequirementKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A kind of thing a scholarship can ask for: a CV, a transcript, an essay.
 * Coordinators keep this list and pick from it on each scholarship.
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

    /** Whether any scholarship asks for this, or any applicant has answered it. */
    public function isInUse(): bool
    {
        return $this->programRequirements()->exists() || $this->applicationDocuments()->exists();
    }

    /** A slug from the name that no other type has taken: "cv", then "cv-2", "cv-3". */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'persyaratan';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
