<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_subjects';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'teaching_role',
        'notes',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
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

    public function scopeTeachingRole($query, $role)
    {
        return $query->where('teaching_role', $role);
    }

    public function scopeRegular($query)
    {
        return $query->where('teaching_role', 'regular');
    }

    public function scopeAssistant($query)
    {
        return $query->where('teaching_role', 'assistant');
    }

    public function scopeSubstitute($query)
    {
        return $query->where('teaching_role', 'substitute');
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('teacher', function ($teacherQuery) use ($keyword) {
                $teacherQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhereHas('subject', function ($subjectQuery) use ($keyword) {
                $subjectQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('code', 'like', '%' . $keyword . '%');
            })->orWhereHas('class', function ($classQuery) use ($keyword) {
                $classQuery->where('name', 'like', '%' . $keyword . '%')
                          ->orWhere('class_code', 'like', '%' . $keyword . '%');
            })->orWhere('teaching_role', 'like', '%' . $keyword . '%')
              ->orWhere('notes', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the teaching role name in a more readable format
     */
    public function getTeachingRoleNameAttribute()
    {
        $roles = [
            'regular' => 'Regular Teacher',
            'assistant' => 'Assistant Teacher',
            'substitute' => 'Substitute Teacher'
        ];

        return $roles[$this->teaching_role] ?? ucfirst($this->teaching_role);
    }

    /**
     * Get the teacher's full information
     */
    public function getTeacherInfoAttribute()
    {
        return $this->teacher;
    }

    /**
     * Get the subject information
     */
    public function getSubjectInfoAttribute()
    {
        return $this->subject;
    }

    /**
     * Get the class information
     */
    public function getClassInfoAttribute()
    {
        return $this->class;
    }

    /**
     * Get the academic year information
     */
    public function getAcademicYearInfoAttribute()
    {
        return $this->academicYear;
    }
}
