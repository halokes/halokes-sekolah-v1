<?php

namespace App\Repositories;

use App\Models\Attendance;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttendanceRepository
{
    public function getAllAttendances(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Attendance::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("attendance_date", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getAttendanceById($attendanceId): ?Attendance
    {
        return Attendance::find($attendanceId);
    }

    public function createAttendance($data)
    {
        return Attendance::create($data);
    }

    public function update($attendanceId, $data)
    {
        $attendance = Attendance::find($attendanceId);
        if ($attendance) {
            $attendance->update($data);
            return $attendance;
        } else {
            throw new Exception("Attendance not found");
        }
    }

    public function delete($attendanceId): ?bool
    {
        try {
            $attendance = Attendance::findOrFail($attendanceId);
            $attendance->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getTodayAttendances($classId = null)
    {
        $query = Attendance::today();

        if ($classId) {
            $query->forClass($classId);
        }

        return $query->with(['enrollment.student', 'enrollment.class'])->get();
    }

    public function getAttendancesByStudent($studentId, $academicYearId = null)
    {
        $query = Attendance::forStudent($studentId)
            ->with(['enrollment.class', 'enrollment.academicYear', 'teacher'])
            ->orderBy('attendance_date', 'desc');

        if ($academicYearId) {
            $query->whereHas('enrollment.academicYear', function ($academicYearQuery) use ($academicYearId) {
                $academicYearQuery->where('id', $academicYearId);
            });
        }

        return $query->get();
    }

    public function getAttendancesByClass($classId, $academicYearId = null, $startDate = null, $endDate = null)
    {
        $query = Attendance::forClass($classId)
            ->with(['enrollment.student', 'teacher'])
            ->orderBy('attendance_date', 'desc');

        if ($academicYearId) {
            $query->whereHas('enrollment.academicYear', function ($academicYearQuery) use ($academicYearId) {
                $academicYearQuery->where('id', $academicYearId);
            });
        }

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get();
    }

    public function getAttendancesByTeacher($teacherId, $startDate = null, $endDate = null)
    {
        $query = Attendance::forTeacher($teacherId)
            ->with(['enrollment.student', 'enrollment.class'])
            ->orderBy('attendance_date', 'desc');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get();
    }

    public function getAttendanceStatistics($studentId, $academicYearId = null): array
    {
        $query = Attendance::forStudent($studentId);

        if ($academicYearId) {
            $query->whereHas('enrollment.academicYear', function ($academicYearQuery) use ($academicYearId) {
                $academicYearQuery->where('id', $academicYearId);
            });
        }

        $totalDays = $query->count();
        $presentDays = $query->present()->count();
        $absentDays = $query->absent()->count();
        $lateDays = $query->late()->count();
        $sickDays = $query->sick()->count();
        $excuseDays = $query->excuse()->count();

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'sick_days' => $sickDays,
            'excuse_days' => $excuseDays,
            'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
        ];
    }

    public function getClassAttendanceStatistics($classId, $academicYearId = null, $startDate = null, $endDate = null): array
    {
        $query = Attendance::forClass($classId);

        if ($academicYearId) {
            $query->whereHas('enrollment.academicYear', function ($academicYearQuery) use ($academicYearId) {
                $academicYearQuery->where('id', $academicYearId);
            });
        }

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $totalRecords = $query->count();
        $presentRecords = $query->present()->count();
        $absentRecords = $query->absent()->count();
        $lateRecords = $query->late()->count();
        $sickRecords = $query->sick()->count();
        $excuseRecords = $query->excuse()->count();

        return [
            'total_records' => $totalRecords,
            'present_records' => $presentRecords,
            'absent_records' => $absentRecords,
            'late_records' => $lateRecords,
            'sick_records' => $sickRecords,
            'excuse_records' => $excuseRecords,
            'attendance_rate' => $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 2) : 0,
        ];
    }

    public function getMonthlyAttendanceReport($classId, $year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = "$year-$month-" . now()->parse($startDate)->endOfMonth()->day;

        return Attendance::forClass($classId)
            ->dateRange($startDate, $endDate)
            ->with(['enrollment.student'])
            ->orderBy('attendance_date')
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->attendance_date->format('Y-m-d');
            });
    }

    public function getStudentAttendanceByMonth($studentId, $year)
    {
        return Attendance::forStudent($studentId)
            ->whereYear('attendance_date', $year)
            ->with(['enrollment.class'])
            ->orderBy('attendance_date')
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->attendance_date->format('Y-m');
            });
    }

    public function checkExistingAttendance($enrollmentId, $date, $excludeId = null): bool
    {
        $query = Attendance::where('enrollment_id', $enrollmentId)
            ->where('attendance_date', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function bulkCreateAttendance($attendanceData)
    {
        DB::beginTransaction();
        try {
            $attendances = [];
            foreach ($attendanceData as $data) {
                // Check for existing attendance
                if (!$this->checkExistingAttendance($data['enrollment_id'], $data['attendance_date'])) {
                    $attendances[] = Attendance::create($data);
                }
            }
            DB::commit();
            return $attendances;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function updateAttendanceStatus($enrollmentId, $date, $status)
    {
        $attendance = Attendance::where('enrollment_id', $enrollmentId)
            ->where('attendance_date', $date)
            ->first();

        if ($attendance) {
            $attendance->status = $status;
            $attendance->save();
            return $attendance;
        }

        return null;
    }

    public function getAttendanceByDateRange($startDate, $endDate, $classId = null)
    {
        $query = Attendance::dateRange($startDate, $endDate)
            ->with(['enrollment.student', 'enrollment.class', 'teacher'])
            ->orderBy('attendance_date');

        if ($classId) {
            $query->forClass($classId);
        }

        return $query->get();
    }

    public function getWeeklyAttendanceReport($classId, $startDate, $endDate)
    {
        return Attendance::forClass($classId)
            ->dateRange($startDate, $endDate)
            ->with(['enrollment.student'])
            ->orderBy('attendance_date')
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->attendance_date->format('Y-m-d');
            });
    }
}
