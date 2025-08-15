<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assignments';
    protected $keyType = 'string';
    public $incrementing = false;

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
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'submission_start' => 'datetime',
        'submission_end' => 'datetime',
        'max_score' => 'integer',
        'is_published' => 'boolean',
        'allow_late_submission' => 'boolean',
        'late_penalty_percent' => 'decimal:2',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany('App\Models\File', 'fileable');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
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

    public function scopeAssignmentType($query, $assignmentType)
    {
        return $query->where('assignment_type', $assignmentType);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now())->published();
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->published();
    }

    public function scopeActive($query)
    {
        return $query->where('due_date', '>=', now())->published();
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%')
              ->orWhere('instructions', 'like', '%' . $keyword . '%')
              ->orWhere('assignment_type', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the assignment type name in a more readable format
     */
    public function getAssignmentTypeNameAttribute()
    {
        $types = [
            'homework' => 'Homework',
            'project' => 'Project',
            'quiz' => 'Quiz',
            'essay' => 'Essay',
            'presentation' => 'Presentation',
            'lab' => 'Lab Report',
            'other' => 'Other'
        ];

        return $types[$this->assignment_type] ?? ucfirst($this->assignment_type);
    }

    /**
     * Check if the assignment is currently available for submission
     */
    public function getIsAvailableForSubmissionAttribute()
    {
        $now = now();
        return $this->is_published &&
               (!$this->submission_start || $now >= $this->submission_start) &&
               (!$this->submission_end || $now <= $this->submission_end);
    }

    /**
     * Check if the assignment is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now();
    }

    /**
     * Check if the assignment is upcoming
     */
    public function getIsUpcomingAttribute()
    {
        return $this->due_date > now();
    }

    /**
     * Check if late submission is allowed
     */
    public function getAllowsLateSubmissionAttribute()
    {
        return $this->allow_late_submission && $this->is_overdue;
    }

    /**
     * Get the late penalty amount
     */
    public function getLatePenaltyAmountAttribute()
    {
        if (!$this->max_score || !$this->late_penalty_percent) {
            return 0;
        }

        return round(($this->max_score * $this->late_penalty_percent) / 100, 2);
    }

    /**
     * Get the submission status for a specific student
     */
    public function getSubmissionStatusForStudent($studentId)
    {
        $submission = $this->submissions()->where('student_id', $studentId)->first();

        if (!$submission) {
            return $this->is_available_for_submission ? 'not_submitted' : 'not_available';
        }

        return $submission->status;
    }

    /**
     * Get the number of submissions
     */
    public function getSubmissionCountAttribute()
    {
        return $this->submissions()->count();
    }

    /**
     * Get the number of graded submissions
     */
    public function getGradedSubmissionCountAttribute()
    {
        return $this->submissions()->where('status', 'graded')->count();
    }

    /**
     * Get the average score of submissions
     */
    public function getAverageScoreAttribute()
    {
        $scores = $this->submissions()->whereNotNull('score')->pluck('score');
        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 2);
    }

    /**
     * Get the submission rate
     */
    public function getSubmissionRateAttribute()
    {
        $totalStudents = $this->class->current_student_count;
        if ($totalStudents === 0) {
            return 0;
        }

        return round(($this->submission_count / $totalStudents) * 100, 2);
    }

    /**
     * Get the formatted due date
     */
    public function getFormattedDueDateAttribute()
    {
        return $this->due_date->format('d M Y H:i');
    }

    /**
     * Get the submission time window
     */
    public function getSubmissionTimeWindowAttribute()
    {
        if ($this->submission_start && $this->submission_end) {
            return $this->submission_start->format('d M Y H:i') . ' - ' . $this->submission_end->format('d M Y H:i');
        } elseif ($this->submission_start) {
            return $this->submission_start->format('d M Y H:i') . ' - Open';
        } elseif ($this->submission_end) {
            return 'Open - ' . $this->submission_end->format('d M Y H:i');
        }
        return 'Open';
    }

    /**
     * Get the file URL if file exists
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    /**
     * Scope to get assignments with files
     */
    public function scopeWithFiles($query)
    {
        return $query->whereNotNull('file_path');
    }

    /**
     * Scope to get assignments without files
     */
    public function scopeWithoutFiles($query)
    {
        return $query->whereNull('file_path');
    }

    /**
     * Get the class information
     */
    public function getClassInfoAttribute()
    {
        return $this->class;
    }

    /**
     * Get the subject information
     */
    public function getSubjectInfoAttribute()
    {
        return $this->subject;
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
     * Check if the assignment has a file attachment
     */
    public function getHasFileAttribute()
    {
        return !is_null($this->file_path);
    }

    /**
     * Get the time remaining until due date
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->is_overdue) {
            return 'Overdue';
        }

        $diff = now()->diff($this->due_date);
        return $diff->days . ' days ' . $diff->h . ' hours';
    }
}
