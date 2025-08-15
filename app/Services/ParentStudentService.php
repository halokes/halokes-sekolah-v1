<?php

namespace App\Services;

use App\Models\ParentStudent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ParentStudentRepository;
use App\Repositories\UserRepository;

class ParentStudentService
{
    private $parentStudentRepository;
    private $userRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(ParentStudentRepository $parentStudentRepository, UserRepository $userRepository)
    {
        $this->parentStudentRepository = $parentStudentRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * =============================================
     *  list all parent-student relationships along with filter, sort, etc
     * =============================================
     */
    public function listAllParentStudents($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->parentStudentRepository->getAllParentStudents($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single parent-student relationship data
     * =============================================
     */
    public function getParentStudentDetail($parentStudentId): ?ParentStudent
    {
        return $this->parentStudentRepository->getParentStudentById($parentStudentId);
    }

    /**
     * =============================================
     * process add new parent-student relationship to database
     * =============================================
     */
    public function addNewParentStudent(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check for existing relationship
            if ($this->parentStudentRepository->checkExistingRelationship(
                $validatedData['parent_id'],
                $validatedData['student_id']
            )) {
                throw new \Exception("This parent-student relationship already exists");
            }

            $parentStudent = $this->parentStudentRepository->createParentStudent($validatedData);
            DB::commit();
            return $parentStudent;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new parent-student relationship to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update parent-student relationship data
     * =============================================
     */
    public function updateParentStudent(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $parentStudent = $this->parentStudentRepository->getParentStudentById($id);

            if (!$parentStudent) {
                throw new \Exception("Parent-Student relationship not found");
            }

            // Check for existing relationship (excluding current record)
            if ($this->parentStudentRepository->checkExistingRelationship(
                $validatedData['parent_id'],
                $validatedData['student_id'],
                $id
            )) {
                throw new \Exception("This parent-student relationship already exists");
            }

            $parentStudent = $this->parentStudentRepository->update($id, $validatedData);
            DB::commit();
            return $parentStudent;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update parent-student relationship in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete parent-student relationship
     * =============================================
     */
    public function deleteParentStudent($parentStudentId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->parentStudentRepository->delete($parentStudentId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete parent-student relationship with id $parentStudentId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get students for parent
     * =============================================
     */
    public function getStudentsForParent($parentId)
    {
        return $this->parentStudentRepository->getStudentsForParent($parentId);
    }

    /**
     * =============================================
     * get parents for student
     * =============================================
     */
    public function getParentsForStudent($studentId)
    {
        return $this->parentStudentRepository->getParentsForStudent($studentId);
    }

    /**
     * =============================================
     * get primary parent for student
     * =============================================
     */
    public function getPrimaryParentForStudent($studentId)
    {
        return $this->parentStudentRepository->getPrimaryParentForStudent($studentId);
    }

    /**
     * =============================================
     * get parent-student by relationship
     * =============================================
     */
    public function getParentStudentByRelationship($parentId, $relationship)
    {
        return $this->parentStudentRepository->getParentStudentByRelationship($parentId, $relationship);
    }

    /**
     * =============================================
     * get parent-student by guardian type
     * =============================================
     */
    public function getParentStudentByGuardianType($parentId, $guardianType)
    {
        return $this->parentStudentRepository->getParentStudentByGuardianType($parentId, $guardianType);
    }

    /**
     * =============================================
     * check for existing relationship
     * =============================================
     */
    public function checkExistingRelationship($parentId, $studentId, $excludeId = null): bool
    {
        return $this->parentStudentRepository->checkExistingRelationship($parentId, $studentId, $excludeId);
    }

    /**
     * =============================================
     * set primary parent
     * =============================================
     */
    public function setPrimaryParent($parentStudentId): ?ParentStudent
    {
        DB::beginTransaction();
        try {
            $parentStudent = $this->parentStudentRepository->setPrimaryParent($parentStudentId);
            DB::commit();
            return $parentStudent;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to set primary parent for id $parentStudentId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get parent-student statistics
     * =============================================
     */
    public function getParentStudentStatistics($parentId = null, $studentId = null): array
    {
        return $this->parentStudentRepository->getParentStudentStatistics($parentId, $studentId);
    }

    /**
     * =============================================
     * get students with no primary parent
     * =============================================
     */
    public function getStudentsWithNoPrimaryParent()
    {
        return $this->userRepository->getAllUsers(1000, null, null, null) // Fetch a large number or all users
            ->filter(function ($user) {
                return $user->isStudent() && !$this->getPrimaryParentForStudent($user->id);
            });
    }

    /**
     * =============================================
     * get parents with no students
     * =============================================
     */
    public function getParentsWithNoStudents()
    {
        return $this->userRepository->getAllUsers(1000, null, null, null) // Fetch a large number or all users
            ->filter(function ($user) {
                return $user->isParent() && $this->getStudentsForParent($user->id)->isEmpty();
            });
    }
}
