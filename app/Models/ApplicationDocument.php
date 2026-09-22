<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * What the applicant supplied against one requirement.
 *
 * A 'file' requirement fills the file columns, a 'text' one fills `body`;
 * never both, which `isFile()` on the requirement type decides. Files live on
 * the private disk and are streamed through a controller, because transcripts
 * and recommendation letters are personal data and must not sit on a guessable
 * public URL.
 */
class ApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'requirement_type_id',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'body',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function requirementType(): BelongsTo
    {
        return $this->belongsTo(RequirementType::class);
    }

    /** True once the applicant has actually put something here. */
    public function isFilled(): bool
    {
        return filled($this->file_path) || filled($this->body);
    }

    /** Human-readable size, for the reviewer's checklist. */
    public function readableSize(): ?string
    {
        if (! $this->size_bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB'];
        $size = $this->size_bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, $unit === 0 ? 0 : 1).' '.$units[$unit];
    }

    /** Removes the stored file, if there is one. Used when it is replaced. */
    public function deleteFile(): void
    {
        if ($this->file_path) {
            Storage::disk('local')->delete($this->file_path);
        }
    }
}
