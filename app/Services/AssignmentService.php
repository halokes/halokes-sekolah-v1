<?php

namespace App\Services;

use App\Models\Assignment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\AssignmentRepository;

class AssignmentService
{
    private $assignmentRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(AssignmentRepository $assignmentRepository)
    {
        $this->assignmentRepository = $assignmentRepository;
    }

    /**
     * =============================================
     *  list all assignments along with filter, sort, etc
     * =============================================
     */
    public function listAllAssignments($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->assignmentRepository->getAllAssignments($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single assignment data
     * =============================================
     */
    public function getAssignmentDetail($assignmentId): ?Assignment
    {
        return $this->assignmentRepository->getAssignmentById($assignmentId);
    }

    /**
     * =============================================
     * process add new assignment to database
     * =============================================
     */
    public function addNewAssignment(array $validatedData)
    {
        DB::beginTransaction();
        try {
            $assignment = $this->assignmentRepository->createAssignment($validatedData);
            DB::commit();
            return $assignment;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new assignment to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update assignment data
     * =============================================
     */
    public function updateAssignment(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $assignment = $this->assignmentRepository->getAssignmentById($id);

            if (!$assignment) {
                throw new \Exception("Assignment not found");
            }

            $assignment = $this->assignmentRepository->update($id, $validatedData);
            DB::commit();
            return $assignment;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update assignment in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete assignment
     * =============================================
     */
    public function deleteAssignment($assignmentId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->assignmentRepository->delete($assignmentId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete assignment with id $assignmentId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get published assignments
     * =============================================
     */
    public function getPublishedAssignments()
    {
        return $this->assignmentRepository->getPublishedAssignments();
    }

    /**
     * =============================================
     * get unpublished assignments
     * =============================================
     */
    public function getUnpublishedAssignments()
    {
        return $this->assignmentRepository->getUnpublishedAssignments();
    }

    /**
     * =============================================
     * get upcoming assignments
     * =============================================
     */
    public function getUpcomingAssignments()
    {
        return $this->assignmentRepository->getUpcomingAssignments();
    }

    /**
     * =============================================
     * get overdue assignments
     * =============================================
     */
    public function getOverdueAssignments()
    {
        return $this->assignmentRepository->getOverdueAssignments();
    }

    /**
     * =============================================
     * get active assignments
     * =============================================
     */
    public function getActiveAssignments()
    {
        return $this->assignmentRepository->getActiveAssignments();
    }

    /**
     * =============================================
     * get assignments for class
     * =============================================
     */
    public function getAssignmentsForClass($classId, $academicYearId = null)
    {
        return $this->assignmentRepository->getAssignmentsForClass($classId, $academicYearId);
    }

    /**
     * =============================================
     * get assignments for subject
     * =============================================
     */
    public function getAssignmentsForSubject($subjectId, $academicYearId = null)
    {
        return $this->assignmentRepository->getAssignmentsForSubject($subjectId, $academicYearId);
    }

    /**
     * =============================================
     * get assignments for teacher
     * =============================================
     */
    public function getAssignmentsForTeacher($teacherId, $academicYearId = null)
    {
        return $this->assignmentRepository->getAssignmentsForTeacher($teacherId, $academicYearId);
    }

    /**
     * =============================================
     * get assignments by assignment type
     * =============================================
     */
    public function getAssignmentsByAssignmentType($assignmentType, $classId = null, $academicYearId = null)
    {
        return $this->assignmentRepository->getAssignmentsByAssignmentType($assignmentType, $classId, $academicYearId);
    }

    /**
     * =============================================
     * get assignments by date range
     * =============================================
     */
    public function getAssignmentsByDateRange($startDate, $endDate, $classId = null, $subjectId = null)
    {
        return $this->assignmentRepository->getAssignmentsByDateRange($startDate, $endDate, $classId, $subjectId);
    }

    /**
     * =============================================
     * get assignment statistics
     * =============================================
     */
    public function getAssignmentStatistics($assignmentId): array
    {
        return $this->assignmentRepository->getAssignmentStatistics($assignmentId);
    }

    /**
     * =============================================
     * toggle published status
     * =============================================
     */
    public function togglePublishedStatus($assignmentId): ?Assignment
    {
        DB::beginTransaction();
        try {
            $assignment = $this->assignmentRepository->togglePublishedStatus($assignmentId);
            DB::commit();
            return $assignment;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle assignment published status with id $assignmentId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get assignments with files
     * =============================================
     */
    public function getAssignmentsWithFiles()
    {
        return $this->assignmentRepository->getAssignmentsWithFiles();
    }

    /**
     * =============================================
     * get assignments without files
     * =============================================
     */
    public function getAssignmentsWithoutFiles()
    {
        return $this->assignmentRepository->getAssignmentsWithoutFiles();
    }

    /**
     * =============================================
     * get assignments by student
     * =============================================
     */
    public function getAssignmentsByStudent($studentId, $academicYearId = null)
    {
        return $this->assignmentRepository->getAssignmentsByStudent($studentId, $academicYearId);
    }

    /**
     * =============================================
     * get assignment dashboard data
     * =============================================
     */
    public function getAssignmentDashboardData($classId = null, $academicYearId = null)
    {
        $data = [
            'total_assignments' => 0,
            'published_assignments' => 0,
            'unpublished_assignments' => 0,
            'upcoming_assignments' => 0,
            'overdue_assignments' => 0,
            'average_submission_rate' => 0,
            'average_score' => 0,
        ];

        $query = Assignment::query();
        if ($classId) {
            $query->forClass($classId);
        }
        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }
        $assignments = $query->get();

        $data['total_assignments'] = $assignments->count();
        $data['published_assignments'] = $assignments->where('is_published', true)->count();
        $data['unpublished_assignments'] = $assignments->where('is_published', false)->count();
        $data['upcoming_assignments'] = $assignments->where('due_date', '>', now())->count();
        $data['overdue_assignments'] = $assignments->where('due_date', '<', now())->count();
        $data['average_submission_rate'] = $assignments->avg('submission_rate') ?? 0;
        $data['average_score'] = $assignments->avg('average_score') ?? 0;

        return $data;
    }
}
