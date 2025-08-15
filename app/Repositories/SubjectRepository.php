<?php

namespace App\Repositories;

use App\Models\Subject;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubjectRepository
{
    public function getAllSubjects(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Subject::query();

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

    public function getSubjectById($subjectId): ?Subject
    {
        return Subject::find($subjectId);
    }

    public function getSubjectByCode($code): ?Subject
    {
        return Subject::where('code', $code)->first();
    }

    public function createSubject($data)
    {
        return Subject::create($data);
    }

    public function update($subjectId, $data)
    {
        $subject = Subject::find($subjectId);
        if ($subject) {
            $subject->update($data);
            return $subject;
        } else {
            throw new Exception("Subject not found");
        }
    }

    public function delete($subjectId): ?bool
    {
        try {
            $subject = Subject::findOrFail($subjectId);
            $subject->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getActiveSubjects()
    {
        return Subject::active()->ordered()->get();
    }

    public function getInactiveSubjects()
    {
        return Subject::inactive()->ordered()->get();
    }

    public function getSubjectsBySchool($schoolId)
    {
        return Subject::forSchool($schoolId)->active()->ordered()->get();
    }

    public function getSubjectsByLevel($levelId)
    {
        return Subject::forLevel($levelId)->active()->ordered()->get();
    }

    public function getSubjectsByCategory($category)
    {
        return Subject::category($category)->active()->ordered()->get();
    }

    public function getAcademicSubjects()
    {
        return Subject::academic()->active()->ordered()->get();
    }

    public function getExtracurricularSubjects()
    {
        return Subject::extracurricular()->active()->ordered()->get();
    }

    public function getSkillSubjects()
    {
        return Subject::skill()->active()->ordered()->get();
    }

    public function isCodeExists(string $code, string $excludeId = null): bool
    {
        $query = Subject::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function getMaxOrder($schoolId = null, $levelId = null)
    {
        $query = Subject::query();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        return $query->max('order') + 1;
    }

    public function getSubjectStatistics($subjectId): array
    {
        $subject = $this->getSubjectById($subjectId);
        if (!$subject) {
            return [];
        }

        return [
            'teacher_count' => $subject->teacher_count,
            'class_count' => $subject->class_count,
            'assignment_count' => $subject->assignment_count,
            'average_grade' => $subject->average_grade,
        ];
    }

    public function getSubjectsByTeacher($teacherId)
    {
        return Subject::whereHas('teacherSubjects', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->active()->ordered()->get();
    }

    public function getSubjectsByClass($classId)
    {
        return Subject::whereHas('teacherSubjects', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->active()->ordered()->get();
    }

    public function getSubjectsByAcademicYear($academicYearId)
    {
        return Subject::whereHas('teacherSubjects', function ($query) use ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        })->active()->ordered()->get();
    }

    public function toggleStatus($subjectId)
    {
        $subject = Subject::find($subjectId);
        if ($subject) {
            $subject->is_active = !$subject->is_active;
            $subject->save();
            return $subject;
        } else {
            throw new Exception("Subject not found");
        }
    }

    public function reorderSubjects(array $orderData)
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
