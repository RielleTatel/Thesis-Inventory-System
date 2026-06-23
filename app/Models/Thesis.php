<?php

namespace App\Models;

use Database\Factories\ThesisFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['department_id', 'title', 'program', 'year', 'abstract', 'recommendations', 'status'])]
class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
    use HasFactory, LogsActivity;

    // "thesis" → "theses"; Laravel would otherwise guess "thesis".
    protected $table = 'theses';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    /**
     * Activity logging (FR-7.2). Records created/updated/deleted with the acting
     * user as causer; `title` is kept in the log so deleted records stay readable.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('thesis')
            ->logOnly(['title', 'year', 'program', 'status'])
            ->setDescriptionForEvent(fn (string $event): string => $event);
    }

    /**
     * Scope to records visible to the public viewer.
     *
     * @param  Builder<Thesis>  $query
     * @return Builder<Thesis>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Whether this thesis has a stored approval/signature page image.
     *
     * `approval_page_path` is intentionally NOT mass-assignable — the value is a
     * server-generated storage path set in the Action, never raw user input.
     */
    public function hasApprovalPage(): bool
    {
        return $this->approvalPagePath() !== null;
    }

    /**
     * A browser-viewable URL for the approval page, or null if none is stored.
     * Private buckets get a short-lived signed URL; otherwise fall back to the
     * plain public URL. If neither can be produced (misconfig, missing object),
     * return null so the View button hides instead of rendering a broken image.
     */
    public function approvalPageUrl(): ?string
    {
        $path = $this->approvalPagePath();

        if ($path === null) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addMinutes(30));
        } catch (\Throwable) {
            // Signing unsupported (public bucket) — try a plain URL below.
        }

        try {
            return $disk->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The stored approval-page path, or null when it's absent or a known-bad
     * sentinel ("0"/"") left behind by an earlier failed upload.
     */
    private function approvalPagePath(): ?string
    {
        $path = $this->approval_page_path;

        return ($path === null || $path === '' || $path === '0') ? null : $path;
    }

    /**
     * Owning department (FR-3.4/3.6).
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany<ThesisAuthor, $this>
     */
    public function authors(): HasMany
    {
        return $this->hasMany(ThesisAuthor::class)->orderBy('position');
    }

    /**
     * @return HasMany<ThesisAdviser, $this>
     */
    public function advisers(): HasMany
    {
        return $this->hasMany(ThesisAdviser::class)->orderBy('position');
    }

    /**
     * @return HasMany<ThesisPanelist, $this>
     */
    public function panelists(): HasMany
    {
        return $this->hasMany(ThesisPanelist::class)->orderBy('position');
    }

    /**
     * @return HasMany<ThesisKeyword, $this>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(ThesisKeyword::class)->orderBy('position');
    }
}
