<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'submissions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'file_path',
        'file_name',
        'score',
        'grade',
        'feedback',
        'submitted_at',
        'status',
        'is_late',
        'days_late',
        'late_penalty_notes',
        'graded_by',
        'graded_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'integer',
        'is_late' => 'boolean',
        'days_late' => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function files(): MorphMany
    {
        return $this->morphMany('App\Models\File', 'fileable');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeOnTime($query)
    {
        return $query->where('is_late', false);
    }

    public function scopeForAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForGrader($query, $gradedBy)
    {
        return $query->where('graded_by', $gradedBy);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('submitted_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('assignment', function ($assignmentQuery) use ($keyword) {
                $assignmentQuery->where('title', 'like', '%' . $keyword . '%')
                                ->orWhere('description', 'like', '%' . $keyword . '%');
            })->orWhereHas('student', function ($studentQuery) use ($keyword) {
                $studentQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhere('content', 'like', '%' . $keyword . '%')
              ->orWhere('feedback', 'like', '%' . $keyword . '%')
              ->orWhere('status', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the status name in a more readable format
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'graded' => 'Graded',
            'returned' => 'Returned'
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if the submission is late
     */
    public function getIsLateAttribute()
    {
        return $this->submitted_at > $this->assignment->due_date;
    }

    /**
     * Get the formatted submitted at date
     */
    public function getFormattedSubmittedAtAttribute()
    {
        return $this->submitted_at ? $this->submitted_at->format('d M Y H:i') : '-';
    }

    /**
     * Get the formatted graded at date
     */
    public function getFormattedGradedAtAttribute()
    {
        return $this->graded_at ? $this->graded_at->format('d M Y H:i') : '-';
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
     * Check if the submission has a file attachment
     */
    public function getHasFileAttribute()
    {
        return !is_null($this->file_path);
    }

    /**
     * Get the student's full information
     */
    public function getStudentInfoAttribute()
    {
        return $this->student;
    }

    /**
     * Get the assignment information
     */
    public function getAssignmentInfoAttribute()
    {
        return $this->assignment;
    }

    /**
     * Get the grader's information
     */
    public function getGraderInfoAttribute()
    {
        return $this->gradedBy;
    }

    /**
     * Get the calculated late penalty
     */
    public function getCalculatedLatePenaltyAttribute()
    {
        if (!$this->is_late || !$this->assignment->late_penalty_percent || !$this->assignment->max_score) {
            return 0;
        }

        $penaltyPerDay = ($this->assignment->max_score * $this->assignment->late_penalty_percent) / 100;
        return round($penaltyPerDay * $this->days_late, 2);
    }

    /**
     * Get the final score after penalty
     */
    public function getFinalScoreAttribute()
    {
        if (!$this->score) {
            return null;
        }

        return max(0, $this->score - $this->calculated_late_penalty);
    }

    /**
     * Get the letter grade based on final score
     */
    public function getFinalGradeAttribute()
    {
        if (is_null($this->final_score)) {
            return '-';
        }

        if ($this->final_score >= 90) return 'A';
        if ($this->final_score >= 80) return 'B';
        if ($this->final_score >= 70) return 'C';
        if ($this->final_score >= 60) return 'D';
        return 'E';
    }

    /**
     * Scope to get submissions by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get submissions by score range
     */
    public function scopeScoreRange($query, $minScore, $maxScore)
    {
        return $query->whereBetween('score', [$minScore, $maxScore]);
    }
}
