<?php

namespace App\Repositories;

use App\Models\Enrollment;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EnrollmentRepository
{
    public function getAllEnrollments(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Enrollment::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("enrollment_date", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getEnrollmentById($enrollmentId): ?Enrollment
    {
        return Enrollment::find($enrollmentId);
    }

    public function createEnrollment($data)
    {
        return Enrollment::create($data);
    }

    public function update($enrollmentId, $data)
    {
        $enrollment = Enrollment::find($enrollmentId);
        if ($enrollment) {
            $enrollment->update($data);
            return $enrollment;
        } else {
            throw new Exception("Enrollment not found");
        }
    }

    public function delete($enrollmentId): ?bool
    {
        try {
            $enrollment = Enrollment::findOrFail($enrollmentId);
            $enrollment->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveEnrollments()
    {
        return Enrollment::active()->orderBy('enrollment_date', 'desc')->get();
    }

    public function getInactiveEnrollments()
    {
        return Enrollment::inactive()->orderBy('enrollment_date', 'desc')->get();
    }

    public function getEnrollmentsByStudent($studentId)
    {
        return Enrollment::forStudent($studentId)->orderBy('enrollment_date', 'desc')->get();
    }

    public function getEnrollmentsByClass($classId)
    {
        return Enrollment::forClass($classId)->orderBy('enrollment_date', 'desc')->get();
    }

    public function getEnrollmentsByAcademicYear($academicYearId)
    {
        return Enrollment::forAcademicYear($academicYearId)->orderBy('enrollment_date', 'desc')->get();
    }

    public function getCurrentEnrollments($studentId = null)
    {
        $query = Enrollment::current();

        if ($studentId) {
            $query->forStudent($studentId);
        }

        return $query->orderBy('enrollment_date', 'desc')->get();
    }

    public function getEnrollmentsByStatus($status)
    {
        return Enrollment::withStatus($status)->orderBy('enrollment_date', 'desc')->get();
    }

    public function getEnrollmentsByAdmissionNumber($admissionNumber)
    {
        return Enrollment::byAdmissionNumber($admissionNumber)->first();
    }

    public function getEnrollmentStatistics($enrollmentId): array
    {
        $enrollment = $this->getEnrollmentById($enrollmentId);
        if (!$enrollment) {
            return [];
        }

        return [
            'attendance_rate' => $enrollment->attendance_rate,
            'average_grade' => $enrollment->average_grade,
            'duration_in_days' => $enrollment->duration_in_days,
            'total_attendances' => $enrollment->attendances()->count(),
            'total_grades' => $enrollment->grades()->count(),
        ];
    }

    public function getStudentEnrollmentHistory($studentId)
    {
        return Enrollment::forStudent($studentId)
            ->with(['class', 'academicYear'])
            ->orderBy('enrollment_date', 'desc')
            ->get();
    }

    public function getClassEnrollmentList($classId, $academicYearId = null)
    {
        $query = Enrollment::forClass($classId)
            ->with(['student'])
            ->active()
            ->orderBy('class_rank');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function updateClassRankings($classId, $academicYearId)
    {
        DB::beginTransaction();
        try {
            $enrollments = Enrollment::forClass($classId)
                ->forAcademicYear($academicYearId)
                ->active()
                ->orderBy('enrollment_date')
                ->get();

            $rank = 1;
            foreach ($enrollments as $enrollment) {
                $enrollment->update(['class_rank' => $rank]);
                $rank++;
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function checkExistingEnrollment($studentId, $classId, $academicYearId, $excludeId = null): bool
    {
        $query = Enrollment::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getEnrollmentsByDateRange($startDate, $endDate)
    {
        return Enrollment::dateRange($startDate, $endDate)
            ->with(['student', 'class'])
            ->orderBy('enrollment_date', 'desc')
            ->get();
    }

    public function getGraduatedEnrollments($academicYearId)
    {
        return Enrollment::graduated()
            ->forAcademicYear($academicYearId)
            ->with(['student', 'class'])
            ->orderBy('graduation_date', 'desc')
            ->get();
    }

    public function promoteStudents($fromAcademicYearId, $toAcademicYearId, $fromClassId, $toClassId)
    {
        DB::beginTransaction();
        try {
            $enrollments = Enrollment::active()
                ->forAcademicYear($fromAcademicYearId)
                ->forClass($fromClassId)
                ->get();

            foreach ($enrollments as $enrollment) {
                $this->createEnrollment([
                    'student_id' => $enrollment->student_id,
                    'class_id' => $toClassId,
                    'academic_year_id' => $toAcademicYearId,
                    'status' => 'active',
                    'enrollment_date' => now(),
                    'notes' => 'Auto-promoted from previous class'
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
