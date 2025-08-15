<?php

namespace App\Services;

use App\Models\SchoolLevel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\SchoolLevelRepository;

class SchoolLevelService
{
    private $schoolLevelRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(SchoolLevelRepository $schoolLevelRepository)
    {
        $this->schoolLevelRepository = $schoolLevelRepository;
    }

    /**
     * =============================================
     *  list all school levels along with filter, sort, etc
     * =============================================
     */
    public function listAllSchoolLevels($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->schoolLevelRepository->getAllSchoolLevels($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single school level data
     * =============================================
     */
    public function getSchoolLevelDetail($schoolLevelId): ?SchoolLevel
    {
        return $this->schoolLevelRepository->getSchoolLevelById($schoolLevelId);
    }

    /**
     * =============================================
     * get school level by code
     * =============================================
     */
    public function getSchoolLevelByCode(string $code): ?SchoolLevel
    {
        return $this->schoolLevelRepository->getSchoolLevelByCode($code);
    }

    /**
     * =============================================
     * process add new school level to database
     * =============================================
     */
    public function addNewSchoolLevel(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Set order if not provided
            if (!isset($validatedData['order'])) {
                $validatedData['order'] = $this->schoolLevelRepository->getMaxOrder($validatedData['school_id'] ?? null);
            }

            $schoolLevel = $this->schoolLevelRepository->createSchoolLevel($validatedData);
            DB::commit();
            return $schoolLevel;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new school level to database: {$exception->getMessage()}");
            return null;
        }
    }

    /**
     * =============================================
     * process update school level data
     * =============================================
     */
    public function updateSchoolLevel(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $schoolLevel = $this->schoolLevelRepository->update($id, $validatedData);
            DB::commit();
            return $schoolLevel;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update school level in the database: {$exception->getMessage()}");
            return null;
        }
    }

    /**
     * =============================================
     * process delete school level
     * =============================================
     */
    public function deleteSchoolLevel($schoolLevelId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->schoolLevelRepository->delete($schoolLevelId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete school level with id $schoolLevelId: {$exception->getMessage()}");
            return false;
        }
    }

    /**
     * =============================================
     * toggle school level status
     * =============================================
     */
    public function toggleSchoolLevelStatus($schoolLevelId): ?SchoolLevel
    {
        DB::beginTransaction();
        try {
            $schoolLevel = $this->schoolLevelRepository->toggleStatus($schoolLevelId);
            DB::commit();
            return $schoolLevel;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle school level status with id $schoolLevelId: {$exception->getMessage()}");
            return null;
        }
    }

    /**
     * =============================================
     * get active school levels
     * =============================================
     */
    public function getActiveSchoolLevels()
    {
        return $this->schoolLevelRepository->getActiveSchoolLevels();
    }

    /**
     * =============================================
     * get inactive school levels
     * =============================================
     */
    public function getInactiveSchoolLevels()
    {
        return $this->schoolLevelRepository->getInactiveSchoolLevels();
    }

    /**
     * =============================================
     * get school levels by school
     * =============================================
     */
    public function getSchoolLevelsBySchool($schoolId)
    {
        return $this->schoolLevelRepository->getSchoolLevelsBySchool($schoolId);
    }

    /**
     * =============================================
     * check if school level code exists
     * =============================================
     */
    public function isSchoolLevelCodeExists(string $code, string $excludeId = null): bool
    {
        return $this->schoolLevelRepository->isCodeExists($code, $excludeId);
    }

    /**
     * =============================================
     * reorder school levels
     * =============================================
     */
    public function reorderSchoolLevels(array $orderData)
    {
        DB::beginTransaction();
        try {
            foreach ($orderData as $id => $order) {
                $this->schoolLevelRepository->update($id, ['order' => $order]);
            }
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to reorder school levels: {$exception->getMessage()}");
            return false;
        }
    }
}
