<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Earlier failed uploads (a silent store() === false) persisted a
        // falsy "0", and some rows may hold "". Reset both to a clean null so
        // the UI treats them as "no approval page".
        DB::table('theses')
            ->whereIn('approval_page_path', ['0', ''])
            ->update(['approval_page_path' => null]);
    }

    public function down(): void
    {
        // No-op: the previous values were invalid; there is nothing to restore.
    }
};
