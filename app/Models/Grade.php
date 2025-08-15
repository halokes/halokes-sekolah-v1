<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grades';
    protected $keyType = 'string';
    public $incrementing = false;

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
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'weight' => 'decimal:2',
        'semester' => 'integer',
        'score' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrollment_id', 'enrollment_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scopeDaily($query)
    {
        return $query->where('assessment_type', 'daily');
    }

    public function scopeQuiz($query)
    {
        return $query->where('assessment_type', 'quiz');
    }

    public function scopeMidterm($query)
    {
        return $query->where('assessment_type', 'midterm');
    }

    public function scopeFinal($query)
    {
        return $query->where('assessment_type', 'final');
    }

    public function scopeProject($query)
    {
        return $query->where('assessment_type', 'project');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->whereHas('enrollment', function ($enrollmentQuery) use ($studentId) {
            $enrollmentQuery->where('student_id', $studentId);
        });
    }

    public function scopeForClass($query, $classId)
    {
        return $query->whereHas('enrollment.class', function ($classQuery) use ($classId) {
            $classQuery->where('id', $classId);
        });
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
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

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('assessment_date', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('enrollment.student', function ($studentQuery) use ($keyword) {
                $studentQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhereHas('subject', function ($subjectQuery) use ($keyword) {
                $subjectQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('code', 'like', '%' . $keyword . '%');
            })->orWhere('assessment_type', 'like', '%' . $keyword . '%')
              ->orWhere('score', 'like', '%' . $keyword . '%')
              ->orWhere('grade', 'like', '%' . $keyword . '%')
              ->orWhere('predikat', 'like', '%' . $keyword . '%')
              ->orWhere('notes', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the assessment type name in a more readable format
     */
    public function getAssessmentTypeNameAttribute()
    {
        $types = [
            'daily' => 'Daily Assessment',
            'quiz' => 'Quiz',
            'midterm' => 'Midterm Exam',
            'final' => 'Final Exam',
            'project' => 'Project'
        ];

        return $types[$this->assessment_type] ?? ucfirst($this->assessment_type);
    }

    /**
     * Get the letter grade based on score
     */
    public function getLetterGradeAttribute()
    {
        if (!$this->score) {
            return '-';
        }

        if ($this->score >= 90) return 'A';
        if ($this->score >= 80) return 'B';
        if ($this->score >= 70) return 'C';
        if ($this->score >= 60) return 'D';
        return 'E';
    }

    /**
     * Get the predicate based on score (Indonesian grading system)
     */
    public function getPredicateAttribute()
    {
        if (!$this->score) {
            return '-';
        }

        if ($this->score >= 96) return 'Sangat Memuaskan';
        if ($this->score >= 91) return 'Memuaskan';
        if ($this->score >= 86) return 'Baik Sekali';
        if ($this->score >= 81) return 'Baik';
        if ($this->score >= 76) return 'Cukup Baik';
        if ($this->score >= 71) return 'Cukup';
        if ($this->score >= 66) return 'Lebih Dari Cukup';
        if ($this->score >= 61) return 'Kurang';
        return 'Sangat Kurang';
    }

    /**
     * Check if the grade is for the current semester
     */
    public function getIsCurrentSemesterAttribute()
    {
        $currentSemester = now()->month <= 6 ? 1 : 2;
        return $this->semester === $currentSemester;
    }

    /**
     * Get the weighted score
     */
    public function getWeightedScoreAttribute()
    {
        if (!$this->score || !$this->weight) {
            return null;
        }

        return round($this->score * $this->weight, 2);
    }

    /**
     * Scope to get grades by score range
     */
    public function scopeScoreRange($query, $minScore, $maxScore)
    {
        return $query->whereBetween('score', [$minScore, $maxScore]);
    }

    /**
     * Scope to get grades by grade range
     */
    public function scopeGradeRange($query, $minGrade, $maxGrade)
    {
        return $query->whereBetween('grade', [$minGrade, $maxGrade]);
    }

    /**
     * Get the student's full information
     */
    public function getStudentInfoAttribute()
    {
        return $this->enrollment->student_info;
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
        return $this->enrollment->class;
    }

    /**
     * Get the teacher information
     */
    public function getTeacherInfoAttribute()
    {
        return $this->teacher;
    }

    /**
     * Get the academic year information
     */
    public function getAcademicYearInfoAttribute()
    {
        return $this->academicYear;
    }

    /**
     * Get the semester name
     */
    public function getSemesterNameAttribute()
    {
        return $this->semester === 1 ? 'Semester 1' : 'Semester 2';
    }

    /**
     * Get the formatted assessment date
     */
    public function getFormattedAssessmentDateAttribute()
    {
        return $this->assessment_date->format('d M Y');
    }

    /**
     * Check if the grade is for a final assessment
     */
    public function getIsFinalAssessmentAttribute()
    {
        return in_array($this->assessment_type, ['final', 'midterm']);
    }

    /**
     * Check if the grade is for a regular assessment
     */
    public function getIsRegularAssessmentAttribute()
    {
        return in_array($this->assessment_type, ['daily', 'quiz', 'project']);
    }
}
