<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theses', function (Blueprint $table) {
            // Path (on the private local disk) to the approval/signature page image.
            // The single allowed exception to the metadata-only rule (FR-4.4) —
            // storing the page image was approved by the department chair.
            $table->string('approval_page_path')->nullable()->after('recommendations');
        });
    }

    public function down(): void
    {
        Schema::table('theses', function (Blueprint $table) {
            $table->dropColumn('approval_page_path');
        });
    }
};
