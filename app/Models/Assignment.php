<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'class_id',
        'title',
        'description',
        'assignment_type',
        'file_path',
        'file_name',
        'due_date',
        'submission_start',
        'submission_end',
        'max_score',
        'instructions',
        'is_published',
        'allow_late_submission',
        'late_penalty_percent',
        'academic_year_id',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'due_date' => 'datetime',
        'submission_start' => 'datetime',
        'submission_end' => 'datetime',
        'max_score' => 'integer',
        'is_published' => 'boolean',
        'allow_late_submission' => 'boolean',
        'late_penalty_percent' => 'integer',
    ];

    // Constants
    const TYPE_HOMEWORK = 'homework';
    const TYPE_PROJECT = 'project';
    const TYPE_QUIZ = 'quiz';
    const TYPE_EXAM = 'exam';
    const TYPE_PRESENTATION = 'presentation';
    const TYPE_ESSAY = 'essay';
    const TYPE_ASSIGNMENT = 'assignment';

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // Scopes
    public function scopeHomework($query)
    {
        return $query->where('assignment_type', self::TYPE_HOMEWORK);
    }

    public function scopeProject($query)
    {
        return $query->where('assignment_type', self::TYPE_PROJECT);
    }

    public function scopeQuiz($query)
    {
        return $query->where('assignment_type', self::TYPE_QUIZ);
    }

    public function scopeExam($query)
    {
        return $query->where('assignment_type', self::TYPE_EXAM);
    }

    public function scopePresentation($query)
    {
        return $query->where('assignment_type', self::TYPE_PRESENTATION);
    }

    public function scopeEssay($query)
    {
        return $query->where('assignment_type', self::TYPE_ESSAY);
    }

    public function scopeAssignment($query)
    {
        return $query->where('assignment_type', self::TYPE_ASSIGNMENT);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeAllowLateSubmission($query)
    {
        return $query->where('allow_late_submission', true);
    }

    public function scopeDisallowLateSubmission($query)
    {
        return $query->where('allow_late_submission', false);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
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

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now())
                    ->where('is_published', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_published', true)
                    ->where('due_date', '>=', now());
    }

    // Accessors
    public function getIsLateSubmissionAllowedAttribute()
    {
        return $this->allow_late_submission;
    }

    public function getHasFileAttribute()
    {
        return !empty($this->file_path) && !empty($this->file_name);
    }

    public function getSubmissionWindowAttribute()
    {
        if (!$this->submission_start && !$this->submission_end) {
            return 'No submission window specified';
        }

        $start = $this->submission_start ? $this->submission_start->format('d M Y H:i') : 'Immediately';
        $end = $this->submission_end ? $this->submission_end->format('d M Y H:i') : 'Until due date';

        return "From {$start} to {$end}";
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->is_published;
    }

    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    public function getLatePenaltyAttribute()
    {
        if (!$this->allow_late_submission || !$this->late_penalty_percent) {
            return 0;
        }

        return $this->late_penalty_percent;
    }

    public function getAssignmentTypeLabelAttribute()
    {
        return self::getAssignmentTypes()[$this->assignment_type] ?? $this->assignment_type;
    }

    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('d M Y H:i') : 'No due date';
    }

    public function getFormattedSubmissionStartAttribute()
    {
        return $this->submission_start ? $this->submission_start->format('d M Y H:i') : 'Immediately';
    }

    public function getFormattedSubmissionEndAttribute()
    {
        return $this->submission_end ? $this->submission_end->format('d M Y H:i') : 'Until due date';
    }

    // Mutators
    public function setAssignmentTypeAttribute($value)
    {
        $this->attributes['assignment_type'] = strtolower($value);
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function setSubmissionStartAttribute($value)
    {
        $this->attributes['submission_start'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function setSubmissionEndAttribute($value)
    {
        $this->attributes['submission_end'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    // Helper methods
    public static function getAssignmentTypes()
    {
        return [
            self::TYPE_HOMEWORK => 'Homework',
            self::TYPE_PROJECT => 'Project',
            self::TYPE_QUIZ => 'Quiz',
            self::TYPE_EXAM => 'Exam',
            self::TYPE_PRESENTATION => 'Presentation',
            self::TYPE_ESSAY => 'Essay',
            self::TYPE_ASSIGNMENT => 'Assignment',
        ];
    }

    public function getSubmissionCountAttribute()
    {
        return $this->submissions()->count();
    }

    public function getGradedSubmissionCountAttribute()
    {
        return $this->submissions()->where('status', 'graded')->count();
    }

    public function getAverageScoreAttribute()
    {
        $gradedSubmissions = $this->submissions()->whereNotNull('score')->get();
        if ($gradedSubmissions->isEmpty()) {
            return 0;
        }

        return $gradedSubmissions->avg('score');
    }
}
