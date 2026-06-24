<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Thesis;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ThesisSeeder extends Seeder
{
    /**
     * The owning department, by the code DatabaseSeeder assigns it ("Science
     * Information Technology Engineering Academic Organization" / SITEAO).
     * All 12 handover theses belong here.
     */
    private const DEPARTMENT_CODE = 'SITEAO';

    /**
     * Populate the catalog from the committed handover data
     * (database/seeders/data/thesis-data.json): 12 published Computer Science
     * theses, each with ordered authors / advisers / panelists / keywords and
     * its approval-page image — so the public browse + detail screens are
     * fully populated after `migrate --seed`.
     */
    public function run(): void
    {
        if (Thesis::query()->exists()) {
            $this->command->info('Theses already present — skipping ThesisSeeder.');

            return;
        }

        $department = $this->resolveDepartment();
        $records = $this->records();

        DB::transaction(function () use ($department, $records) {
            foreach ($records as $record) {
                $this->seedThesis($department, $record);
            }
        });

        $this->command->info(sprintf('Seeded %d theses for %s.', count($records), $department->name));
    }

    /**
     * Resolve the single owning department created during DatabaseSeeder.
     * Never creates one and never returns null — a missing department means the
     * seeders ran out of order, which is a bug, not a record to fabricate.
     */
    private function resolveDepartment(): Department
    {
        return Department::query()->where('code', self::DEPARTMENT_CODE)->first()
            ?? throw new \RuntimeException(sprintf(
                'Department [%s] not found. Seed departments (DatabaseSeeder) before ThesisSeeder.',
                self::DEPARTMENT_CODE,
            ));
    }

    /**
     * Create one thesis row, its ordered child rows, and its approval image.
     *
     * @param  array<string, mixed>  $record
     */
    private function seedThesis(Department $department, array $record): void
    {
        // department_id comes from the relationship, so it can never be null.
        $thesis = $department->theses()->create([
            'title' => $record['title'],
            'program' => $record['program'],
            'year' => $record['year'],
            'abstract' => $record['abstract'],
            'recommendations' => $record['recommendations'],
            // Required: the column defaults to 'draft', which hides records from
            // the public viewer — every seeded record must be published.
            'status' => 'published',
        ]);

        foreach (['authors', 'advisers', 'panelists', 'keywords'] as $relation) {
            $this->attachOrdered($thesis, $relation, $this->stringList($record[$relation] ?? []));
        }

        // "ref" is an internal label (e.g. "T1") — used only for log output.
        $this->storeApprovalPage(
            $thesis,
            (string) ($record['ref'] ?? '?'),
            (string) ($record['approval_image'] ?? ''),
        );
    }

    /**
     * Insert ordered child rows for a HasMany relation, preserving JSON array
     * order with a 1-based position.
     *
     * @param  list<string>  $values
     */
    private function attachOrdered(Thesis $thesis, string $relation, array $values): void
    {
        $position = 1;

        foreach ($values as $name) {
            $thesis->{$relation}()->create(['name' => $name, 'position' => $position++]);
        }
    }

    /**
     * Copy a handover approval image onto the same disk/path the OCR upload uses
     * (a generated filename under approval_pages/ on the private 'local' disk),
     * then record its stored path. Graceful by design: if the copy fails, warn
     * and leave approval_page_path null — never abort the run, never persist a
     * bad or empty path.
     */
    private function storeApprovalPage(Thesis $thesis, string $ref, string $filename): void
    {
        $source = database_path('seeders/data/approval-images/'.$filename);

        if ($filename === '' || ! is_file($source)) {
            $this->command->warn(sprintf('[%s] approval image "%s" not found — seeded without it.', $ref, $filename));

            return;
        }

        try {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk(Thesis::APPROVAL_DISK);

            // Mirrors HandlesApprovalPage's $file->store(APPROVAL_DIR, APPROVAL_DISK).
            $path = $disk->putFile(Thesis::APPROVAL_DIR, new File($source));
        } catch (\Throwable $e) {
            $this->command->warn(sprintf('[%s] approval image upload failed (%s) — seeded without it.', $ref, $e->getMessage()));

            return;
        }

        // A false/empty return means the write failed — never persist a falsy path.
        if (! $path) {
            $this->command->warn(sprintf('[%s] approval image could not be stored (disk unconfigured?) — seeded without it.', $ref));

            return;
        }

        $thesis->approval_page_path = $path;
        $thesis->save();
    }

    /**
     * The thesis records from the committed handover JSON. The file stays in the
     * repo as the data source and is read at runtime; "_meta" is ignored.
     *
     * @return list<array<string, mixed>>
     */
    private function records(): array
    {
        $path = database_path('seeders/data/thesis-data.json');

        if (! is_file($path)) {
            throw new \RuntimeException("Thesis seed data not found at [{$path}].");
        }

        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded) || ! isset($decoded['theses']) || ! is_array($decoded['theses'])) {
            throw new \RuntimeException("Thesis seed data at [{$path}] has no 'theses' array.");
        }

        /** @var list<array<string, mixed>> $theses */
        $theses = array_values($decoded['theses']);

        return $theses;
    }

    /**
     * Normalize a JSON multi-value field into an ordered list of strings.
     *
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $name): string => (string) $name, $value));
    }
}
