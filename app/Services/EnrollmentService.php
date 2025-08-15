<?php

namespace App\Services;

use App\Models\Enrollment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\EnrollmentRepository;

class EnrollmentService
{
    private $enrollmentRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(EnrollmentRepository $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    /**
     * =============================================
     *  list all enrollments along with filter, sort, etc
     * =============================================
     */
    public function listAllEnrollments($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->enrollmentRepository->getAllEnrollments($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single enrollment data
     * =============================================
     */
    public function getEnrollmentDetail($enrollmentId): ?Enrollment
    {
        return $this->enrollmentRepository->getEnrollmentById($enrollmentId);
    }

    /**
     * =============================================
     * process add new enrollment to database
     * =============================================
     */
    public function addNewEnrollment(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check for existing enrollment
            if ($this->enrollmentRepository->checkExistingEnrollment(
                $validatedData['student_id'],
                $validatedData['class_id'],
                $validatedData['academic_year_id']
            )) {
                throw new \Exception("Student is already enrolled in this class for the academic year");
            }

            $enrollment = $this->enrollmentRepository->createEnrollment($validatedData);
            DB::commit();
            return $enrollment;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new enrollment to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update enrollment data
     * =============================================
     */
    public function updateEnrollment(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $enrollment = $this->enrollmentRepository->getEnrollmentById($id);

            if (!$enrollment) {
                throw new \Exception("Enrollment not found");
            }

            // Check for existing enrollment (excluding current record)
            if ($this->enrollmentRepository->checkExistingEnrollment(
                $validatedData['student_id'],
                $validatedData['class_id'],
                $validatedData['academic_year_id'],
                $id
            )) {
                throw new \Exception("Student is already enrolled in this class for the academic year");
            }

            $enrollment = $this->enrollmentRepository->update($id, $validatedData);
            DB::commit();
            return $enrollment;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update enrollment in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete enrollment
     * =============================================
     */
    public function deleteEnrollment($enrollmentId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->enrollmentRepository->delete($enrollmentId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete enrollment with id $enrollmentId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get active enrollments
     * =============================================
     */
    public function getActiveEnrollments()
    {
        return $this->enrollmentRepository->getActiveEnrollments();
    }

    /**
     * =============================================
     * get inactive enrollments
     * =============================================
     */
    public function getInactiveEnrollments()
    {
        return $this->enrollmentRepository->getInactiveEnrollments();
    }

    /**
     * =============================================
     * get enrollments by student
     * =============================================
     */
    public function getEnrollmentsByStudent($studentId)
    {
        return $this->enrollmentRepository->getEnrollmentsByStudent($studentId);
    }

    /**
     * =============================================
     * get enrollments by class
     * =============================================
     */
    public function getEnrollmentsByClass($classId)
    {
        return $this->enrollmentRepository->getEnrollmentsByClass($classId);
    }

    /**
     * =============================================
     * get enrollments by academic year
     * =============================================
     */
    public function getEnrollmentsByAcademicYear($academicYearId)
    {
        return $this->enrollmentRepository->getEnrollmentsByAcademicYear($academicYearId);
    }

    /**
     * =============================================
     * get current enrollments
     * =============================================
     */
    public function getCurrentEnrollments($studentId = null)
    {
        return $this->enrollmentRepository->getCurrentEnrollments($studentId);
    }

    /**
     * =============================================
     * get enrollments by status
     * =============================================
     */
    public function getEnrollmentsByStatus($status)
    {
        return $this->enrollmentRepository->getEnrollmentsByStatus($status);
    }

    /**
     * =============================================
     * get enrollment by admission number
     * =============================================
     */
    public function getEnrollmentByAdmissionNumber($admissionNumber)
    {
        return $this->enrollmentRepository->getEnrollmentsByAdmissionNumber($admissionNumber);
    }

    /**
     * =============================================
     * get enrollment statistics
     * =============================================
     */
    public function getEnrollmentStatistics($enrollmentId): array
    {
        return $this->enrollmentRepository->getEnrollmentStatistics($enrollmentId);
    }

    /**
     * =============================================
     * get student enrollment history
     * =============================================
     */
    public function getStudentEnrollmentHistory($studentId)
    {
        return $this->enrollmentRepository->getStudentEnrollmentHistory($studentId);
    }

    /**
     * =============================================
     * get class enrollment list
     * =============================================
     */
    public function getClassEnrollmentList($classId, $academicYearId = null)
    {
        return $this->enrollmentRepository->getClassEnrollmentList($classId, $academicYearId);
    }

    /**
     * =============================================
     * update class rankings
     * =============================================
     */
    public function updateClassRankings($classId, $academicYearId): bool
    {
        DB::beginTransaction();
        try {
            $result = $this->enrollmentRepository->updateClassRankings($classId, $academicYearId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update class rankings: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * check for existing enrollment
     * =============================================
     */
    public function checkExistingEnrollment($studentId, $classId, $academicYearId, $excludeId = null): bool
    {
        return $this->enrollmentRepository->checkExistingEnrollment($studentId, $classId, $academicYearId, $excludeId);
    }

    /**
     * =============================================
     * get enrollments by date range
     * =============================================
     */
    public function getEnrollmentsByDateRange($startDate, $endDate)
    {
        return $this->enrollmentRepository->getEnrollmentsByDateRange($startDate, $endDate);
    }

    /**
     * =============================================
     * get graduated enrollments
     * =============================================
     */
    public function getGraduatedEnrollments($academicYearId)
    {
        return $this->enrollmentRepository->getGraduatedEnrollments($academicYearId);
    }

    /**
     * =============================================
     * promote students
     * =============================================
     */
    public function promoteStudents($fromAcademicYearId, $toAcademicYearId, $fromClassId, $toClassId): bool
    {
        DB::beginTransaction();
        try {
            $result = $this->enrollmentRepository->promoteStudents($fromAcademicYearId, $toAcademicYearId, $fromClassId, $toClassId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to promote students: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get class statistics
     * =============================================
     */
    public function getClassStatistics($classId, $academicYearId = null): array
    {
        $enrollments = $this->enrollmentRepository->getClassEnrollmentList($classId, $academicYearId);

        return [
            'total_students' => $enrollments->count(),
            'active_students' => $enrollments->where('status', 'active')->count(),
            'graduated_students' => $enrollments->where('status', 'graduated')->count(),
            'transferred_students' => $enrollments->where('status', 'transferred')->count(),
            'suspended_students' => $enrollments->where('status', 'suspended')->count(),
            'average_attendance_rate' => $enrollments->avg('attendance_rate') ?? 0,
            'average_grade' => $enrollments->avg('average_grade') ?? 0,
        ];
    }

    /**
     * =============================================
     * get student enrollment status
     * =============================================
     */
    public function getStudentEnrollmentStatus($studentId): array
    {
        $currentEnrollments = $this->getCurrentEnrollments($studentId);
        $enrollmentHistory = $this->getStudentEnrollmentHistory($studentId);

        return [
            'is_enrolled' => $currentEnrollments->isNotEmpty(),
            'current_enrollments' => $currentEnrollments,
            'enrollment_history' => $enrollmentHistory,
            'total_enrollments' => $enrollmentHistory->count(),
        ];
    }
}
