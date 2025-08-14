<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'teacher_id',
        'assessment_type',
        'score',
        'grade',
        'predikat',
        'weight',
        'notes',
        'assessment_date',
        'semester',
        'academic_year_id',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'weight' => 'decimal:2',
        'assessment_date' => 'date',
        'semester' => 'integer',
    ];

    // Constants
    const ASSESSMENT_DAILY = 'daily';
    const ASSESSMENT_QUIZ = 'quiz';
    const ASSESSMENT_MIDTERM = 'midterm';
    const ASSESSMENT_FINAL = 'final';
    const ASSESSMENT_PROJECT = 'project';
    const ASSESSMENT_ASSIGNMENT = 'assignment';

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function student()
    {
        return $this->hasOneThrough(User::class, Enrollment::class, 'id', 'id', 'enrollment_id', 'student_id');
    }

    // Scopes
    public function scopeDaily($query)
    {
        return $query->where('assessment_type', self::ASSESSMENT_DAILY);
    }

    public function scopeQuiz($query)
    {
        return $query->where('assessment_type', self::ASSESSMENT_QUIZ);
    }

    public function scopeMidterm($query)
    {
        return $query->where('assessment_type', self::ASSESSMENT_MIDTERM);
    }

    public function scopeFinal($query)
    {
        return $query->where('assessment_type', self::ASSESSMENT_FINAL);
    }

    public function scopeProject($query)
    {
        return $query->where('assessment_type', self::ASSESSMENT_PROJECT);
    }

    public function scopeAssignment($query)
    {
        return $query->where('assessment_type', self::ASSESSMENT_ASSIGNMENT);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->whereHas('enrollment', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
    }

    public function scopeForClass($query, $classId)
    {
        return $query->whereHas('enrollment', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        });
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeAssessmentType($query, $assessmentType)
    {
        return $query->where('assessment_type', $assessmentType);
    }

    // Accessors
    public function getWeightedScoreAttribute()
    {
        return $this->score * $this->weight;
    }

    public function getIsDailyAttribute()
    {
        return $this->assessment_type === self::ASSESSMENT_DAILY;
    }

    public function getIsQuizAttribute()
    {
        return $this->assessment_type === self::ASSESSMENT_QUIZ;
    }

    public function getIsMidtermAttribute()
    {
        return $this->assessment_type === self::ASSESSMENT_MIDTERM;
    }

    public function getIsFinalAttribute()
    {
        return $this->assessment_type === self::ASSESSMENT_FINAL;
    }

    public function getIsProjectAttribute()
    {
        return $this->assessment_type === self::ASSESSMENT_PROJECT;
    }

    public function getIsAssignmentAttribute()
    {
        return $this->assessment_type === self::ASSESSMENT_ASSIGNMENT;
    }

    public function getGradeLabelAttribute()
    {
        return $this->grade ? $this->grade . ' (' . $this->predikat . ')' : '-';
    }

    public function getFormattedAssessmentDateAttribute()
    {
        return $this->assessment_date ? $this->assessment_date->format('d M Y') : '-';
    }

    // Mutators
    public function setAssessmentTypeAttribute($value)
    {
        $this->attributes['assessment_type'] = strtolower($value);
    }

    public function setScoreAttribute($value)
    {
        $this->attributes['score'] = $value ? floatval($value) : null;
    }

    public function setWeightAttribute($value)
    {
        $this->attributes['weight'] = $value ? floatval($value) : 1.00;
    }

    // Helper methods
    public static function getAssessmentTypes()
    {
        return [
            self::ASSESSMENT_DAILY => 'Daily Assessment',
            self::ASSESSMENT_QUIZ => 'Quiz',
            self::ASSESSMENT_MIDTERM => 'Midterm',
            self::ASSESSMENT_FINAL => 'Final',
            self::ASSESSMENT_PROJECT => 'Project',
            self::ASSESSMENT_ASSIGNMENT => 'Assignment',
        ];
    }

    public function getAssessmentTypeLabelAttribute()
    {
        return self::getAssessmentTypes()[$this->assessment_type] ?? $this->assessment_type;
    }
}
