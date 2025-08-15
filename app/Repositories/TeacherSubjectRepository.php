<?php

namespace App\Repositories;

use App\Models\TeacherSubject;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TeacherSubjectRepository
{
    public function getAllTeacherSubjects(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = TeacherSubject::query();

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

    public function getTeacherSubjectById($teacherSubjectId): ?TeacherSubject
    {
        return TeacherSubject::find($teacherSubjectId);
    }

    public function createTeacherSubject($data)
    {
        return TeacherSubject::create($data);
    }

    public function update($teacherSubjectId, $data)
    {
        $teacherSubject = TeacherSubject::find($teacherSubjectId);
        if ($teacherSubject) {
            $teacherSubject->update($data);
            return $teacherSubject;
        } else {
            throw new Exception("Teacher-Subject relationship not found");
        }
    }

    public function delete($teacherSubjectId): ?bool
    {
        try {
            $teacherSubject = TeacherSubject::findOrFail($teacherSubjectId);
            $teacherSubject->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getTeacherSubjectsForTeacher($teacherId, $academicYearId = null)
    {
        $query = TeacherSubject::forTeacher($teacherId)
            ->with(['subject', 'class', 'academicYear'])
            ->orderBy('created_at', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getTeachersForSubject($subjectId, $academicYearId = null)
    {
        $query = TeacherSubject::forSubject($subjectId)
            ->with(['teacher', 'class', 'academicYear'])
            ->orderBy('created_at', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getTeacherSubjectsForClass($classId, $academicYearId = null)
    {
        $query = TeacherSubject::forClass($classId)
            ->with(['teacher', 'subject', 'academicYear'])
            ->orderBy('created_at', 'desc');

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->get();
    }

    public function getTeacherSubjectsByAcademicYear($academicYearId)
    {
        return TeacherSubject::forAcademicYear($academicYearId)
            ->with(['teacher', 'subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTeacherSubjectsByRole($role, $academicYearId = null)
    {
        $query = TeacherSubject::teachingRole($role)
            ->with(['teacher', 'subject', 'class']);

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function checkExistingTeacherSubject($teacherId, $subjectId, $classId, $academicYearId, $excludeId = null): bool
    {
        $query = TeacherSubject::where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getTeacherSubjectStatistics($teacherId = null, $subjectId = null, $classId = null, $academicYearId = null): array
    {
        $query = TeacherSubject::query();

        if ($teacherId) {
            $query->forTeacher($teacherId);
        }

        if ($subjectId) {
            $query->forSubject($subjectId);
        }

        if ($classId) {
            $query->forClass($classId);
        }

        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }

        $totalAssignments = $query->count();
        $activeAssignments = $query->active()->count();
        $inactiveAssignments = $query->inactive()->count();

        return [
            'total_assignments' => $totalAssignments,
            'active_assignments' => $activeAssignments,
            'inactive_assignments' => $inactiveAssignments,
        ];
    }

    public function toggleStatus($teacherSubjectId)
    {
        $teacherSubject = TeacherSubject::find($teacherSubjectId);
        if ($teacherSubject) {
            $teacherSubject->is_active = !$teacherSubject->is_active;
            $teacherSubject->save();
            return $teacherSubject;
        } else {
            throw new Exception("Teacher-Subject relationship not found");
        }
    }
}
