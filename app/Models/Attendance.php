<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendances';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'enrollment_id',
        'teacher_id',
        'attendance_date',
        'status',
        'notes',
        'check_in_time',
        'check_out_time',
        'attendance_type',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrollment_id', 'enrollment_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeExcuse($query)
    {
        return $query->where('status', 'excuse');
    }

    public function scopeSick($query)
    {
        return $query->where('status', 'sick');
    }

    public function scopeDaily($query)
    {
        return $query->where('attendance_type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('attendance_type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('attendance_type', 'monthly');
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

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('enrollment.student', function ($studentQuery) use ($keyword) {
                $studentQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhereHas('enrollment.class', function ($classQuery) use ($keyword) {
                $classQuery->where('name', 'like', '%' . $keyword . '%')
                          ->orWhere('class_code', 'like', '%' . $keyword . '%');
            })->orWhere('status', 'like', '%' . $keyword . '%')
              ->orWhere('notes', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the status name in a more readable format
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'excuse' => 'Excuse',
            'sick' => 'Sick'
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get the attendance type name in a more readable format
     */
    public function getAttendanceTypeNameAttribute()
    {
        $types = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly'
        ];

        return $types[$this->attendance_type] ?? ucfirst($this->attendance_type);
    }

    /**
     * Check if attendance is for today
     */
    public function getIsTodayAttribute()
    {
        return $this->attendance_date->isToday();
    }

    /**
     * Check if student was late
     */
    public function getIsLateAttribute()
    {
        return $this->status === 'late';
    }

    /**
     * Check if student was absent
     */
    public function getIsAbsentAttribute()
    {
        return in_array($this->status, ['absent', 'sick']);
    }

    /**
     * Get the duration in minutes (if check-in and check-out are recorded)
     */
    public function getDurationAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        return null;
    }

    /**
     * Get the formatted time range
     */
    public function getTimeRangeAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->format('H:i') . ' - ' . $this->check_out_time->format('H:i');
        } elseif ($this->check_in_time) {
            return $this->check_in_time->format('H:i') . ' - ';
        }
        return '';
    }

    /**
     * Scope to get attendance records by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get attendance records by attendance type
     */
    public function scopeWithType($query, $type)
    {
        return $query->where('attendance_type', $type);
    }

    /**
     * Scope to get attendance records with check-in and check-out times
     */
    public function scopeWithCheckInOut($query)
    {
        return $query->whereNotNull('check_in_time')->whereNotNull('check_out_time');
    }

    /**
     * Scope to get attendance records without check-in and check-out times
     */
    public function scopeWithoutCheckInOut($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('check_in_time')->orWhereNull('check_out_time');
        });
    }

    /**
     * Get the student's full information
     */
    public function getStudentInfoAttribute()
    {
        return $this->enrollment->student_info;
    }

    /**
     * Get the class information
     */
    public function getClassInfoAttribute()
    {
        return $this->enrollment->class;
    }

    /**
     * Get the week number of the attendance date
     */
    public function getWeekNumberAttribute()
    {
        return $this->attendance_date->weekOfYear;
    }

    /**
     * Get the month name of the attendance date
     */
    public function getMonthNameAttribute()
    {
        return $this->attendance_date->format('F');
    }
}
