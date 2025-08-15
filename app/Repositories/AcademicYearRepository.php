<?php

namespace App\Repositories;

use App\Models\AcademicYear;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AcademicYearRepository
{
    public function getAllAcademicYears(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = AcademicYear::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("start_date", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getAcademicYearById($academicYearId): ?AcademicYear
    {
        return AcademicYear::find($academicYearId);
    }

    public function getAcademicYearByCode($yearCode): ?AcademicYear
    {
        return AcademicYear::where('year_code', $yearCode)->first();
    }

    public function createAcademicYear($data)
    {
        return AcademicYear::create($data);
    }

    public function update($academicYearId, $data)
    {
        $academicYear = AcademicYear::find($academicYearId);
        if ($academicYear) {
            $academicYear->update($data);
            return $academicYear;
        } else {
            throw new Exception("Academic Year not found");
        }
    }

    public function delete($academicYearId): ?bool
    {
        try {
            $academicYear = AcademicYear::findOrFail($academicYearId);
            $academicYear->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveAcademicYears()
    {
        return AcademicYear::active()->orderBy('start_date', 'desc')->get();
    }

    public function getInactiveAcademicYears()
    {
        return AcademicYear::inactive()->orderBy('start_date', 'desc')->get();
    }

    public function getCurrentAcademicYear()
    {
        return AcademicYear::current()->first();
    }

    public function setCurrentAcademicYear($academicYearId)
    {
        DB::beginTransaction();
        try {
            // First, remove current status from all academic years
            AcademicYear::query()->update(['is_current' => false]);

            // Then set the specified academic year as current
            $academicYear = AcademicYear::findOrFail($academicYearId);
            $academicYear->is_current = true;
            $academicYear->save();

            DB::commit();
            return $academicYear;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAcademicYearsBySchool($schoolId)
    {
        return AcademicYear::forSchool($schoolId)->orderBy('start_date', 'desc')->get();
    }

    public function getAcademicYearsInDateRange($startDate, $endDate)
    {
        return AcademicYear::dateRange($startDate, $endDate)->get();
    }

    public function isYearCodeExists(string $yearCode, string $excludeId = null): bool
    {
        $query = AcademicYear::where('year_code', $yearCode);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function isDateRangeOverlapping($startDate, $endDate, $excludeId = null): bool
    {
        $query = AcademicYear::query()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                      $subQuery->where('start_date', '<=', $startDate)
                               ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getUpcomingAcademicYears($limit = 5)
    {
        return AcademicYear::active()
            ->where('start_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getPreviousAcademicYears($limit = 5)
    {
        return AcademicYear::active()
            ->where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->limit($limit)
            ->get();
    }
}
