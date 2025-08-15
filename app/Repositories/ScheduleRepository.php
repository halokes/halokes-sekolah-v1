<?php

namespace App\Repositories;

use App\Models\Schedule;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ScheduleRepository
{
    public function getAllSchedules(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Schedule::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy('day_of_week')->orderBy('start_time');
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getScheduleById($scheduleId): ?Schedule
    {
        return Schedule::find($scheduleId);
    }

    public function createSchedule($data)
    {
        return Schedule::create($data);
    }

    public function update($scheduleId, $data)
    {
        $schedule = Schedule::find($scheduleId);
        if ($schedule) {
            $schedule->update($data);
            return $schedule;
        } else {
            throw new Exception("Schedule not found");
        }
    }

    public function delete($scheduleId): ?bool
    {
        try {
            $schedule = Schedule::findOrFail($scheduleId);
            $schedule->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveSchedules()
    {
        return Schedule::active()->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function getInactiveSchedules()
    {
        return Schedule::inactive()->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function getSchedulesByDay($day)
    {
        return Schedule::active()->forDay($day)->orderBy('start_time')->get();
    }

    public function getSchedulesByTeacher($teacherId)
    {
        return Schedule::active()->forTeacher($teacherId)->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function getSchedulesByClass($classId)
    {
        return Schedule::active()->forClass($classId)->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function getSchedulesBySubject($subjectId)
    {
        return Schedule::active()->forSubject($subjectId)->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function getSchedulesByAcademicYear($academicYearId)
    {
        return Schedule::active()->forAcademicYear($academicYearId)->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    public function getTodaySchedules()
    {
        return Schedule::active()->isToday()->orderBy('start_time')->get();
    }

    public function getHappeningNowSchedules()
    {
        return Schedule::active()->where('is_happening_now', true)->orderBy('start_time')->get();
    }

    public function getWeeklySchedule($classId = null, $teacherId = null)
    {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weeklySchedule = [];

        foreach ($daysOfWeek as $day) {
            $query = Schedule::active()->forDay($day);

            if ($classId) {
                $query->forClass($classId);
            }

            if ($teacherId) {
                $query->forTeacher($teacherId);
            }

            $weeklySchedule[$day] = $query->orderBy('start_time')->get();
        }

        return $weeklySchedule;
    }

    public function checkTimeConflict($scheduleId, $day, $startTime, $endTime, $classId = null, $teacherId = null)
    {
        $query = Schedule::query()
            ->where('id', '!=', $scheduleId)
            ->forTimeSlot($day, $startTime, $endTime)
            ->active();

        if ($classId) {
            $query->forClass($classId);
        }

        if ($teacherId) {
            $query->forTeacher($teacherId);
        }

        return $query->exists();
    }

    public function getTeacherScheduleForWeek($teacherId, $startDate = null)
    {
        if (!$startDate) {
            $startDate = now()->startOfWeek();
        }

        $endDate = $startDate->copy()->endOfWeek();

        return Schedule::active()
            ->forTeacher($teacherId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function getClassScheduleForWeek($classId, $startDate = null)
    {
        if (!$startDate) {
            $startDate = now()->startOfWeek();
        }

        $endDate = $startDate->copy()->endOfWeek();

        return Schedule::active()
            ->forClass($classId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function getScheduleStatistics($scheduleId): array
    {
        $schedule = $this->getScheduleById($scheduleId);
        if (!$schedule) {
            return [];
        }

        // Get related attendance records
        $attendanceCount = $schedule->attendances()->count();
        $presentCount = $schedule->attendances()->where('status', 'present')->count();

        return [
            'total_attendance' => $attendanceCount,
            'present_count' => $presentCount,
            'absent_count' => $attendanceCount - $presentCount,
            'attendance_rate' => $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 2) / 2 : 0,
        ];
    }

    public function getNextSchedule($classId = null, $teacherId = null)
    {
        $query = Schedule::active()->where('start_time', '>', now());

        if ($classId) {
            $query->forClass($classId);
        }

        if ($teacherId) {
            $query->forTeacher($teacherId);
        }

        return $query->orderBy('start_time')->first();
    }

    public function toggleStatus($scheduleId)
    {
        $schedule = Schedule::find($scheduleId);
        if ($schedule) {
            $schedule->is_active = !$schedule->is_active;
            $schedule->save();
            return $schedule;
        } else {
            throw new Exception("Schedule not found");
        }
    }
}
