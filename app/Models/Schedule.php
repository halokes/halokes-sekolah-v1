<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schedules';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'notes',
        'is_active',
        'academic_year_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeDayOfWeek($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeForDay($query, $day)
    {
        $days = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];

        $dayName = $days[strtolower($day)] ?? $day;
        return $query->where('day_of_week', $dayName);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeTimeRange($query, $startTime, $endTime)
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function ($subQuery) use ($startTime, $endTime) {
                  $subQuery->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
              });
        });
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('class', function ($classQuery) use ($keyword) {
                $classQuery->where('name', 'like', '%' . $keyword . '%')
                          ->orWhere('class_code', 'like', '%' . $keyword . '%');
            })->orWhereHas('subject', function ($subjectQuery) use ($keyword) {
                $subjectQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('code', 'like', '%' . $keyword . '%');
            })->orWhereHas('teacher', function ($teacherQuery) use ($keyword) {
                $teacherQuery->where('name', 'like', '%' . $keyword . '%');
            })->orWhere('day_of_week', 'like', '%' . $keyword . '%')
              ->orWhere('room', 'like', '%' . $keyword . '%')
              ->orWhere('notes', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the day name in a more readable format
     */
    public function getDayNameAttribute()
    {
        return $this->day_of_week;
    }

    /**
     * Get the formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    /**
     * Get the duration in minutes
     */
    public function getDurationAttribute()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Check if the schedule is currently happening (based on current day and time)
     */
    public function getIsHappeningNowAttribute()
    {
        $now = now();
        $today = $now->format('l'); // Day name (Monday, Tuesday, etc.)
        $currentTime = $now->format('H:i:s');

        return $this->day_of_week === $today &&
               $currentTime >= $this->start_time->format('H:i:s') &&
               $currentTime <= $this->end_time->format('H:i:s');
    }

    /**
     * Check if the schedule is for today
     */
    public function getIsTodayAttribute()
    {
        return $this->day_of_week === now()->format('l');
    }

    /**
     * Get the next occurrence of this schedule
     */
    public function getNextOccurrenceAttribute()
    {
        $today = now();
        $currentDayOfWeek = strtolower($today->format('l'));

        $daysOfWeek = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6
        ];

        $scheduleDayOfWeek = strtolower($this->day_of_week);
        $currentDayIndex = $daysOfWeek[$currentDayOfWeek];
        $scheduleDayIndex = $daysOfWeek[$scheduleDayOfWeek];

        // If the schedule is for today and the time has passed, return next week
        if ($scheduleDayIndex === $currentDayIndex &&
            now()->format('H:i:s') > $this->start_time->format('H:i:s')) {
            return $this->start_time->addWeek();
        }

        // Calculate days until next occurrence
        $daysToAdd = ($scheduleDayIndex - $currentDayIndex + 7) % 7;
        if ($daysToAdd === 0) {
            $daysToAdd = 7; // If it's the same day, schedule for next week
        }

        return $this->start_time->addDays($daysToAdd);
    }

    /**
     * Scope to get schedules for a specific time slot
     */
    public function scopeForTimeSlot($query, $day, $startTime, $endTime)
    {
        return $query->where('day_of_week', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<=', $endTime)
                  ->whereTime('end_time', '>=', $startTime);
            });
    }
}
