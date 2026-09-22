<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of a scholarship's checklist: this programme asks for this kind of
 * thing, and whether the applicant may leave it out.
 */
class ProgramRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'requirement_type_id',
        'is_required',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function requirementType(): BelongsTo
    {
        return $this->belongsTo(RequirementType::class);
    }
}
