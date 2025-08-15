<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\AcademicYearRepository;

class AcademicYearService
{
    private $academicYearRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(AcademicYearRepository $academicYearRepository)
    {
        $this->academicYearRepository = $academicYearRepository;
    }

    /**
     * =============================================
     *  list all academic years along with filter, sort, etc
     * =============================================
     */
    public function listAllAcademicYears($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->academicYearRepository->getAllAcademicYears($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single academic year data
     * =============================================
     */
    public function getAcademicYearDetail($academicYearId): ?AcademicYear
    {
        return $this->academicYearRepository->getAcademicYearById($academicYearId);
    }

    /**
     * =============================================
     * get academic year by code
     * =============================================
     */
    public function getAcademicYearByCode(string $yearCode): ?AcademicYear
    {
        return $this->academicYearRepository->getAcademicYearByCode($yearCode);
    }

    /**
     * =============================================
     * process add new academic year to database
     * =============================================
     */
    public function addNewAcademicYear(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check if year code already exists
            if ($this->academicYearRepository->isYearCodeExists($validatedData['year_code'])) {
                throw new \Exception("Year code already exists");
            }

            // Check if date range overlaps with existing academic years
            if ($this->academicYearRepository->isDateRangeOverlapping(
                $validatedData['start_date'],
                $validatedData['end_date']
            )) {
                throw new \Exception("Date range overlaps with existing academic year");
            }

            $academicYear = $this->academicYearRepository->createAcademicYear($validatedData);
            DB::commit();
            return $academicYear;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new academic year to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update academic year data
     * =============================================
     */
    public function updateAcademicYear(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $academicYear = $this->academicYearRepository->getAcademicYearById($id);

            if (!$academicYear) {
                throw new \Exception("Academic Year not found");
            }

            // Check if year code already exists (excluding current record)
            if ($this->academicYearRepository->isYearCodeExists($validatedData['year_code'], $id)) {
                throw new \Exception("Year code already exists");
            }

            // Check if date range overlaps with existing academic years (excluding current record)
            if ($this->academicYearRepository->isDateRangeOverlapping(
                $validatedData['start_date'],
                $validatedData['end_date'],
                $id
            )) {
                throw new \Exception("Date range overlaps with existing academic year");
            }

            $academicYear = $this->academicYearRepository->update($id, $validatedData);
            DB::commit();
            return $academicYear;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update academic year in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete academic year
     * =============================================
     */
    public function deleteAcademicYear($academicYearId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->academicYearRepository->delete($academicYearId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete academic year with id $academicYearId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get active academic years
     * =============================================
     */
    public function getActiveAcademicYears()
    {
        return $this->academicYearRepository->getActiveAcademicYears();
    }

    /**
     * =============================================
     * get inactive academic years
     * =============================================
     */
    public function getInactiveAcademicYears()
    {
        return $this->academicYearRepository->getInactiveAcademicYears();
    }

    /**
     * =============================================
     * get current academic year
     * =============================================
     */
    public function getCurrentAcademicYear()
    {
        return $this->academicYearRepository->getCurrentAcademicYear();
    }

    /**
     * =============================================
     * set current academic year
     * =============================================
     */
    public function setCurrentAcademicYear($academicYearId): ?AcademicYear
    {
        DB::beginTransaction();
        try {
            $academicYear = $this->academicYearRepository->setCurrentAcademicYear($academicYearId);
            DB::commit();
            return $academicYear;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to set current academic year with id $academicYearId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get academic years by school
     * =============================================
     */
    public function getAcademicYearsBySchool($schoolId)
    {
        return $this->academicYearRepository->getAcademicYearsBySchool($schoolId);
    }

    /**
     * =============================================
     * get academic years in date range
     * =============================================
     */
    public function getAcademicYearsInDateRange($startDate, $endDate)
    {
        return $this->academicYearRepository->getAcademicYearsInDateRange($startDate, $endDate);
    }

    /**
     * =============================================
     * check if academic year code exists
     * =============================================
     */
    public function isAcademicYearCodeExists(string $yearCode, string $excludeId = null): bool
    {
        return $this->academicYearRepository->isYearCodeExists($yearCode, $excludeId);
    }

    /**
     * =============================================
     * check if date range overlaps
     * =============================================
     */
    public function isDateRangeOverlapping($startDate, $endDate, $excludeId = null): bool
    {
        return $this->academicYearRepository->isDateRangeOverlapping($startDate, $endDate, $excludeId);
    }

    /**
     * =============================================
     * get upcoming academic years
     * =============================================
     */
    public function getUpcomingAcademicYears($limit = 5)
    {
        return $this->academicYearRepository->getUpcomingAcademicYears($limit);
    }

    /**
     * =============================================
     * get previous academic years
     * =============================================
     */
    public function getPreviousAcademicYears($limit = 5)
    {
        return $this->academicYearRepository->getPreviousAcademicYears($limit);
    }
}
