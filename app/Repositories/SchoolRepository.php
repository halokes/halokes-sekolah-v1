<?php

namespace App\Repositories;

use App\Models\School;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SchoolRepository
{
    public function getAllSchools(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = School::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("name", "asc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getSchoolById($schoolId): ?School
    {
        return School::find($schoolId);
    }

    public function getSchoolByCode($code): ?School
    {
        return School::where('code', $code)->first();
    }

    public function createSchool($data)
    {
        return School::create($data);
    }

    public function update($schoolId, $data)
    {
        $school = School::find($schoolId);
        if ($school) {
            $school->update($data);
            return $school;
        } else {
            throw new Exception("School not found");
        }
    }

    public function delete($schoolId): ?bool
    {
        try {
            $school = School::findOrFail($schoolId);
            $school->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveSchools()
    {
        return School::active()->get();
    }

    public function getInactiveSchools()
    {
        return School::inactive()->get();
    }

    public function toggleStatus($schoolId)
    {
        $school = School::find($schoolId);
        if ($school) {
            $school->is_active = !$school->is_active;
            $school->save();
            return $school;
        } else {
            throw new Exception("School not found");
        }
    }
}
