<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSubject extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'teaching_role',
        'notes',
        'is_active',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Constants
    const ROLE_REGULAR = 'regular';
    const ROLE_ASSISTANT = 'assistant';
    const ROLE_SUBSTITUTE = 'substitute';
    const ROLE_HEAD = 'head';
    const ROLE_COORDINATOR = 'coordinator';

    // Relationships
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Scopes
    public function scopeRegular($query)
    {
        return $query->where('teaching_role', self::ROLE_REGULAR);
    }

    public function scopeAssistant($query)
    {
        return $query->where('teaching_role', self::ROLE_ASSISTANT);
    }

    public function scopeSubstitute($query)
    {
        return $query->where('teaching_role', self::ROLE_SUBSTITUTE);
    }

    public function scopeHead($query)
    {
        return $query->where('teaching_role', self::ROLE_HEAD);
    }

    public function scopeCoordinator($query)
    {
        return $query->where('teaching_role', self::ROLE_COORDINATOR);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    // Accessors
    public function getTeachingRoleLabelAttribute()
    {
        return self::getTeachingRoles()[$this->teaching_role] ?? $this->teaching_role;
    }

    public function getIsRegularTeacherAttribute()
    {
        return $this->teaching_role === self::ROLE_REGULAR;
    }

    public function getIsAssistantTeacherAttribute()
    {
        return $this->teaching_role === self::ROLE_ASSISTANT;
    }

    public function getIsSubstituteTeacherAttribute()
    {
        return $this->teaching_role === self::ROLE_SUBSTITUTE;
    }

    public function getIsHeadTeacherAttribute()
    {
        return $this->teaching_role === self::ROLE_HEAD;
    }

    public function getIsCoordinatorAttribute()
    {
        return $this->teaching_role === self::ROLE_COORDINATOR;
    }

    public function getTeacherNameAttribute()
    {
        return $this->teacher ? $this->teacher->name : 'Unknown Teacher';
    }

    public function getSubjectNameAttribute()
    {
        return $this->subject ? $this->subject->name : 'Unknown Subject';
    }

    public function getClassNameAttribute()
    {
        return $this->class ? $this->class->name : 'Unknown Class';
    }

    public function getAcademicYearNameAttribute()
    {
        return $this->academicYear ? $this->academicYear->name : 'Unknown Academic Year';
    }

    // Mutators
    public function setTeachingRoleAttribute($value)
    {
        $this->attributes['teaching_role'] = strtolower($value);
    }

    // Helper methods
    public static function getTeachingRoles()
    {
        return [
            self::ROLE_REGULAR => 'Regular Teacher',
            self::ROLE_ASSISTANT => 'Assistant Teacher',
            self::ROLE_SUBSTITUTE => 'Substitute Teacher',
            self::ROLE_HEAD => 'Head Teacher',
            self::ROLE_COORDINATOR => 'Subject Coordinator',
        ];
    }

    // Get total teaching hours for this assignment
    public function getTotalTeachingHours()
    {
        return $this->schedules()->active()->sum('duration');
    }

    // Get schedule count for this assignment
    public function getScheduleCountAttribute()
    {
        return $this->schedules()->active()->count();
    }

    // Get student count for this assignment
    public function getStudentCountAttribute()
    {
        return $this->class ? $this->class->student_count : 0;
    }

    // Get assignment count for this assignment
    public function getAssignmentCountAttribute()
    {
        return $this->assignments()->published()->count();
    }

    // Get grade count for this assignment
    public function getGradeCountAttribute()
    {
        return $this->grades()->count();
    }

    // Check if teacher is available for more classes
    public function isAvailableForMoreClasses()
    {
        // This is a simple check - you can make it more sophisticated
        $currentScheduleCount = $this->schedule_count;
        $maxSchedulesPerTeacher = 30; // Adjust as needed

        return $currentScheduleCount < $maxSchedulesPerTeacher;
    }

    // Get all related enrollments
    public function enrollments()
    {
        return Enrollment::where('class_id', $this->class_id)
                        ->where('academic_year_id', $this->academic_year_id)
                        ->get();
    }

    // Get all related students
    public function students()
    {
        return $this->enrollments->pluck('student');
    }

    // Check if this teacher subject has any schedule conflicts
    public function hasScheduleConflicts()
    {
        $conflicts = Schedule::where('class_id', $this->class_id)
                            ->where('day_of_week', $this->schedules->first()->day_of_week ?? null)
                            ->where('teacher_id', $this->teacher_id)
                            ->where('id', '!=', $this->id ?? null)
                            ->where('is_active', true)
                            ->exists();

        return $conflicts;
    }
}
