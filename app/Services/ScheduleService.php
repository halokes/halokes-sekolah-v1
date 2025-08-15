<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ScheduleRepository;

class ScheduleService
{
    private $scheduleRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(ScheduleRepository $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * =============================================
     *  list all schedules along with filter, sort, etc
     * =============================================
     */
    public function listAllSchedules($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->scheduleRepository->getAllSchedules($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single schedule data
     * =============================================
     */
    public function getScheduleDetail($scheduleId): ?Schedule
    {
        return $this->scheduleRepository->getScheduleById($scheduleId);
    }

    /**
     * =============================================
     * process add new schedule to database
     * =============================================
     */
    public function addNewSchedule(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check for time conflicts
            if ($this->scheduleRepository->checkTimeConflict(
                null,
                $validatedData['day_of_week'],
                $validatedData['start_time'],
                $validatedData['end_time'],
                $validatedData['class_id'] ?? null,
                $validatedData['teacher_id'] ?? null
            )) {
                throw new \Exception("Schedule conflicts with existing schedule for this class/teacher");
            }

            $schedule = $this->scheduleRepository->createSchedule($validatedData);
            DB::commit();
            return $schedule;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new schedule to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update schedule data
     * =============================================
     */
    public function updateSchedule(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $schedule = $this->scheduleRepository->getScheduleById($id);

            if (!$schedule) {
                throw new \Exception("Schedule not found");
            }

            // Check for time conflicts (excluding current record)
            if ($this->scheduleRepository->checkTimeConflict(
                $id,
                $validatedData['day_of_week'],
                $validatedData['start_time'],
                $validatedData['end_time'],
                $validatedData['class_id'] ?? null,
                $validatedData['teacher_id'] ?? null
            )) {
                throw new \Exception("Schedule conflicts with existing schedule for this class/teacher");
            }

            $schedule = $this->scheduleRepository->update($id, $validatedData);
            DB::commit();
            return $schedule;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update schedule in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete schedule
     * =============================================
     */
    public function deleteSchedule($scheduleId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->scheduleRepository->delete($scheduleId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete schedule with id $scheduleId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get active schedules
     * =============================================
     */
    public function getActiveSchedules()
    {
        return $this->scheduleRepository->getActiveSchedules();
    }

    /**
     * =============================================
     * get inactive schedules
     * =============================================
     */
    public function getInactiveSchedules()
    {
        return $this->scheduleRepository->getInactiveSchedules();
    }

    /**
     * =============================================
     * get schedules by day
     * =============================================
     */
    public function getSchedulesByDay($day)
    {
        return $this->scheduleRepository->getSchedulesByDay($day);
    }

    /**
     * =============================================
     * get schedules by teacher
     * =============================================
     */
    public function getSchedulesByTeacher($teacherId)
    {
        return $this->scheduleRepository->getSchedulesByTeacher($teacherId);
    }

    /**
     * =============================================
     * get schedules by class
     * =============================================
     */
    public function getSchedulesByClass($classId)
    {
        return $this->scheduleRepository->getSchedulesByClass($classId);
    }

    /**
     * =============================================
     * get schedules by subject
     * =============================================
     */
    public function getSchedulesBySubject($subjectId)
    {
        return $this->scheduleRepository->getSchedulesBySubject($subjectId);
    }

    /**
     * =============================================
     * get schedules by academic year
     * =============================================
     */
    public function getSchedulesByAcademicYear($academicYearId)
    {
        return $this->scheduleRepository->getSchedulesByAcademicYear($academicYearId);
    }

    /**
     * =============================================
     * get today's schedules
     * =============================================
     */
    public function getTodaySchedules()
    {
        return $this->scheduleRepository->getTodaySchedules();
    }

    /**
     * =============================================
     * get schedules happening now
     * =============================================
     */
    public function getHappeningNowSchedules()
    {
        return $this->scheduleRepository->getHappeningNowSchedules();
    }

    /**
     * =============================================
     * get weekly schedule
     * =============================================
     */
    public function getWeeklySchedule($classId = null, $teacherId = null)
    {
        return $this->scheduleRepository->getWeeklySchedule($classId, $teacherId);
    }

    /**
     * =============================================
     * get teacher schedule for week
     * =============================================
     */
    public function getTeacherScheduleForWeek($teacherId, $startDate = null)
    {
        return $this->scheduleRepository->getTeacherScheduleForWeek($teacherId, $startDate);
    }

    /**
     * =============================================
     * get class schedule for week
     * =============================================
     */
    public function getClassScheduleForWeek($classId, $startDate = null)
    {
        return $this->scheduleRepository->getClassScheduleForWeek($classId, $startDate);
    }

    /**
     * =============================================
     * check for time conflict
     * =============================================
     */
    public function checkTimeConflict($scheduleId, $day, $startTime, $endTime, $classId = null, $teacherId = null): bool
    {
        return $this->scheduleRepository->checkTimeConflict($scheduleId, $day, $startTime, $endTime, $classId, $teacherId);
    }

    /**
     * =============================================
     * get schedule statistics
     * =============================================
     */
    public function getScheduleStatistics($scheduleId): array
    {
        return $this->scheduleRepository->getScheduleStatistics($scheduleId);
    }

    /**
     * =============================================
     * get next schedule
     * =============================================
     */
    public function getNextSchedule($classId = null, $teacherId = null)
    {
        return $this->scheduleRepository->getNextSchedule($classId, $teacherId);
    }

    /**
     * =============================================
     * toggle schedule status
     * =============================================
     */
    public function toggleScheduleStatus($scheduleId): ?Schedule
    {
        DB::beginTransaction();
        try {
            $schedule = $this->scheduleRepository->toggleStatus($scheduleId);
            DB::commit();
            return $schedule;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle schedule status with id $scheduleId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get schedule calendar view data
     * =============================================
     */
    public function getScheduleCalendarData($classId = null, $teacherId = null, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = now()->startOfWeek();
        }

        if (!$endDate) {
            $endDate = now()->endOfWeek();
        }

        $query = Schedule::active()
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($classId) {
            $query->forClass($classId);
        }

        if ($teacherId) {
            $query->forTeacher($teacherId);
        }

        return $query->get();
    }
}
