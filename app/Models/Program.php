<?php

namespace App\Models;

use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'funding_source',
        'budget',
        'application_deadline',
        'max_family_income',
        'min_gpa',
        'coordinator_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
            'application_deadline' => 'date',
            'budget' => 'decimal:2',
            'max_family_income' => 'decimal:2',
            'min_gpa' => 'decimal:2',
        ];
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * A stock cover for the programme. There is no image column, so one is
     * drawn from a fixed pool by id: the same programme always gets the same
     * picture, and the pool wraps once there are more programmes than covers.
     */
    public function coverImage(): string
    {
        $covers = [
            'photo-1523050854058-8df90110c9f1', // graduation caps
            'photo-1541339907198-e08756dedf3f', // lecture hall
            'photo-1562774053-701939374585',    // campus building
            'photo-1522202176988-66273c2fd55f', // students working together
            'photo-1498243691581-b145c3f54a5a', // library shelves
            'photo-1532012197267-da84d127e765', // stacked books
            'photo-1543269865-cbf427effbad',    // study group at a table
        ];

        $cover = $covers[($this->id ?? 0) % count($covers)];

        return "https://images.unsplash.com/{$cover}?auto=format&fit=crop&w=1400&q=70";
    }

    /**
     * The gradient shown under the cover, and instead of it if it fails to load.
     */
    public function coverGradient(): string
    {
        $gradients = [
            'linear-gradient(135deg, #4f46e5, #7c3aed 55%, #c026d3)',
            'linear-gradient(135deg, #7c3aed, #c026d3 60%, #4f46e5)',
            'linear-gradient(135deg, #4338ca, #6366f1 50%, #a21caf)',
        ];

        return $gradients[($this->id ?? 0) % count($gradients)];
    }

    public function isOpen(): bool
    {
        return $this->application_deadline->isFuture() || $this->application_deadline->isToday();
    }
}
