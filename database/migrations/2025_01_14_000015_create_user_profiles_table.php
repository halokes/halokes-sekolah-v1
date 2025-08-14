<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            // Use UUID for the primary key
            $table->uuid('id')->primary();

            // Foreign key to users table using UUID, with cascade on delete
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Nullable profile columns
            $table->date('date_of_birth')->nullable()->default(null);
            $table->enum('gender', ['male', 'female'])->nullable()->default(null);
            $table->string('address')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('country')->nullable()->default(null);
            $table->string('profile_picture')->nullable()->default(null);

            // School-specific fields
            $table->uuid('school_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('student_id')->nullable();
            $table->string('nisn')->nullable();
            $table->string('nik')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('education_background')->nullable();
            $table->text('work_experience')->nullable();
            $table->text('skills')->nullable();
            $table->string('status')->default('active'); // active, inactive, suspended, graduated

            //used for auditing
            $table->string('created_by')->nullable()->default(null);
            $table->string('updated_by')->nullable()->default(null);

            // Add soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Add foreign key for school
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
};
