<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enrollments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'status',
        'enrollment_date',
        'graduation_date',
        'notes',
        'admission_number',
        'class_rank',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
        'class_rank' => 'integer'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', '!=', 'active');
    }

    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }

    public function scopeTransferred($query)
    {
        return $query->where('status', 'transferred');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('enrollment_date', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('student', function ($studentQuery) use ($keyword) {
                $studentQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhereHas('class', function ($classQuery) use ($keyword) {
                $classQuery->where('name', 'like', '%' . $keyword . '%')
                          ->orWhere('class_code', 'like', '%' . $keyword . '%');
            })->orWhere('admission_number', 'like', '%' . $keyword . '%')
              ->orWhere('status', 'like', '%' . $keyword . '%')
              ->orWhere('notes', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the status name in a more readable format
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'active' => 'Active',
            'graduated' => 'Graduated',
            'transferred' => 'Transferred',
            'suspended' => 'Suspended'
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if the enrollment is currently active
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Get the duration of enrollment in days
     */
    public function getDurationInDaysAttribute()
    {
        if ($this->graduation_date) {
            return $this->enrollment_date->diffInDays($this->graduation_date);
        }
        return $this->enrollment_date->diffInDays(now());
    }

    /**
     * Get the attendance rate for this enrollment
     */
    public function getAttendanceRateAttribute()
    {
        $totalAttendances = $this->attendances()->count();
        if ($totalAttendances === 0) {
            return 0;
        }

        $presentCount = $this->attendances()->where('status', 'present')->count();
        return round(($presentCount / $totalAttendances) * 100, 2);
    }

    /**
     * Get the average grade for this enrollment
    */
    public function getAverageGradeAttribute()
    {
        $grades = $this->grades()->whereNotNull('score')->pluck('score');
        if ($grades->isEmpty()) {
            return null;
        }

        return round($grades->avg(), 2);
    }

    /**
     * Get the current academic year enrollment
     */
    public function scopeCurrent($query)
    {
        return $query->where('academic_year_id', function ($subQuery) {
            $subQuery->select('id')
                    ->from('academic_years')
                    ->where('is_current', true)
                    ->limit(1);
        });
    }

    /**
     * Get the class name with code
     */
    public function getClassNameWithCodeAttribute()
    {
        return $this->class ? "{$this->class->name} ({$this->class->class_code})" : '';
    }

    /**
     * Get the student's full information
     */
    public function getStudentInfoAttribute()
    {
        return $this->student ? [
            'id' => $this->student->id,
            'name' => $this->student->name,
            'email' => $this->student->email,
            'phone' => $this->student->phone_number,
        ] : null;
    }

    /**
     * Scope to get enrollments with specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get enrollments by admission number
     */
    public function scopeByAdmissionNumber($query, $admissionNumber)
    {
        return $query->where('admission_number', $admissionNumber);
    }
}
