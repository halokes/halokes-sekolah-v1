<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('year_code')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->uuid('school_id');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add foreign key
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');

            // Ensure only one academic year can be current per school
            $table->unique(['school_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
