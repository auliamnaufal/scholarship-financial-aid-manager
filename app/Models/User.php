<?php

namespace App\Models;

// Email verification is deliberately off: the app sends no mail, so a new
// account could never confirm its address and would be locked out. Implementing
// MustVerifyEmail turns the `verified` middleware on the routes into a real check.
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function guardianPhones(): HasMany
    {
        return $this->hasMany(GuardianPhone::class);
    }

    /** Replaces the guardian's numbers with these, dropping blanks and repeats. */
    public function syncGuardianPhones(array $numbers): void
    {
        $numbers = collect($numbers)->map(fn ($n) => trim((string) $n))->filter()->unique()->values();

        $this->guardianPhones()->delete();
        $this->guardianPhones()->createMany($numbers->map(fn ($n) => ['phone_number' => $n])->all());
    }

    public function programsManaged(): HasMany
    {
        return $this->hasMany(Program::class, 'coordinator_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'student_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Every coordinator is a reviewer; a reviewer is not necessarily a
     * coordinator. The coordinator role carries the reviewer's abilities by
     * itself, so no account can be a coordinator and forget to be a reviewer.
     */
    public function isReviewer(): bool
    {
        return $this->hasAnyRole(['reviewer', 'coordinator']);
    }
}
