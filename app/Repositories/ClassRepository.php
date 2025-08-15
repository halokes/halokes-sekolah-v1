<?php

namespace App\Repositories;

use App\Models\ClassModel;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClassRepository
{
    public function getAllClasses(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = ClassModel::query();

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

    public function getClassById($classId): ?ClassModel
    {
        return ClassModel::find($classId);
    }

    public function getClassByCode($classCode): ?ClassModel
    {
        return ClassModel::where('class_code', $classCode)->first();
    }

    public function createClass($data)
    {
        return ClassModel::create($data);
    }

    public function update($classId, $data)
    {
        $class = ClassModel::find($classId);
        if ($class) {
            $class->update($data);
            return $class;
        } else {
            throw new Exception("Class not found");
        }
    }

    public function delete($classId): ?bool
    {
        try {
            $class = ClassModel::findOrFail($classId);
            $class->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveClasses()
    {
        return ClassModel::active()->ordered()->get();
    }

    public function getInactiveClasses()
    {
        return ClassModel::inactive()->ordered()->get();
    }

    public function getClassesBySchool($schoolId)
    {
        return ClassModel::forSchool($schoolId)->active()->ordered()->get();
    }

    public function getClassesByAcademicYear($academicYearId)
    {
        return ClassModel::forAcademicYear($academicYearId)->active()->ordered()->get();
    }

    public function getClassesByLevel($levelId)
    {
        return ClassModel::forLevel($levelId)->active()->ordered()->get();
    }

    public function getClassesWithAvailableSlots()
    {
        return ClassModel::withAvailableSlots()->active()->ordered()->get();
    }

    public function getClassStudentCount($classId)
    {
        $class = ClassModel::find($classId);
        if ($class) {
            return $class->students()->where('status', 'active')->count();
        }
        return 0;
    }

    public function isClassCodeExists(string $classCode, string $excludeId = null, string $schoolId = null, string $academicYearId = null): bool
    {
        $query = ClassModel::where('class_code', $classCode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->exists();
    }

    public function getClassesByHomeroomTeacher($teacherId)
    {
        return ClassModel::where('homeroom_teacher_id', $teacherId)->active()->ordered()->get();
    }

    public function toggleStatus($classId)
    {
        $class = ClassModel::find($classId);
        if ($class) {
            $class->is_active = !$class->is_active;
            $class->save();
            return $class;
        } else {
            throw new Exception("Class not found");
        }
    }

    public function getMaxOrder($schoolId = null, $academicYearId = null, $levelId = null)
    {
        $query = ClassModel::query();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        return $query->max('order') + 1;
    }

    public function reorderClasses(array $orderData)
    {
        DB::beginTransaction();
        try {
            foreach ($orderData as $id => $order) {
                $this->update($id, ['order' => $order]);
            }
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
