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
        Schema::create('grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id');
            $table->uuid('subject_id');
            $table->uuid('teacher_id');
            $table->string('assessment_type'); // daily, quiz, midterm, final, project
            $table->string('score'); // numeric score
            $table->string('grade'); // letter grade (A, B, C, etc.)
            $table->string('predikat'); // predikat (Memuaskan, Baik, etc.)
            $table->decimal('weight', 5, 2)->default(1.00); // weight for calculation
            $table->text('notes')->nullable();
            $table->date('assessment_date');
            $table->integer('semester')->default(1); // 1 or 2
            $table->uuid('academic_year_id');
            $table->timestamps();
            $table->softDeletes();

            // Add foreign keys
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');

            // Ensure unique grade per enrollment, subject, assessment type, and semester
            $table->unique(['enrollment_id', 'subject_id', 'assessment_type', 'semester', 'academic_year_id'], 'grade_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
