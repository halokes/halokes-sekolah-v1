<?php

namespace App\Repositories;

use App\Models\ParentStudent;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ParentStudentRepository
{
    public function getAllParentStudents(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = ParentStudent::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("created_at", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getParentStudentById($parentStudentId): ?ParentStudent
    {
        return ParentStudent::find($parentStudentId);
    }

    public function createParentStudent($data)
    {
        return ParentStudent::create($data);
    }

    public function update($parentStudentId, $data)
    {
        $parentStudent = ParentStudent::find($parentStudentId);
        if ($parentStudent) {
            $parentStudent->update($data);
            return $parentStudent;
        } else {
            throw new Exception("Parent-Student relationship not found");
        }
    }

    public function delete($parentStudentId): ?bool
    {
        try {
            $parentStudent = ParentStudent::findOrFail($parentStudentId);
            $parentStudent->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getStudentsForParent($parentId)
    {
        return ParentStudent::forParent($parentId)
            ->with(['student'])
            ->orderBy('is_primary', 'desc')
            ->get();
    }

    public function getParentsForStudent($studentId)
    {
        return ParentStudent::forStudent($studentId)
            ->with(['parent'])
            ->orderBy('is_primary', 'desc')
            ->get();
    }

    public function getPrimaryParentForStudent($studentId)
    {
        return ParentStudent::forStudent($studentId)->primary()->first();
    }

    public function getParentStudentByRelationship($parentId, $relationship)
    {
        return ParentStudent::forParent($parentId)->relationship($relationship)->first();
    }

    public function getParentStudentByGuardianType($parentId, $guardianType)
    {
        return ParentStudent::forParent($parentId)->guardianType($guardianType)->get();
    }

    public function checkExistingRelationship($parentId, $studentId, $excludeId = null): bool
    {
        $query = ParentStudent::where('parent_id', $parentId)
            ->where('student_id', $studentId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function setPrimaryParent($parentStudentId)
    {
        DB::beginTransaction();
        try {
            $parentStudent = $this->getParentStudentById($parentStudentId);
            if (!$parentStudent) {
                throw new Exception("Parent-Student relationship not found");
            }

            // Set all other relationships for this student to not primary
            ParentStudent::where('student_id', $parentStudent->student_id)
                ->where('id', '!=', $parentStudentId)
                ->update(['is_primary' => false]);

            // Set the selected relationship as primary
            $parentStudent->is_primary = true;
            $parentStudent->save();

            DB::commit();
            return $parentStudent;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getParentStudentStatistics($parentId = null, $studentId = null): array
    {
        $query = ParentStudent::query();

        if ($parentId) {
            $query->forParent($parentId);
        }

        if ($studentId) {
            $query->forStudent($studentId);
        }

        $totalRelationships = $query->count();
        $primaryRelationships = $query->primary()->count();

        return [
            'total_relationships' => $totalRelationships,
            'primary_relationships' => $primaryRelationships,
            'non_primary_relationships' => $totalRelationships - $primaryRelationships,
        ];
    }
}
