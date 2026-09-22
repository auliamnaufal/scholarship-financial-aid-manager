<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'program_id',
        'semester',
        'submission_date',
        'status',
        'awarded_amount',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'submission_date' => 'date',
            'awarded_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function reviewsVisibleToStudent(): bool
    {
        return in_array($this->status, [ApplicationStatus::Approved, ApplicationStatus::Rejected], true);
    }

    /** Money already paid to this student for this application. */
    public function disbursedTotal(): float
    {
        return (float) $this->disbursements()->sum('amount');
    }

    /**
     * What is still owed on an approved application. Null while there is no
     * award to measure against — an unapproved application owes nothing.
     */
    public function remainingAward(): ?float
    {
        if ($this->awarded_amount === null) {
            return null;
        }

        return max(0, (float) $this->awarded_amount - $this->disbursedTotal());
    }

    /**
     * The requirements this application has not answered yet, keyed by
     * requirement type id. Optional ones are left out — only the compulsory
     * gaps block a submission.
     */
    public function missingRequirements()
    {
        $answered = $this->documents
            ->filter(fn (ApplicationDocument $document) => $document->isFilled())
            ->pluck('requirement_type_id')
            ->all();

        return $this->program->requirements
            ->where('is_required', true)
            ->reject(fn (ProgramRequirement $requirement) => in_array($requirement->requirement_type_id, $answered, true));
    }
}
