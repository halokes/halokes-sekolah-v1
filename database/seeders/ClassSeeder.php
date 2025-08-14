<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all schools and academic years
        $schools = School::all();
        $academicYears = AcademicYear::all();

        foreach ($schools as $school) {
            $schoolLevels = SchoolLevel::all();

            foreach ($schoolLevels as $level) {
                $currentAcademicYear = $academicYears->where('school_id', $school->id)
                    ->where('is_current', true)
                    ->first();

                if (!$currentAcademicYear) {
                    continue;
                }

                // Determine class names based on level
                $classNames = $this->getClassNamesByLevel($level->code);
                $classCodes = $this->getClassCodesByLevel($level->code);

                foreach ($classNames as $index => $className) {
                    $classCode = $classCodes[$index] ?? $level->code . '-' . ($index + 1);

                    ClassModel::create([
                        'name' => $className,
                        'class_code' => $classCode,
                        'school_id' => $school->id,
                        'level_id' => $level->id,
                        'academic_year_id' => $currentAcademicYear->id,
                        'homeroom_teacher_id' => null, // Will be assigned later
                        'max_students' => $this->getMaxStudentsByLevel($level->code),
                        'description' => 'Kelas ' . $className . ' untuk jenjang ' . $level->name,
                        'is_active' => true,
                        'order' => $index + 1,
                    ]);
                }
            }
        }
    }

    /**
     * Get class names based on school level
     */
    private function getClassNamesByLevel($levelCode)
    {
        switch ($levelCode) {
            case 'SD':
                return ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
            case 'SMP':
                return ['Kelas 7', 'Kelas 8', 'Kelas 9'];
            case 'SMA':
                return ['Kelas 10', 'Kelas 11', 'Kelas 12'];
            default:
                return [];
        }
    }

    /**
     * Get class codes based on school level
     */
    private function getClassCodesByLevel($levelCode)
    {
        switch ($levelCode) {
            case 'SD':
                return ['1', '2', '3', '4', '5', '6'];
            case 'SMP':
                return ['7', '8', '9'];
            case 'SMA':
                return ['10', '11', '12'];
            default:
                return [];
        }
    }

    /**
     * Get maximum students based on school level
     */
    private function getMaxStudentsByLevel($levelCode)
    {
        switch ($levelCode) {
            case 'SD':
                return 30;
            case 'SMP':
                return 35;
            case 'SMA':
                return 40;
            default:
                return 30;
        }
    }
}
