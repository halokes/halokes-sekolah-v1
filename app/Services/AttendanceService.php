<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\AttendanceRepository;

class AttendanceService
{
    private $attendanceRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * =============================================
     *  list all attendances along with filter, sort, etc
     * =============================================
     */
    public function listAllAttendances($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->attendanceRepository->getAllAttendances($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single attendance data
     * =============================================
     */
    public function getAttendanceDetail($attendanceId): ?Attendance
    {
        return $this->attendanceRepository->getAttendanceById($attendanceId);
    }

    /**
     * =============================================
     * process add new attendance to database
     * =============================================
     */
    public function addNewAttendance(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check for existing attendance
            if ($this->attendanceRepository->checkExistingAttendance(
                $validatedData['enrollment_id'],
                $validatedData['attendance_date']
            )) {
                throw new \Exception("Attendance record already exists for this student on this date");
            }

            $attendance = $this->attendanceRepository->createAttendance($validatedData);
            DB::commit();
            return $attendance;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new attendance to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update attendance data
     * =============================================
     */
    public function updateAttendance(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $attendance = $this->attendanceRepository->getAttendanceById($id);

            if (!$attendance) {
                throw new \Exception("Attendance not found");
            }

            // Check for existing attendance (excluding current record)
            if ($this->attendanceRepository->checkExistingAttendance(
                $validatedData['enrollment_id'],
                $validatedData['attendance_date'],
                $id
            )) {
                throw new \Exception("Attendance record already exists for this student on this date");
            }

            $attendance = $this->attendanceRepository->update($id, $validatedData);
            DB::commit();
            return $attendance;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update attendance in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete attendance
     * =============================================
     */
    public function deleteAttendance($attendanceId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->attendanceRepository->delete($attendanceId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete attendance with id $attendanceId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get today's attendances
     * =============================================
     */
    public function getTodayAttendances($classId = null)
    {
        return $this->attendanceRepository->getTodayAttendances($classId);
    }

    /**
     * =============================================
     * get attendances by student
     * =============================================
     */
    public function getAttendancesByStudent($studentId, $academicYearId = null)
    {
        return $this->attendanceRepository->getAttendancesByStudent($studentId, $academicYearId);
    }

    /**
     * =============================================
     * get attendances by class
     * =============================================
     */
    public function getAttendancesByClass($classId, $academicYearId = null, $startDate = null, $endDate = null)
    {
        return $this->attendanceRepository->getAttendancesByClass($classId, $academicYearId, $startDate, $endDate);
    }

    /**
     * =============================================
     * get attendances by teacher
     * =============================================
     */
    public function getAttendancesByTeacher($teacherId, $startDate = null, $endDate = null)
    {
        return $this->attendanceRepository->getAttendancesByTeacher($teacherId, $startDate, $endDate);
    }

    /**
     * =============================================
     * get attendance statistics
     * =============================================
     */
    public function getAttendanceStatistics($studentId, $academicYearId = null): array
    {
        return $this->attendanceRepository->getAttendanceStatistics($studentId, $academicYearId);
    }

    /**
     * =============================================
     * get class attendance statistics
     * =============================================
     */
    public function getClassAttendanceStatistics($classId, $academicYearId = null, $startDate = null, $endDate = null): array
    {
        return $this->attendanceRepository->getClassAttendanceStatistics($classId, $academicYearId, $startDate, $endDate);
    }

    /**
     * =============================================
     * get monthly attendance report
     * =============================================
     */
    public function getMonthlyAttendanceReport($classId, $year, $month)
    {
        return $this->attendanceRepository->getMonthlyAttendanceReport($classId, $year, $month);
    }

    /**
     * =============================================
     * get student attendance by month
     * =============================================
     */
    public function getStudentAttendanceByMonth($studentId, $year)
    {
        return $this->attendanceRepository->getStudentAttendanceByMonth($studentId, $year);
    }

    /**
     * =============================================
     * check for existing attendance
     * =============================================
     */
    public function checkExistingAttendance($enrollmentId, $date, $excludeId = null): bool
    {
        return $this->attendanceRepository->checkExistingAttendance($enrollmentId, $date, $excludeId);
    }

    /**
     * =============================================
     * bulk create attendance
     * =============================================
     */
    public function bulkCreateAttendance(array $attendanceData)
    {
        DB::beginTransaction();
        try {
            $result = $this->attendanceRepository->bulkCreateAttendance($attendanceData);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to bulk create attendance records: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * update attendance status
     * =============================================
     */
    public function updateAttendanceStatus($enrollmentId, $date, $status)
    {
        return $this->attendanceRepository->updateAttendanceStatus($enrollmentId, $date, $status);
    }

    /**
     * =============================================
     * get attendance by date range
     * =============================================
     */
    public function getAttendanceByDateRange($startDate, $endDate, $classId = null)
    {
        return $this->attendanceRepository->getAttendanceByDateRange($startDate, $endDate, $classId);
    }

    /**
     * =============================================
     * get weekly attendance report
     * =============================================
     */
    public function getWeeklyAttendanceReport($classId, $startDate, $endDate)
    {
        return $this->attendanceRepository->getWeeklyAttendanceReport($classId, $startDate, $endDate);
    }

    /**
     * =============================================
     * mark attendance for multiple students
     * =============================================
     */
    public function markAttendanceForMultipleStudents(array $attendanceData)
    {
        DB::beginTransaction();
        try {
            $results = [];
            foreach ($attendanceData as $data) {
                // Check for existing attendance
                if (!$this->checkExistingAttendance($data['enrollment_id'], $data['attendance_date'])) {
                    $results[] = $this->attendanceRepository->createAttendance($data);
                }
            }
            DB::commit();
            return $results;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to mark attendance for multiple students: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get attendance dashboard data
     * =============================================
     */
    public function getAttendanceDashboardData($classId = null, $academicYearId = null)
    {
        $today = now();
        $startOfMonth = $today->startOfMonth();
        $endOfMonth = $today->endOfMonth();

        $data = [
            'today' => [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
            ],
            'this_month' => [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
            ],
            'this_year' => [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
            ],
        ];

        if ($classId) {
            // Today's data
            $todayAttendances = $this->getAttendancesByClass($classId, $academicYearId, $today, $today);
            $data['today']['total'] = $todayAttendances->count();
            $data['today']['present'] = $todayAttendances->where('status', 'present')->count();
            $data['today']['absent'] = $todayAttendances->whereIn('status', ['absent', 'sick'])->count();
            $data['today']['late'] = $todayAttendances->where('status', 'late')->count();

            // This month's data
            $monthAttendances = $this->getAttendancesByClass($classId, $academicYearId, $startOfMonth, $endOfMonth);
            $data['this_month']['total'] = $monthAttendances->count();
            $data['this_month']['present'] = $monthAttendances->where('status', 'present')->count();
            $data['this_month']['absent'] = $monthAttendances->whereIn('status', ['absent', 'sick'])->count();
            $data['this_month']['late'] = $monthAttendances->where('status', 'late')->count();

            // This year's data
            $yearAttendances = $this->getAttendancesByClass($classId, $academicYearId, $today->startOfYear(), $today->endOfYear());
            $data['this_year']['total'] = $yearAttendances->count();
            $data['this_year']['present'] = $yearAttendances->where('status', 'present')->count();
            $data['this_year']['absent'] = $yearAttendances->whereIn('status', ['absent', 'sick'])->count();
            $data['this_year']['late'] = $yearAttendances->where('status', 'late')->count();
        }

        return $data;
    }
}
