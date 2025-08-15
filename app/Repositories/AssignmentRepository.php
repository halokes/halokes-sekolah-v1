<?php

namespace App\Repositories;

use App\Models\Assignment;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AssignmentRepository
{
    public function getAllAssignments(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Assignment::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("due_date", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getAssignmentById($assignmentId): ?Assignment
    {
        return Assignment::find($assignmentId);
    }

    public function createAssignment($data)
    {
        return Assignment::create($data);
    }

    public function update($assignmentId, $data)
    {
        $assignment = Assignment::find($assignmentId);
        if ($assignment) {
            $assignment->update($data);
            return $assignment;
        } else {
            throw new Exception("Assignment not found");
        }
    }

    public function delete($assignmentId): ?bool
    {
        try {
            $assignment = Assignment::findOrFail($assignmentId);
            $assignment->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getPublishedAssignments()
    {
        return Assignment::published()->orderBy('due_date', 'asc')->get();
    }

    public function getUnpublishedAssignments()
    {
        return Assignment::unpublished()->orderBy('due_date', 'asc')->get();
    }

    public function getUpcomingAssignments()
    {
        return Assignment::upcoming()->orderBy('due_date', 'asc')->get();
    }

    public function getOverdueAssignments()
    {
        return Assignment::overdue()->orderBy('due_date', 'desc')->get();
    }

    public function getActiveAssignments()
    {
        return Assignment::active()->orderBy('due_date', 'asc')->get();
    }

    public function getAssignmentsForClass($classId, $academicYearId = null)
    {
        $query = Assignment::forClass($classId)
            ->with(['subject', 'teacher'])
            ->orderBy('due_date', 'asc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getAssignmentsForSubject($subjectId, $academicYearId = null)
    {
        $query = Assignment::forSubject($subjectId)
            ->with(['class', 'teacher'])
            ->orderBy('due_date', 'asc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getAssignmentsForTeacher($teacherId, $academicYearId = null)
    {
        $query = Assignment::forTeacher($teacherId)
            ->with(['class', 'subject'])
            ->orderBy('due_date', 'asc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getAssignmentsByAssignmentType($assignmentType, $classId = null, $academicYearId = null)
    {
        $query = Assignment::assignmentType($assignmentType)
            ->with(['class', 'subject', 'teacher'])
            ->orderBy('due_date', 'asc');

        if ($classId) {
            $query->forClass($classId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getAssignmentsByDateRange($startDate, $endDate, $classId = null, $subjectId = null)
    {
        $query = Assignment::dateRange($startDate, $endDate)
            ->with(['class', 'subject', 'teacher'])
            ->orderBy('due_date', 'asc');

        if ($classId) {
            $query->forClass($classId);
        }

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        return $query->get();
    }

    public function getAssignmentStatistics($assignmentId): array
    {
        $assignment = $this->getAssignmentById($assignmentId);
        if (!$assignment) {
            return [];
        }

        return [
            'submission_count' => $assignment->submission_count,
            'graded_submission_count' => $assignment->graded_submission_count,
            'average_score' => $assignment->average_score,
            'submission_rate' => $assignment->submission_rate,
            'is_overdue' => $assignment->is_overdue,
            'is_available_for_submission' => $assignment->is_available_for_submission,
        ];
    }

    public function togglePublishedStatus($assignmentId)
    {
        $assignment = Assignment::find($assignmentId);
        if ($assignment) {
            $assignment->is_published = !$assignment->is_published;
            $assignment->save();
            return $assignment;
        } else {
            throw new Exception("Assignment not found");
        }
    }

    public function getAssignmentsWithFiles()
    {
        return Assignment::withFiles()->orderBy('due_date', 'desc')->get();
    }

    public function getAssignmentsWithoutFiles()
    {
        return Assignment::withoutFiles()->orderBy('due_date', 'desc')->get();
    }

    public function getAssignmentsByStudent($studentId, $academicYearId = null)
    {
        $query = Assignment::query()
            ->whereHas('class.enrollments', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->with(['class', 'subject', 'teacher'])
            ->orderBy('due_date', 'asc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }
}
