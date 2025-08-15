<?php

namespace App\Repositories;

use App\Models\Grade;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GradeRepository
{
    public function getAllGrades(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Grade::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("assessment_date", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getGradeById($gradeId): ?Grade
    {
        return Grade::find($gradeId);
    }

    public function createGrade($data)
    {
        return Grade::create($data);
    }

    public function update($gradeId, $data)
    {
        $grade = Grade::find($gradeId);
        if ($grade) {
            $grade->update($data);
            return $grade;
        } else {
            throw new Exception("Grade not found");
        }
    }

    public function delete($gradeId): ?bool
    {
        try {
            $grade = Grade::findOrFail($gradeId);
            $grade->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getGradesByStudent($studentId, $academicYearId = null)
    {
        $query = Grade::forStudent($studentId)
            ->with(['subject', 'teacher', 'enrollment.class'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getGradesByClass($classId, $academicYearId = null, $subjectId = null)
    {
        $query = Grade::forClass($classId)
            ->with(['student', 'subject', 'teacher'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        return $query->get();
    }

    public function getGradesBySubject($subjectId, $academicYearId = null)
    {
        $query = Grade::forSubject($subjectId)
            ->with(['student', 'teacher', 'enrollment.class'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getGradesByTeacher($teacherId, $academicYearId = null)
    {
        $query = Grade::forTeacher($teacherId)
            ->with(['student', 'subject', 'enrollment.class'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getStudentGradeReport($studentId, $academicYearId = null, $subjectId = null)
    {
        $query = Grade::forStudent($studentId)
            ->with(['subject', 'teacher'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        return $query->get();
    }

    public function getClassGradeReport($classId, $academicYearId = null, $subjectId = null)
    {
        $query = Grade::forClass($classId)
            ->with(['student', 'subject', 'teacher'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        return $query->get();
    }

    public function getStudentAverageGrade($studentId, $subjectId = null, $academicYearId = null)
    {
        $query = Grade::forStudent($studentId)
            ->whereNotNull('score');

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->avg('score');
    }

    public function getClassAverageGrade($classId, $subjectId = null, $academicYearId = null)
    {
        $query = Grade::forClass($classId)
            ->whereNotNull('score');

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->avg('score');
    }

    public function getGradeStatistics($studentId, $academicYearId = null): array
    {
        $query = Grade::forStudent($studentId)->whereNotNull('score');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        $totalGrades = $query->count();
        $averageScore = $query->avg('score');
        $maxScore = $query->max('score');
        $minScore = $query->min('score');

        // Group by assessment type
        $byAssessmentType = $query->get()->groupBy('assessment_type');
        $assessmentTypeStats = [];
        foreach ($byAssessmentType as $type => $grades) {
            $assessmentTypeStats[$type] = [
                'count' => $grades->count(),
                'average' => $grades->avg('score'),
                'max' => $grades->max('score'),
                'min' => $grades->min('score'),
            ];
        }

        // Group by subject
        $bySubject = $query->get()->groupBy('subject_id');
        $subjectStats = [];
        foreach ($bySubject as $subjectId => $grades) {
            $subject = $grades->first()->subject;
            $subjectStats[$subjectId] = [
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'average' => $grades->avg('score'),
                'max' => $grades->max('score'),
                'min' => $grades->min('score'),
            ];
        }

        return [
            'total_grades' => $totalGrades,
            'average_score' => round($averageScore, 2),
            'max_score' => $maxScore,
            'min_score' => $minScore,
            'by_assessment_type' => $assessmentTypeStats,
            'by_subject' => $subjectStats,
        ];
    }

    public function getClassGradeStatistics($classId, $academicYearId = null): array
    {
        $query = Grade::forClass($classId)->whereNotNull('score');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        $totalGrades = $query->count();
        $averageScore = $query->avg('score');
        $maxScore = $query->max('score');
        $minScore = $query->min('score');

        // Group by assessment type
        $byAssessmentType = $query->get()->groupBy('assessment_type');
        $assessmentTypeStats = [];
        foreach ($byAssessmentType as $type => $grades) {
            $assessmentTypeStats[$type] = [
                'count' => $grades->count(),
                'average' => $grades->avg('score'),
                'max' => $grades->max('score'),
                'min' => $grades->min('score'),
            ];
        }

        // Group by subject
        $bySubject = $query->get()->groupBy('subject_id');
        $subjectStats = [];
        foreach ($bySubject as $subjectId => $grades) {
            $subject = $grades->first()->subject;
            $subjectStats[$subjectId] = [
                'subject_name' => $subject->name,
                'subject_code' => $subject->code,
                'average' => $grades->avg('score'),
                'max' => $grades->max('score'),
                'min' => $grades->min('score'),
            ];
        }

        return [
            'total_grades' => $totalGrades,
            'average_score' => round($averageScore, 2),
            'max_score' => $maxScore,
            'min_score' => $minScore,
            'by_assessment_type' => $assessmentTypeStats,
            'by_subject' => $subjectStats,
        ];
    }

    public function getGradesByAssessmentType($assessmentType, $classId = null, $academicYearId = null)
    {
        $query = Grade::assessmentType($assessmentType)
            ->with(['student', 'subject', 'teacher']);

        if ($classId) {
            $query->forClass($classId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->orderBy('assessment_date', 'desc')->get();
    }

    public function getGradesBySemester($semester, $classId = null, $academicYearId = null)
    {
        $query = Grade::forSemester($semester)
            ->with(['student', 'subject', 'teacher']);

        if ($classId) {
            $query->forClass($classId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->orderBy('assessment_date', 'desc')->get();
    }

    public function getGradeDistribution($classId = null, $subjectId = null, $academicYearId = null)
    {
        $query = Grade::query()->whereNotNull('score');

        if ($classId) {
            $query->forClass($classId);
        }

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        $grades = $query->get();

        // Define grade ranges
        $gradeRanges = [
            'A (90-100)' => 0,
            'B (80-89)' => 0,
            'C (70-79)' => 0,
            'D (60-69)' => 0,
            'E (<60)' => 0,
        ];

        foreach ($grades as $grade) {
            if ($grade->score >= 90) {
                $gradeRanges['A (90-100)']++;
            } elseif ($grade->score >= 80) {
                $gradeRanges['B (80-89)']++;
            } elseif ($grade->score >= 70) {
                $gradeRanges['C (70-79)']++;
            } elseif ($grade->score >= 60) {
                $gradeRanges['D (60-69)']++;
            } else {
                $gradeRanges['E (<60)']++;
            }
        }

        return $gradeRanges;
    }

    public function bulkCreateGrades($gradeData)
    {
        DB::beginTransaction();
        try {
            $grades = [];
            foreach ($gradeData as $data) {
                $grades[] = Grade::create($data);
            }
            DB::commit();
            return $grades;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function updateGradeScore($gradeId, $score)
    {
        $grade = Grade::find($gradeId);
        if ($grade) {
            $grade->score = $score;
            $grade->save();
            return $grade;
        }
        return null;
    }

    public function getGradesByDateRange($startDate, $endDate, $classId = null)
    {
        $query = Grade::dateRange($startDate, $endDate)
            ->with(['student', 'subject', 'teacher', 'enrollment.class'])
            ->orderBy('assessment_date');

        if ($classId) {
            $query->forClass($classId);
        }

        return $query->get();
    }
}
