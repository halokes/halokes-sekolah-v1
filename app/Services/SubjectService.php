<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\SubjectRepository;

class SubjectService
{
    private $subjectRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(SubjectRepository $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * =============================================
     *  list all subjects along with filter, sort, etc
     * =============================================
     */
    public function listAllSubjects($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->subjectRepository->getAllSubjects($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single subject data
     * =============================================
     */
    public function getSubjectDetail($subjectId): ?Subject
    {
        return $this->subjectRepository->getSubjectById($subjectId);
    }

    /**
     * =============================================
     * get subject by code
     * =============================================
     */
    public function getSubjectByCode(string $code): ?Subject
    {
        return $this->subjectRepository->getSubjectByCode($code);
    }

    /**
     * =============================================
     * process add new subject to database
     * =============================================
     */
    public function addNewSubject(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check if subject code already exists
            if ($this->subjectRepository->isCodeExists($validatedData['code'])) {
                throw new \Exception("Subject code already exists");
            }

            // Set order if not provided
            if (!isset($validatedData['order'])) {
                $validatedData['order'] = $this->subjectRepository->getMaxOrder(
                    $validatedData['school_id'] ?? null,
                    $validatedData['level_id'] ?? null
                );
            }

            $subject = $this->subjectRepository->createSubject($validatedData);
            DB::commit();
            return $subject;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new subject to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update subject data
     * =============================================
     */
    public function updateSubject(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $subject = $this->subjectRepository->getSubjectById($id);

            if (!$subject) {
                throw new \Exception("Subject not found");
            }

            // Check if subject code already exists (excluding current record)
            if ($this->subjectRepository->isCodeExists($validatedData['code'], $id)) {
                throw new \Exception("Subject code already exists");
            }

            $subject = $this->subjectRepository->update($id, $validatedData);
            DB::commit();
            return $subject;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update subject in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete subject
     * =============================================
     */
    public function deleteSubject($subjectId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->subjectRepository->delete($subjectId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete subject with id $subjectId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get active subjects
     * =============================================
     */
    public function getActiveSubjects()
    {
        return $this->subjectRepository->getActiveSubjects();
    }

    /**
     * =============================================
     * get inactive subjects
     * =============================================
     */
    public function getInactiveSubjects()
    {
        return $this->subjectRepository->getInactiveSubjects();
    }

    /**
     * =============================================
     * get subjects by school
     * =============================================
     */
    public function getSubjectsBySchool($schoolId)
    {
        return $this->subjectRepository->getSubjectsBySchool($schoolId);
    }

    /**
     * =============================================
     * get subjects by level
     * =============================================
     */
    public function getSubjectsByLevel($levelId)
    {
        return $this->subjectRepository->getSubjectsByLevel($levelId);
    }

    /**
     * =============================================
     * get subjects by category
     * =============================================
     */
    public function getSubjectsByCategory($category)
    {
        return $this->subjectRepository->getSubjectsByCategory($category);
    }

    /**
     * =============================================
     * get academic subjects
     * =============================================
     */
    public function getAcademicSubjects()
    {
        return $this->subjectRepository->getAcademicSubjects();
    }

    /**
     * =============================================
     * get extracurricular subjects
     * =============================================
     */
    public function getExtracurricularSubjects()
    {
        return $this->subjectRepository->getExtracurricularSubjects();
    }

    /**
     * =============================================
     * get skill subjects
     * =============================================
     */
    public function getSkillSubjects()
    {
        return $this->subjectRepository->getSkillSubjects();
    }

    /**
     * =============================================
     * check if subject code exists
     * =============================================
     */
    public function isSubjectCodeExists(string $code, string $excludeId = null): bool
    {
        return $this->subjectRepository->isCodeExists($code, $excludeId);
    }

    /**
     * =============================================
     * get subjects by teacher
     * =============================================
     */
    public function getSubjectsByTeacher($teacherId)
    {
        return $this->subjectRepository->getSubjectsByTeacher($teacherId);
    }

    /**
     * =============================================
     * get subjects by class
     * =============================================
     */
    public function getSubjectsByClass($classId)
    {
        return $this->subjectRepository->getSubjectsByClass($classId);
    }

    /**
     * =============================================
     * get subjects by academic year
     * =============================================
     */
    public function getSubjectsByAcademicYear($academicYearId)
    {
        return $this->subjectRepository->getSubjectsByAcademicYear($academicYearId);
    }

    /**
     * =============================================
     * toggle subject status
     * =============================================
     */
    public function toggleSubjectStatus($subjectId): ?Subject
    {
        DB::beginTransaction();
        try {
            $subject = $this->subjectRepository->toggleStatus($subjectId);
            DB::commit();
            return $subject;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle subject status with id $subjectId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * reorder subjects
     * =============================================
     */
    public function reorderSubjects(array $orderData): bool
    {
        DB::beginTransaction();
        try {
            $result = $this->subjectRepository->reorderSubjects($orderData);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to reorder subjects: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get subject statistics
     * =============================================
     */
    public function getSubjectStatistics($subjectId): array
    {
        return $this->subjectRepository->getSubjectStatistics($subjectId);
    }

    /**
     * =============================================
     * get subject category statistics
     * =============================================
     */
    public function getCategoryStatistics(): array
    {
        return [
            'academic' => $this->subjectRepository->getAcademicSubjects()->count(),
            'extracurricular' => $this->subjectRepository->getExtracurricularSubjects()->count(),
            'skill' => $this->subjectRepository->getSkillSubjects()->count(),
        ];
    }
}
