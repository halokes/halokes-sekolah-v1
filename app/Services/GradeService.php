<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\GradeRepository;

class GradeService
{
    private $gradeRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(GradeRepository $gradeRepository)
    {
        $this->gradeRepository = $gradeRepository;
    }

    /**
     * =============================================
     *  list all grades along with filter, sort, etc
     * =============================================
     */
    public function listAllGrades($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->gradeRepository->getAllGrades($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single grade data
     * =============================================
     */
    public function getGradeDetail($gradeId): ?Grade
    {
        return $this->gradeRepository->getGradeById($gradeId);
    }

    /**
     * =============================================
     * process add new grade to database
     * =============================================
     */
    public function addNewGrade(array $validatedData)
    {
        DB::beginTransaction();
        try {
            $grade = $this->gradeRepository->createGrade($validatedData);
            DB::commit();
            return $grade;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new grade to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update grade data
     * =============================================
     */
    public function updateGrade(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $grade = $this->gradeRepository->getGradeById($id);

            if (!$grade) {
                throw new \Exception("Grade not found");
            }

            $grade = $this->gradeRepository->update($id, $validatedData);
            DB::commit();
            return $grade;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update grade in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete grade
     * =============================================
     */
    public function deleteGrade($gradeId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->gradeRepository->delete($gradeId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete grade with id $gradeId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get grades by student
     * =============================================
     */
    public function getGradesByStudent($studentId, $academicYearId = null)
    {
        return $this->gradeRepository->getGradesByStudent($studentId, $academicYearId);
    }

    /**
     * =============================================
     * get grades by class
     * =============================================
     */
    public function getGradesByClass($classId, $academicYearId = null, $subjectId = null)
    {
        return $this->gradeRepository->getGradesByClass($classId, $academicYearId, $subjectId);
    }

    /**
     * =============================================
     * get grades by subject
     * =============================================
     */
    public function getGradesBySubject($subjectId, $academicYearId = null)
    {
        return $this->gradeRepository->getGradesBySubject($subjectId, $academicYearId);
    }

    /**
     * =============================================
     * get grades by teacher
     * =============================================
     */
    public function getGradesByTeacher($teacherId, $academicYearId = null)
    {
        return $this->gradeRepository->getGradesByTeacher($teacherId, $academicYearId);
    }

    /**
     * =============================================
     * get student grade report
     * =============================================
     */
    public function getStudentGradeReport($studentId, $academicYearId = null, $subjectId = null)
    {
        return $this->gradeRepository->getStudentGradeReport($studentId, $academicYearId, $subjectId);
    }

    /**
     * =============================================
     * get class grade report
     * =============================================
     */
    public function getClassGradeReport($classId, $academicYearId = null, $subjectId = null)
    {
        return $this->gradeRepository->getClassGradeReport($classId, $academicYearId, $subjectId);
    }

    /**
     * =============================================
     * get student average grade
     * =============================================
     */
    public function getStudentAverageGrade($studentId, $subjectId = null, $academicYearId = null)
    {
        return $this->gradeRepository->getStudentAverageGrade($studentId, $subjectId, $academicYearId);
    }

    /**
     * =============================================
     * get class average grade
     * =============================================
     */
    public function getClassAverageGrade($classId, $subjectId = null, $academicYearId = null)
    {
        return $this->gradeRepository->getClassAverageGrade($classId, $subjectId, $academicYearId);
    }

    /**
     * =============================================
     * get grade statistics
     * =============================================
     */
    public function getGradeStatistics($studentId, $academicYearId = null): array
    {
        return $this->gradeRepository->getGradeStatistics($studentId, $academicYearId);
    }

    /**
     * =============================================
     * get class grade statistics
     * =============================================
     */
    public function getClassGradeStatistics($classId, $academicYearId = null): array
    {
        return $this->gradeRepository->getClassGradeStatistics($classId, $academicYearId);
    }

    /**
     * =============================================
     * get grades by assessment type
     * =============================================
     */
    public function getGradesByAssessmentType($assessmentType, $classId = null, $academicYearId = null)
    {
        return $this->gradeRepository->getGradesByAssessmentType($assessmentType, $classId, $academicYearId);
    }

    /**
     * =============================================
     * get grades by semester
     * =============================================
     */
    public function getGradesBySemester($semester, $classId = null, $academicYearId = null)
    {
        return $this->gradeRepository->getGradesBySemester($semester, $classId, $academicYearId);
    }

    /**
     * =============================================
     * get grade distribution
     * =============================================
     */
    public function getGradeDistribution($classId = null, $subjectId = null, $academicYearId = null): array
    {
        return $this->gradeRepository->getGradeDistribution($classId, $subjectId, $academicYearId);
    }

    /**
     * =============================================
     * bulk create grades
     * =============================================
     */
    public function bulkCreateGrades(array $gradeData)
    {
        DB::beginTransaction();
        try {
            $result = $this->gradeRepository->bulkCreateGrades($gradeData);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to bulk create grades: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * update grade score
     * =============================================
     */
    public function updateGradeScore($gradeId, $score)
    {
        return $this->gradeRepository->updateGradeScore($gradeId, $score);
    }

    /**
     * =============================================
     * get grades by date range
     * =============================================
     */
    public function getGradesByDateRange($startDate, $endDate, $classId = null)
    {
        return $this->gradeRepository->getGradesByDateRange($startDate, $endDate, $classId);
    }

    /**
     * =============================================
     * get student final grades
     * =============================================
     */
    public function getStudentFinalGrades($studentId, $academicYearId = null)
    {
        $query = Grade::forStudent($studentId)
            ->whereIn('assessment_type', ['final', 'midterm'])
            ->with(['subject', 'teacher'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    /**
     * =============================================
     * get class final grades
     * =============================================
     */
    public function getClassFinalGrades($classId, $academicYearId = null)
    {
        $query = Grade::forClass($classId)
            ->whereIn('assessment_type', ['final', 'midterm'])
            ->with(['student', 'subject', 'teacher'])
            ->orderBy('assessment_date', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    /**
     * =============================================
     * calculate student gpa
     * =============================================
     */
    public function calculateStudentGPA($studentId, $academicYearId = null): float
    {
        $finalGrades = $this->getStudentFinalGrades($studentId, $academicYearId);

        if ($finalGrades->isEmpty()) {
            return 0.0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($finalGrades as $grade) {
            if ($grade->score) {
                $totalScore += $grade->score;
                $count++;
            }
        }

        return $count > 0 ? round($totalScore / $count, 2) : 0.0;
    }

    /**
     * =============================================
     * calculate class gpa
     * =============================================
     */
    public function calculateClassGPA($classId, $academicYearId = null): float
    {
        $finalGrades = $this->getClassFinalGrades($classId, $academicYearId);

        if ($finalGrades->isEmpty()) {
            return 0.0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($finalGrades as $grade) {
            if ($grade->score) {
                $totalScore += $grade->score;
                $count++;
            }
        }

        return $count > 0 ? round($totalScore / $count, 2) : 0.0;
    }

    /**
     * =============================================
     * get grade dashboard data
     * =============================================
     */
    public function getGradeDashboardData($classId = null, $academicYearId = null)
    {
        $today = now();
        $startOfMonth = $today->startOfMonth();
        $endOfMonth = $today->endOfMonth();

        $data = [
            'this_month' => [
                'total_grades' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
            ],
            'this_year' => [
                'total_grades' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
            ],
            'grade_distribution' => [],
        ];

        if ($classId) {
            // This month's data
            $monthGrades = $this->getGradesByClass($classId, $academicYearId, null, $startOfMonth, $endOfMonth);
            $data['this_month']['total_grades'] = $monthGrades->count();
            $data['this_month']['average_score'] = $monthGrades->avg('score') ?? 0;
            $data['this_month']['highest_score'] = $monthGrades->max('score') ?? 0;
            $data['this_month']['lowest_score'] = $monthGrades->min('score') ?? 0;

            // This year's data
            $yearGrades = $this->getGradesByClass($classId, $academicYearId);
            $data['this_year']['total_grades'] = $yearGrades->count();
            $data['this_year']['average_score'] = $yearGrades->avg('score') ?? 0;
            $data['this_year']['highest_score'] = $yearGrades->max('score') ?? 0;
            $data['this_year']['lowest_score'] = $yearGrades->min('score') ?? 0;

            // Grade distribution
            $data['grade_distribution'] = $this->getGradeDistribution($classId, null, $academicYearId);
        }

        return $data;
    }
}
