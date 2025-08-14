<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassModel extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'class_code',
        'school_id',
        'level_id',
        'academic_year_id',
        'homeroom_teacher_id',
        'max_students',
        'description',
        'is_active',
        'order',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'max_students' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function level()
    {
        return $this->belongsTo(SchoolLevel::class, 'level_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->hasManyThrough(User::class, Enrollment::class, 'class_id', 'id', 'id', 'student_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'class_id', 'subject_id')
            ->withPivot(['teacher_id', 'teaching_role', 'notes'])
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForLevel($query, $levelId)
    {
        return $query->where('level_id', $levelId);
    }

    // Accessors
    public function getStudentCountAttribute()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function getIsFullAttribute()
    {
        return $this->max_students && $this->student_count >= $this->max_students;
    }

    public function getHomeroomTeacherNameAttribute()
    {
        return $this->homeroomTeacher ? $this->homeroomTeacher->name : 'Not assigned';
    }
}
