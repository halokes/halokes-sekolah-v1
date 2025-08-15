<?php

namespace App\Repositories;

use App\Models\SchoolLevel;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SchoolLevelRepository
{
    public function getAllSchoolLevels(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = SchoolLevel::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->ordered()->orderBy("name", "asc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getSchoolLevelById($schoolLevelId): ?SchoolLevel
    {
        return SchoolLevel::find($schoolLevelId);
    }

    public function getSchoolLevelByCode($code): ?SchoolLevel
    {
        return SchoolLevel::where('code', $code)->first();
    }

    public function createSchoolLevel($data)
    {
        return SchoolLevel::create($data);
    }

    public function update($schoolLevelId, $data)
    {
        $schoolLevel = SchoolLevel::find($schoolLevelId);
        if ($schoolLevel) {
            $schoolLevel->update($data);
            return $schoolLevel;
        } else {
            throw new Exception("School Level not found");
        }
    }

    public function delete($schoolLevelId): ?bool
    {
        try {
            $schoolLevel = SchoolLevel::findOrFail($schoolLevelId);
            $schoolLevel->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveSchoolLevels()
    {
        return SchoolLevel::active()->ordered()->get();
    }

    public function getInactiveSchoolLevels()
    {
        return SchoolLevel::inactive()->ordered()->get();
    }

    public function toggleStatus($schoolLevelId)
    {
        $schoolLevel = SchoolLevel::find($schoolLevelId);
        if ($schoolLevel) {
            $schoolLevel->is_active = !$schoolLevel->is_active;
            $schoolLevel->save();
            return $schoolLevel;
        } else {
            throw new Exception("School Level not found");
        }
    }

    public function getSchoolLevelsBySchool($schoolId)
    {
        return SchoolLevel::where('school_id', $schoolId)->active()->ordered()->get();
    }

    public function getMaxOrder($schoolId = null)
    {
        $query = SchoolLevel::query();
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        return $query->max('order') + 1;
    }

    public function isCodeExists(string $code, string $excludeId = null): bool
    {
        $query = SchoolLevel::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
