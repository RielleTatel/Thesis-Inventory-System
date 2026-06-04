<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();

            // Owning department (FR-3.4/3.6 scoping). restrictOnDelete keeps the
            // SRS keep-or-delete choice (FR-2.3) an explicit action, never a cascade.
            $table->foreignId('department_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('title');
            $table->string('program')->nullable();   // degree / program
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('abstract')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();

            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theses');
    }
};
