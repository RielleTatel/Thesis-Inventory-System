<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ordered related table (coding standard #7): one row per author, with a
        // position column because author order is meaningful and must stay searchable.
        Schema::create('thesis_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['thesis_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thesis_authors');
    }
};
