<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'enrollment_id',
        'teacher_id',
        'attendance_date',
        'status',
        'notes',
        'check_in_time',
        'check_out_time',
        'attendance_type',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Constants
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_EXCUSE = 'excuse';
    const STATUS_SICK = 'sick';

    const TYPE_DAILY = 'daily';
    const TYPE_WEEKLY = 'weekly';
    const TYPE_MONTHLY = 'monthly';

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student()
    {
        return $this->hasOneThrough(User::class, Enrollment::class, 'id', 'id', 'enrollment_id', 'student_id');
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }

    public function scopeLate($query)
    {
        return $query->where('status', self::STATUS_LATE);
    }

    public function scopeExcuse($query)
    {
        return $query->where('status', self::STATUS_EXCUSE);
    }

    public function scopeSick($query)
    {
        return $query->where('status', self::STATUS_SICK);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
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

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->whereHas('enrollment', function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId);
        });
    }

    public function scopeDaily($query)
    {
        return $query->where('attendance_type', self::TYPE_DAILY);
    }

    public function scopeWeekly($query)
    {
        return $query->where('attendance_type', self::TYPE_WEEKLY);
    }

    public function scopeMonthly($query)
    {
        return $query->where('attendance_type', self::TYPE_MONTHLY);
    }

    // Accessors
    public function getIsPresentAttribute()
    {
        return $this->status === self::STATUS_PRESENT;
    }

    public function getIsAbsentAttribute()
    {
        return $this->status === self::STATUS_ABSENT;
    }

    public function getIsLateAttribute()
    {
        return $this->status === self::STATUS_LATE;
    }

    public function getIsExcuseAttribute()
    {
        return $this->status === self::STATUS_EXCUSE;
    }

    public function getIsSickAttribute()
    {
        return $this->status === self::STATUS_SICK;
    }

    public function getDurationAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        $start = \Carbon\Carbon::parse($this->check_in_time);
        $end = \Carbon\Carbon::parse($this->check_out_time);
        return $start->diffInMinutes($end);
    }

    public function getFormattedCheckInTimeAttribute()
    {
        return $this->check_in_time ? \Carbon\Carbon::parse($this->check_in_time)->format('H:i') : '-';
    }

    public function getFormattedCheckOutTimeAttribute()
    {
        return $this->check_out_time ? \Carbon\Carbon::parse($this->check_out_time)->format('H:i') : '-';
    }

    // Mutators
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function setCheckInTimeAttribute($value)
    {
        $this->attributes['check_in_time'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function setCheckOutTimeAttribute($value)
    {
        $this->attributes['check_out_time'] = $value ? \Carbon\Carbon::parse($value) : null;
    }
}
