<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

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
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'integer',
        'is_late' => 'boolean',
        'days_late' => 'integer',
    ];

    // Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_GRADED = 'graded';
    const STATUS_RETURNED = 'returned';

    // Relationships
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeGraded($query)
    {
        return $query->where('status', self::STATUS_GRADED);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', self::STATUS_RETURNED);
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

    public function scopeForClass($query, $classId)
    {
        return $query->whereHas('assignment', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        });
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->whereHas('assignment', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        });
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->whereHas('assignment', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        });
    }

    public function scopeGradedBy($query, $graderId)
    {
        return $query->where('graded_by', $graderId);
    }

    // Accessors
    public function getIsDraftAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getIsSubmittedAttribute()
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function getIsGradedAttribute()
    {
        return $this->status === self::STATUS_GRADED;
    }

    public function getIsReturnedAttribute()
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function getHasFileAttribute()
    {
        return !empty($this->file_path) && !empty($this->file_name);
    }

    public function getHasContentAttribute()
    {
        return !empty($this->content);
    }

    public function getHasFeedbackAttribute()
    {
        return !empty($this->feedback);
    }

    public function getFormattedSubmittedAtAttribute()
    {
        return $this->submitted_at ? $this->submitted_at->format('d M Y H:i') : 'Not submitted';
    }

    public function getFormattedGradedAtAttribute()
    {
        return $this->graded_at ? $this->graded_at->format('d M Y H:i') : 'Not graded';
    }

    public function getScorePercentageAttribute()
    {
        if (!$this->assignment || !$this->assignment->max_score || !$this->score) {
            return 0;
        }

        return round(($this->score / $this->assignment->max_score) * 100, 2);
    }

    public function getGradeLabelAttribute()
    {
        if (!$this->grade) {
            return 'No grade';
        }

        return $this->grade . ($this->score ? ' (' . $this->score . ')' : '');
    }

    public function getLatePenaltyAppliedAttribute()
    {
        return $this->is_late && $this->days_late > 0;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_GRADED => 'Graded',
            self::STATUS_RETURNED => 'Returned',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    // Mutators
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function setIsLateAttribute($value)
    {
        $this->attributes['is_late'] = (bool) $value;
    }

    public function setSubmittedAtAttribute($value)
    {
        $this->attributes['submitted_at'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function setGradedAtAttribute($value)
    {
        $this->attributes['graded_at'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    // Helper methods
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_GRADED => 'Graded',
            self::STATUS_RETURNED => 'Returned',
        ];
    }

    public function canBeGraded()
    {
        return $this->status === self::STATUS_SUBMITTED && !$this->isGraded;
    }

    public function canBeSubmitted()
    {
        return ($this->hasContent || $this->hasFile) && !$this->isSubmitted;
    }

    public function calculateLatePenalty()
    {
        if (!$this->is_late || !$this->assignment || !$this->assignment->allow_late_submission) {
            return 0;
        }

        $penaltyPercent = $this->assignment->late_penalty_percent;
        $maxScore = $this->assignment->max_score;

        return round(($maxScore * $penaltyPercent / 100) * $this->days_late, 2);
    }

    public function getFinalScoreAttribute()
    {
        if (!$this->score) {
            return null;
        }

        $penalty = $this->calculateLatePenalty();
        $finalScore = max(0, $this->score - $penalty);

        return round($finalScore, 2);
    }

    public function getFinalScorePercentageAttribute()
    {
        if (!$this->assignment || !$this->assignment->max_score || !$this->final_score) {
            return 0;
        }

        return round(($this->final_score / $this->assignment->max_score) * 100, 2);
    }
}
