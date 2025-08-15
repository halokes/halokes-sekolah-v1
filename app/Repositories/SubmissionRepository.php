<?php

namespace App\Repositories;

use App\Models\Submission;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubmissionRepository
{
    public function getAllSubmissions(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Submission::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("submitted_at", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getSubmissionById($submissionId): ?Submission
    {
        return Submission::find($submissionId);
    }

    public function createSubmission($data)
    {
        return Submission::create($data);
    }

    public function update($submissionId, $data)
    {
        $submission = Submission::find($submissionId);
        if ($submission) {
            $submission->update($data);
            return $submission;
        } else {
            throw new Exception("Submission not found");
        }
    }

    public function delete($submissionId): ?bool
    {
        try {
            $submission = Submission::findOrFail($submissionId);
            $submission->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getSubmissionsForAssignment($assignmentId, $studentId = null)
    {
        $query = Submission::forAssignment($assignmentId)
            ->with(['student', 'gradedBy'])
            ->orderBy('submitted_at', 'desc');

        if ($studentId) {
            $query->forStudent($studentId);
        }

        return $query->get();
    }

    public function getSubmissionsForStudent($studentId, $assignmentId = null)
    {
        $query = Submission::forStudent($studentId)
            ->with(['assignment', 'gradedBy'])
            ->orderBy('submitted_at', 'desc');

        if ($assignmentId) {
            $query->forAssignment($assignmentId);
        }

        return $query->get();
    }

    public function getSubmissionsByStatus($status, $assignmentId = null)
    {
        $query = Submission::withStatus($status)
            ->with(['assignment', 'student']);

        if ($assignmentId) {
            $query->forAssignment($assignmentId);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    public function getLateSubmissions($assignmentId = null)
    {
        $query = Submission::late()
            ->with(['assignment', 'student']);

        if ($assignmentId) {
            $query->forAssignment($assignmentId);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
    }

    public function getGradedSubmissions($assignmentId = null)
    {
        $query = Submission::graded()
            ->with(['assignment', 'student', 'gradedBy']);

        if ($assignmentId) {
            $query->forAssignment($assignmentId);
        }

        return $query->orderBy('graded_at', 'desc')->get();
    }

    public function getSubmissionStatistics($assignmentId): array
    {
        $submissions = $this->getSubmissionsForAssignment($assignmentId);

        $totalSubmissions = $submissions->count();
        $gradedSubmissions = $submissions->where('status', 'graded')->count();
        $lateSubmissions = $submissions->where('is_late', true)->count();
        $averageScore = $submissions->whereNotNull('score')->avg('score');

        return [
            'total_submissions' => $totalSubmissions,
            'graded_submissions' => $gradedSubmissions,
            'late_submissions' => $lateSubmissions,
            'average_score' => round($averageScore, 2),
            'submission_rate' => $totalSubmissions > 0 ? round(($totalSubmissions / $submissions->first()->assignment->class->current_student_count) * 100, 2) : 0,
            'graded_rate' => $totalSubmissions > 0 ? round(($gradedSubmissions / $totalSubmissions) * 100, 2) : 0,
        ];
    }

    public function checkExistingSubmission($assignmentId, $studentId, $excludeId = null): bool
    {
        $query = Submission::where('assignment_id', $assignmentId)
            ->where('student_id', $studentId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function updateSubmissionScoreAndGrade($submissionId, $score, $grade, $feedback = null, $gradedBy = null)
    {
        $submission = $this->getSubmissionById($submissionId);
        if ($submission) {
            $submission->score = $score;
            $submission->grade = $grade;
            $submission->feedback = $feedback;
            $submission->graded_by = $gradedBy;
            $submission->graded_at = now();
            $submission->status = 'graded';
            $submission->save();
            return $submission;
        }
        return null;
    }

    public function getSubmissionsByDateRange($startDate, $endDate, $assignmentId = null)
    {
        $query = Submission::dateRange($startDate, $endDate)
            ->with(['assignment', 'student', 'gradedBy'])
            ->orderBy('submitted_at', 'desc');

        if ($assignmentId) {
            $query->forAssignment($assignmentId);
        }

        return $query->get();
    }

    public function getSubmissionsByClass($classId, $academicYearId = null)
    {
        return Submission::whereHas('assignment.class', function ($query) use ($classId) {
            $query->where('id', $classId);
        })
        ->whereHas('assignment.academicYear', function ($query) use ($academicYearId) {
            $query->where('id', $academicYearId);
        })
        ->with(['assignment', 'student', 'gradedBy'])
        ->orderBy('submitted_at', 'desc')
        ->get();
    }

    public function getSubmissionsBySubject($subjectId, $academicYearId = null)
    {
        return Submission::whereHas('assignment.subject', function ($query) use ($subjectId) {
            $query->where('id', $subjectId);
        })
        ->whereHas('assignment.academicYear', function ($query) use ($academicYearId) {
            $query->where('id', $academicYearId);
        })
        ->with(['assignment', 'student', 'gradedBy'])
        ->orderBy('submitted_at', 'desc')
        ->get();
    }
}
