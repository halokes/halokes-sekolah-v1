<?php

namespace App\Services;

use App\Models\ClassModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ClassRepository;

class ClassService
{
    private $classRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(ClassRepository $classRepository)
    {
        $this->classRepository = $classRepository;
    }

    /**
     * =============================================
     *  list all classes along with filter, sort, etc
     * =============================================
     */
    public function listAllClasses($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->classRepository->getAllClasses($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single class data
     * =============================================
     */
    public function getClassDetail($classId): ?ClassModel
    {
        return $this->classRepository->getClassById($classId);
    }

    /**
     * =============================================
     * get class by code
     * =============================================
     */
    public function getClassByCode(string $classCode): ?ClassModel
    {
        return $this->classRepository->getClassByCode($classCode);
    }

    /**
     * =============================================
     * process add new class to database
     * =============================================
     */
    public function addNewClass(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check if class code already exists for this school and academic year
            if ($this->classRepository->isClassCodeExists(
                $validatedData['class_code'],
                null,
                $validatedData['school_id'] ?? null,
                $validatedData['academic_year_id'] ?? null
            )) {
                throw new \Exception("Class code already exists for this school and academic year");
            }

            // Set order if not provided
            if (!isset($validatedData['order'])) {
                $validatedData['order'] = $this->classRepository->getMaxOrder(
                    $validatedData['school_id'] ?? null,
                    $validatedData['academic_year_id'] ?? null,
                    $validatedData['level_id'] ?? null
                );
            }

            $class = $this->classRepository->createClass($validatedData);
            DB::commit();
            return $class;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new class to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update class data
     * =============================================
     */
    public function updateClass(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $class = $this->classRepository->getClassById($id);

            if (!$class) {
                throw new \Exception("Class not found");
            }

            // Check if class code already exists (excluding current record)
            if ($this->classRepository->isClassCodeExists(
                $validatedData['class_code'],
                $id,
                $validatedData['school_id'] ?? null,
                $validatedData['academic_year_id'] ?? null
            )) {
                throw new \Exception("Class code already exists for this school and academic year");
            }

            $class = $this->classRepository->update($id, $validatedData);
            DB::commit();
            return $class;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update class in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete class
     * =============================================
     */
    public function deleteClass($classId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->classRepository->delete($classId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete class with id $classId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get active classes
     * =============================================
     */
    public function getActiveClasses()
    {
        return $this->classRepository->getActiveClasses();
    }

    /**
     * =============================================
     * get inactive classes
     * =============================================
     */
    public function getInactiveClasses()
    {
        return $this->classRepository->getInactiveClasses();
    }

    /**
     * =============================================
     * get classes by school
     * =============================================
     */
    public function getClassesBySchool($schoolId)
    {
        return $this->classRepository->getClassesBySchool($schoolId);
    }

    /**
     * =============================================
     * get classes by academic year
     * =============================================
     */
    public function getClassesByAcademicYear($academicYearId)
    {
        return $this->classRepository->getClassesByAcademicYear($academicYearId);
    }

    /**
     * =============================================
     * get classes by level
     * =============================================
     */
    public function getClassesByLevel($levelId)
    {
        return $this->classRepository->getClassesByLevel($levelId);
    }

    /**
     * =============================================
     * get classes with available slots
     * =============================================
     */
    public function getClassesWithAvailableSlots()
    {
        return $this->classRepository->getClassesWithAvailableSlots();
    }

    /**
     * =============================================
     * get class student count
     * =============================================
     */
    public function getClassStudentCount($classId)
    {
        return $this->classRepository->getClassStudentCount($classId);
    }

    /**
     * =============================================
     * check if class code exists
     * =============================================
     */
    public function isClassCodeExists(string $classCode, string $excludeId = null, string $schoolId = null, string $academicYearId = null): bool
    {
        return $this->classRepository->isClassCodeExists($classCode, $excludeId, $schoolId, $academicYearId);
    }

    /**
     * =============================================
     * get classes by homeroom teacher
     * =============================================
     */
    public function getClassesByHomeroomTeacher($teacherId)
    {
        return $this->classRepository->getClassesByHomeroomTeacher($teacherId);
    }

    /**
     * =============================================
     * toggle class status
     * =============================================
     */
    public function toggleClassStatus($classId): ?ClassModel
    {
        DB::beginTransaction();
        try {
            $class = $this->classRepository->toggleStatus($classId);
            DB::commit();
            return $class;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle class status with id $classId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * reorder classes
     * =============================================
     */
    public function reorderClasses(array $orderData): bool
    {
        DB::beginTransaction();
        try {
            $result = $this->classRepository->reorderClasses($orderData);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to reorder classes: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get class statistics
     * =============================================
     */
    public function getClassStatistics($classId): array
    {
        $class = $this->classRepository->getClassById($classId);
        if (!$class) {
            return [];
        }

        return [
            'total_students' => $class->current_student_count,
            'max_students' => $class->max_students,
            'available_slots' => $class->available_slots,
            'has_available_slots' => $class->has_available_slots,
            'total_subjects' => $class->subjects()->count(),
            'total_teachers' => $class->teacherSubjects()->distinct('teacher_id')->count('teacher_id'),
        ];
    }
}
