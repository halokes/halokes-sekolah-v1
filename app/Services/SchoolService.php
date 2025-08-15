<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\SchoolRepository;

class SchoolService
{
    private $schoolRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(SchoolRepository $schoolRepository)
    {
        $this->schoolRepository = $schoolRepository;
    }

    /**
     * =============================================
     *  list all schools along with filter, sort, etc
     * =============================================
     */
    public function listAllSchools($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->schoolRepository->getAllSchools($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single school data
     * =============================================
     */
    public function getSchoolDetail($schoolId): ?School
    {
        return $this->schoolRepository->getSchoolById($schoolId);
    }

    /**
     * =============================================
     * get school by code
     * =============================================
     */
    public function getSchoolByCode(string $code): ?School
    {
        return $this->schoolRepository->getSchoolByCode($code);
    }

    /**
     * =============================================
     * process add new school to database
     * =============================================
     */
    public function addNewSchool(array $validatedData)
    {
        DB::beginTransaction();
        try {
            $school = $this->schoolRepository->createSchool($validatedData);
            DB::commit();
            return $school;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new school to database: {$exception->getMessage()}");
            return null;
        }
    }

    /**
     * =============================================
     * process update school data
     * =============================================
     */
    public function updateSchool(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $school = $this->schoolRepository->update($id, $validatedData);
            DB::commit();
            return $school;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update school in the database: {$exception->getMessage()}");
            return null;
        }
    }

    /**
     * =============================================
     * process delete school
     * =============================================
     */
    public function deleteSchool($schoolId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->schoolRepository->delete($schoolId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete school with id $schoolId: {$exception->getMessage()}");
            return false;
        }
    }

    /**
     * =============================================
     * toggle school status
     * =============================================
     */
    public function toggleSchoolStatus($schoolId): ?School
    {
        DB::beginTransaction();
        try {
            $school = $this->schoolRepository->toggleStatus($schoolId);
            DB::commit();
            return $school;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle school status with id $schoolId: {$exception->getMessage()}");
            return null;
        }
    }

    /**
     * =============================================
     * get active schools
     * =============================================
     */
    public function getActiveSchools()
    {
        return $this->schoolRepository->getActiveSchools();
    }

    /**
     * =============================================
     * get inactive schools
     * =============================================
     */
    public function getInactiveSchools()
    {
        return $this->schoolRepository->getInactiveSchools();
    }

    /**
     * =============================================
     * check if school code exists
     * =============================================
     */
    public function isSchoolCodeExists(string $code, string $excludeId = null): bool
    {
        $query = School::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
