<?php

namespace App\Services;

use App\Models\TeacherSubject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\TeacherSubjectRepository;

class TeacherSubjectService
{
    private $teacherSubjectRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(TeacherSubjectRepository $teacherSubjectRepository)
    {
        $this->teacherSubjectRepository = $teacherSubjectRepository;
    }

    /**
     * =============================================
     *  list all teacher-subject relationships along with filter, sort, etc
     * =============================================
     */
    public function listAllTeacherSubjects($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->teacherSubjectRepository->getAllTeacherSubjects($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single teacher-subject relationship data
     * =============================================
     */
    public function getTeacherSubjectDetail($teacherSubjectId): ?TeacherSubject
    {
        return $this->teacherSubjectRepository->getTeacherSubjectById($teacherSubjectId);
    }

    /**
     * =============================================
     * process add new teacher-subject relationship to database
     * =============================================
     */
    public function addNewTeacherSubject(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Check for existing relationship
            if ($this->teacherSubjectRepository->checkExistingTeacherSubject(
                $validatedData['teacher_id'],
                $validatedData['subject_id'],
                $validatedData['class_id'],
                $validatedData['academic_year_id']
            )) {
                throw new \Exception("This teacher is already assigned to this subject and class for the academic year");
            }

            $teacherSubject = $this->teacherSubjectRepository->createTeacherSubject($validatedData);
            DB::commit();
            return $teacherSubject;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new teacher-subject relationship to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update teacher-subject relationship data
     * =============================================
     */
    public function updateTeacherSubject(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $teacherSubject = $this->teacherSubjectRepository->getTeacherSubjectById($id);

            if (!$teacherSubject) {
                throw new \Exception("Teacher-Subject relationship not found");
            }

            // Check for existing relationship (excluding current record)
            if ($this->teacherSubjectRepository->checkExistingTeacherSubject(
                $validatedData['teacher_id'],
                $validatedData['subject_id'],
                $validatedData['class_id'],
                $validatedData['academic_year_id'],
                $id
            )) {
                throw new \Exception("This teacher is already assigned to this subject and class for the academic year");
            }

            $teacherSubject = $this->teacherSubjectRepository->update($id, $validatedData);
            DB::commit();
            return $teacherSubject;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update teacher-subject relationship in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete teacher-subject relationship
     * =============================================
     */
    public function deleteTeacherSubject($teacherSubjectId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->teacherSubjectRepository->delete($teacherSubjectId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete teacher-subject relationship with id $teacherSubjectId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get teacher-subjects for teacher
     * =============================================
     */
    public function getTeacherSubjectsForTeacher($teacherId, $academicYearId = null)
    {
        return $this->teacherSubjectRepository->getTeacherSubjectsForTeacher($teacherId, $academicYearId);
    }

    /**
     * =============================================
     * get teachers for subject
     * =============================================
     */
    public function getTeachersForSubject($subjectId, $academicYearId = null)
    {
        return $this->teacherSubjectRepository->getTeachersForSubject($subjectId, $academicYearId);
    }

    /**
     * =============================================
     * get teacher-subjects for class
     * =============================================
     */
    public function getTeacherSubjectsForClass($classId, $academicYearId = null)
    {
        return $this->teacherSubjectRepository->getTeacherSubjectsForClass($classId, $academicYearId);
    }

    /**
     * =============================================
     * get teacher-subjects by academic year
     * =============================================
     */
    public function getTeacherSubjectsByAcademicYear($academicYearId)
    {
        return $this->teacherSubjectRepository->getTeacherSubjectsByAcademicYear($academicYearId);
    }

    /**
     * =============================================
     * get teacher-subjects by role
     * =============================================
     */
    public function getTeacherSubjectsByRole($role, $academicYearId = null)
    {
        return $this->teacherSubjectRepository->getTeacherSubjectsByRole($role, $academicYearId);
    }

    /**
     * =============================================
     * check for existing teacher-subject relationship
     * =============================================
     */
    public function checkExistingTeacherSubject($teacherId, $subjectId, $classId, $academicYearId, $excludeId = null): bool
    {
        return $this->teacherSubjectRepository->checkExistingTeacherSubject($teacherId, $subjectId, $classId, $academicYearId, $excludeId);
    }

    /**
     * =============================================
     * get teacher-subject statistics
     * =============================================
     */
    public function getTeacherSubjectStatistics($teacherId = null, $subjectId = null, $classId = null, $academicYearId = null): array
    {
        return $this->teacherSubjectRepository->getTeacherSubjectStatistics($teacherId, $subjectId, $classId, $academicYearId);
    }

    /**
     * =============================================
     * toggle status
     * =============================================
     */
    public function toggleTeacherSubjectStatus($teacherSubjectId): ?TeacherSubject
    {
        DB::beginTransaction();
        try {
            $teacherSubject = $this->teacherSubjectRepository->toggleStatus($teacherSubjectId);
            DB::commit();
            return $teacherSubject;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle teacher-subject status with id $teacherSubjectId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get teacher's assigned classes
     * =============================================
     */
    public function getTeachersAssignedClasses($teacherId, $academicYearId = null)
    {
        $query = TeacherSubject::forTeacher($teacherId);
        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }
        return $query->with('class')->get()->pluck('class')->unique('id');
    }

    /**
     * =============================================
     * get teacher's assigned subjects
     * =============================================
     */
    public function getTeachersAssignedSubjects($teacherId, $academicYearId = null)
    {
        $query = TeacherSubject::forTeacher($teacherId);
        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }
        return $query->with('subject')->get()->pluck('subject')->unique('id');
    }

    /**
     * =============================================
     * get class's assigned teachers
     * =============================================
     */
    public function getClassAssignedTeachers($classId, $academicYearId = null)
    {
        $query = TeacherSubject::forClass($classId);
        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }
        return $query->with('teacher')->get()->pluck('teacher')->unique('id');
    }

    /**
     * =============================================
     * get subject's assigned teachers
     * =============================================
     */
    public function getSubjectAssignedTeachers($subjectId, $academicYearId = null)
    {
        $query = TeacherSubject::forSubject($subjectId);
        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }
        return $query->with('teacher')->get()->pluck('teacher')->unique('id');
    }
}
