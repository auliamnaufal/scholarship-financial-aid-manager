<?php

namespace App\Models;

use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    /** The photo for each scholarship name the seed data uses. */
    private const COVERS = [
        // Need-based
        'Beasiswa Peduli Pendidikan' => 'photo-1532629345422-7515f3d16bb6', // coins in open hands
        'Beasiswa Harapan Bangsa' => 'photo-1522202176988-66273c2fd55f', // students smiling at a laptop
        'Beasiswa Pelita Ilmu' => 'photo-1456513080510-7bf3a84b82f8', // open book
        'Beasiswa Bakti Negeri' => 'photo-1469571486292-0ba58a3f068b', // hands joined in a heart
        'Beasiswa Kemandirian Belajar' => 'photo-1488190211105-8b0e65b80b4e', // studying alone at a desk
        'Beasiswa Cita Bangsa' => 'photo-1529156069898-49953e39b3ac', // friends side by side
        'Beasiswa Sinar Pendidikan' => 'photo-1524178232363-1fb2b075b655', // lecture room
        'Beasiswa Alumni Peduli' => 'photo-1521791136064-7986c2920216', // handshake
        'Beasiswa Talenta Daerah' => 'photo-1543269865-cbf427effbad', // friends studying together
        'Beasiswa Pendidikan Tinggi Mandiri' => 'photo-1562774053-701939374585', // campus building
        'Beasiswa Bangun Negeri' => 'photo-1579621970563-ebec7560ff3e', // seedling growing from coins
        'Beasiswa Mitra Pendidikan' => 'photo-1519389950473-47ba0277781c', // team around a table
        'Beasiswa Cahaya Ilmu' => 'photo-1427504494785-3a9ca7044f45', // student walking through a library
        'Beasiswa Langkah Awal' => 'photo-1503023345310-bd7c1de61c7d', // walking through a field
        // Merit-based
        'Beasiswa Prestasi Akademik' => 'photo-1523580846011-d3a5bc25702b', // graduation cap
        'Beasiswa Unggulan Mahasiswa' => 'photo-1606761568499-6d2451b23c66', // lecture hall
        'Beasiswa Garuda Muda' => 'photo-1571260899304-425eee4c7efc', // students heading to class
        'Beasiswa Generasi Emas' => 'photo-1541339907198-e08756dedf3f', // graduates throwing caps
        'Beasiswa Mahasiswa Berprestasi' => 'photo-1627556704302-624286467c65', // cap held up
        'Beasiswa Pemimpin Muda' => 'photo-1475721027785-f74eccf877e2', // microphone and audience
        'Beasiswa Cendekia Nusantara' => 'photo-1532012197267-da84d127e765', // book floating in a library
        'Beasiswa Inovasi dan Riset' => 'photo-1532094349884-543bc11b234d', // laboratory glassware
        'Beasiswa Studi Lanjut' => 'photo-1481627834876-b7833e8f5570', // bookshelves
        'Beasiswa Prestasi Olahraga dan Seni' => 'photo-1461896836934-ffe607ba8211', // sprinter on the blocks
        'Beasiswa Bintang Akademik' => 'photo-1509869175650-a1d97972541a', // chalkboard equation
        'Beasiswa Duta Kampus' => 'photo-1523240795612-9a054b0db644', // students laughing in a library
        'Beasiswa Juara Nusantara' => 'photo-1517649763962-0c623066013b', // cycling race
        'Beasiswa Puncak Prestasi' => 'photo-1454496522488-7a8e488e8606', // mountain peak
    ];

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

    public function requirements(): HasMany
    {
        return $this->hasMany(ProgramRequirement::class);
    }

    public function requirementTypes(): BelongsToMany
    {
        return $this->belongsToMany(RequirementType::class, 'program_requirements')
            ->withPivot(['is_required', 'instructions'])
            ->withTimestamps();
    }

    /**
     * Money already paid out across every application to this programme.
     * Worked out on read rather than stored, so it cannot drift from the
     * disbursement rows it summarises.
     */
    public function disbursedTotal(): float
    {
        return (float) Disbursement::query()
            ->whereIn('application_id', $this->applications()->select('id'))
            ->sum('amount');
    }

    /** Budget not yet handed out. Never reported below zero. */
    public function remainingBudget(): float
    {
        return max(0, (float) $this->budget - $this->disbursedTotal());
    }

    /**
     * A photo that fits the scholarship. Each name the seed data uses has a
     * picture chosen for it; any other name falls back to a pool for its type,
     * picked by the name so a programme keeps the same picture every time.
     * There is no image column, so nothing is stored.
     */
    public function coverImage(): string
    {
        $fallbacks = [
            ProgramType::NeedBased->value => [
                'photo-1522202176988-66273c2fd55f', // students at a laptop
                'photo-1543269865-cbf427effbad', // friends studying together
                'photo-1469571486292-0ba58a3f068b', // hands together
                'photo-1524178232363-1fb2b075b655', // lecture room
                'photo-1427504494785-3a9ca7044f45', // student in a library
            ],
            ProgramType::MeritBased->value => [
                'photo-1541339907198-e08756dedf3f', // graduates
                'photo-1523580846011-d3a5bc25702b', // graduation cap
                'photo-1606761568499-6d2451b23c66', // lecture hall
                'photo-1532012197267-da84d127e765', // library
                'photo-1481627834876-b7833e8f5570', // bookshelves
            ],
        ];

        $pool = $fallbacks[$this->type?->value ?? ProgramType::NeedBased->value];
        $photo = self::COVERS[$this->name] ?? $pool[crc32((string) $this->name) % count($pool)];

        return "https://images.unsplash.com/{$photo}?auto=format&fit=crop&w=1400&q=70";
    }

    /**
     * The gradient shown under the cover, and instead of it if it fails to load.
     */
    public function coverGradient(): string
    {
        $gradients = [
            'linear-gradient(135deg, #0f172a, #1c2f66 60%, #2a4cc2)',
            'linear-gradient(135deg, #111a3d, #223e9c 55%, #3a63e0)',
            'linear-gradient(135deg, #1e293b, #1f3680 55%, #0e7490)',
        ];

        return $gradients[($this->id ?? 0) % count($gradients)];
    }

    public function isOpen(): bool
    {
        return $this->application_deadline->isFuture() || $this->application_deadline->isToday();
    }
}
