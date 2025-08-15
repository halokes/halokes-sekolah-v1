<?php

namespace App\Services;

use App\Models\Submission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\SubmissionRepository;

class SubmissionService
{
    private $submissionRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(SubmissionRepository $submissionRepository)
    {
        $this->submissionRepository = $submissionRepository;
    }

    /**
     * =============================================
     *  list all submissions along with filter, sort, etc
     * =============================================
     */
    public function listAllSubmissions($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->submissionRepository->getAllSubmissions($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single submission data
     * =============================================
     */
    public function getSubmissionDetail($submissionId): ?Submission
    {
        return $this->submissionRepository->getSubmissionById($submissionId);
    }

    /**
     * =============================================
     * process add new submission to database
     * =============================================
     */
    public function addNewSubmission(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check for existing submission
            if ($this->submissionRepository->checkExistingSubmission(
                $validatedData['assignment_id'],
                $validatedData['student_id']
            )) {
                throw new \Exception("Student has already submitted for this assignment");
            }

            $submission = $this->submissionRepository->createSubmission($validatedData);
            DB::commit();
            return $submission;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new submission to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update submission data
     * =============================================
     */
    public function updateSubmission(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $submission = $this->submissionRepository->getSubmissionById($id);

            if (!$submission) {
                throw new \Exception("Submission not found");
            }

            // Check for existing submission (excluding current record)
            if ($this->submissionRepository->checkExistingSubmission(
                $validatedData['assignment_id'],
                $validatedData['student_id'],
                $id
            )) {
                throw new \Exception("Student has already submitted for this assignment");
            }

            $submission = $this->submissionRepository->update($id, $validatedData);
            DB::commit();
            return $submission;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update submission in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete submission
     * =============================================
     */
    public function deleteSubmission($submissionId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->submissionRepository->delete($submissionId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete submission with id $submissionId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get submissions for assignment
     * =============================================
     */
    public function getSubmissionsForAssignment($assignmentId, $studentId = null)
    {
        return $this->submissionRepository->getSubmissionsForAssignment($assignmentId, $studentId);
    }

    /**
     * =============================================
     * get submissions for student
     * =============================================
     */
    public function getSubmissionsForStudent($studentId, $assignmentId = null)
    {
        return $this->submissionRepository->getSubmissionsForStudent($studentId, $assignmentId);
    }

    /**
     * =============================================
     * get submissions by status
     * =============================================
     */
    public function getSubmissionsByStatus($status, $assignmentId = null)
    {
        return $this->submissionRepository->getSubmissionsByStatus($status, $assignmentId);
    }

    /**
     * =============================================
     * get late submissions
     * =============================================
     */
    public function getLateSubmissions($assignmentId = null)
    {
        return $this->submissionRepository->getLateSubmissions($assignmentId);
    }

    /**
     * =============================================
     * get graded submissions
     * =============================================
     */
    public function getGradedSubmissions($assignmentId = null)
    {
        return $this->submissionRepository->getGradedSubmissions($assignmentId);
    }

    /**
     * =============================================
     * get submission statistics
     * =============================================
     */
    public function getSubmissionStatistics($assignmentId): array
    {
        return $this->submissionRepository->getSubmissionStatistics($assignmentId);
    }

    /**
     * =============================================
     * check for existing submission
     * =============================================
     */
    public function checkExistingSubmission($assignmentId, $studentId, $excludeId = null): bool
    {
        return $this->submissionRepository->checkExistingSubmission($assignmentId, $studentId, $excludeId);
    }

    /**
     * =============================================
     * update submission score and grade
     * =============================================
     */
    public function updateSubmissionScoreAndGrade($submissionId, $score, $grade, $feedback = null, $gradedBy = null)
    {
        DB::beginTransaction();
        try {
            $submission = $this->submissionRepository->updateSubmissionScoreAndGrade($submissionId, $score, $grade, $feedback, $gradedBy);
            DB::commit();
            return $submission;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update submission score and grade for id $submissionId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get submissions by date range
     * =============================================
     */
    public function getSubmissionsByDateRange($startDate, $endDate, $assignmentId = null)
    {
        return $this->submissionRepository->getSubmissionsByDateRange($startDate, $endDate, $assignmentId);
    }

    /**
     * =============================================
     * get submissions by class
     * =============================================
     */
    public function getSubmissionsByClass($classId, $academicYearId = null)
    {
        return $this->submissionRepository->getSubmissionsByClass($classId, $academicYearId);
    }

    /**
     * =============================================
     * get submissions by subject
     * =============================================
     */
    public function getSubmissionsBySubject($subjectId, $academicYearId = null)
    {
        return $this->submissionRepository->getSubmissionsBySubject($subjectId, $academicYearId);
    }

    /**
     * =============================================
     * get student submission overview
     * =============================================
     */
    public function getStudentSubmissionOverview($studentId, $academicYearId = null): array
    {
        $totalAssignments = 0;
        $submittedAssignments = 0;
        $gradedAssignments = 0;
        $lateSubmissions = 0;
        $averageScore = 0;

        $assignments = app(AssignmentService::class)->getAssignmentsByStudent($studentId, $academicYearId);
        $totalAssignments = $assignments->count();

        $submissions = $this->getSubmissionsForStudent($studentId);

        foreach ($assignments as $assignment) {
            $submission = $submissions->where('assignment_id', $assignment->id)->first();
            if ($submission) {
                $submittedAssignments++;
                if ($submission->status === 'graded') {
                    $gradedAssignments++;
                }
                if ($submission->is_late) {
                    $lateSubmissions++;
                }
            }
        }

        if ($gradedAssignments > 0) {
            $averageScore = $submissions->where('status', 'graded')->avg('score');
        }

        return [
            'total_assignments' => $totalAssignments,
            'submitted_assignments' => $submittedAssignments,
            'graded_assignments' => $gradedAssignments,
            'late_submissions' => $lateSubmissions,
            'average_score' => round($averageScore, 2),
            'submission_rate' => $totalAssignments > 0 ? round(($submittedAssignments / $totalAssignments) * 100, 2) : 0,
            'graded_rate' => $submittedAssignments > 0 ? round(($gradedAssignments / $submittedAssignments) * 100, 2) : 0,
        ];
    }

    /**
     * =============================================
     * get assignment submission status for all students in a class
     * =============================================
     */
    public function getAssignmentSubmissionStatusForClass($assignmentId, $classId)
    {
        $assignment = app(AssignmentService::class)->getAssignmentDetail($assignmentId);
        if (!$assignment || $assignment->class_id !== $classId) {
            return collect();
        }

        $studentsInClass = app(EnrollmentService::class)->getClassEnrollmentList($classId, $assignment->academic_year_id);
        $submissions = $this->getSubmissionsForAssignment($assignmentId);

        $results = collect();
        foreach ($studentsInClass as $enrollment) {
            $student = $enrollment->student;
            $submission = $submissions->where('student_id', $student->id)->first();

            $results->push([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'status' => $submission ? $submission->status : 'not_submitted',
                'submitted_at' => $submission ? $submission->submitted_at : null,
                'score' => $submission ? $submission->score : null,
                'grade' => $submission ? $submission->grade : null,
                'is_late' => $submission ? $submission->is_late : false,
            ]);
        }

        return $results;
    }
}
