<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mata pelajaran untuk SD
        $sdSubjects = [
            ['name' => 'Bahasa Indonesia', 'code' => 'BHS_ID', 'category' => 'academic', 'level_id' => 1, 'order' => 1],
            ['name' => 'Matematika', 'code' => 'MATH', 'category' => 'academic', 'level_id' => 1, 'order' => 2],
            ['name' => 'IPA (Ilmu Pengetahuan Alam)', 'code' => 'IPA', 'category' => 'academic', 'level_id' => 1, 'order' => 3],
            ['name' => 'IPS (Ilmu Pengetahuan Sosial)', 'code' => 'IPS', 'category' => 'academic', 'level_id' => 1, 'order' => 4],
            ['name' => 'Pendidikan Agama', 'code' => 'AGAMA', 'category' => 'academic', 'level_id' => 1, 'order' => 5],
            ['name' => 'Pendidikan Jasmani', 'code' => 'PJOK', 'category' => 'academic', 'level_id' => 1, 'order' => 6],
            ['name' => 'Seni Budaya', 'code' => 'SENIBUD', 'category' => 'academic', 'level_id' => 1, 'order' => 7],
            ['name' => 'Prakarya', 'code' => 'PRAKARYA', 'category' => 'academic', 'level_id' => 1, 'order' => 8],
        ];

        // Mata pelajaran untuk SMP
        $smpSubjects = [
            ['name' => 'Bahasa Indonesia', 'code' => 'BHS_ID_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 1],
            ['name' => 'Matematika', 'code' => 'MATH_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 2],
            ['name' => 'IPA (Ilmu Pengetahuan Alam)', 'code' => 'IPA_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 3],
            ['name' => 'IPS (Ilmu Pengetahuan Sosial)', 'code' => 'IPS_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 4],
            ['name' => 'Pendidikan Agama', 'code' => 'AGAMA_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 5],
            ['name' => 'Pendidikan Jasmani', 'code' => 'PJOK_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 6],
            ['name' => 'Seni Budaya', 'code' => 'SENIBUD_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 7],
            ['name' => 'Prakarya', 'code' => 'PRAKARYA_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 8],
            ['name' => 'Bahasa Inggris', 'code' => 'INGGRIS_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 9],
            ['name' => 'PKN (Pendidikan Kewarganegaraan)', 'code' => 'PKN_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 10],
            ['name' => 'Informatika', 'code' => 'IF_SMP', 'category' => 'academic', 'level_id' => 2, 'order' => 11],
        ];

        // Mata pelajaran untuk SMA
        $smaSubjects = [
            ['name' => 'Bahasa Indonesia', 'code' => 'BHS_ID_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 1],
            ['name' => 'Matematika', 'code' => 'MATH_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 2],
            ['name' => 'Fisika', 'code' => 'FISIKA_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 3],
            ['name' => 'Kimia', 'code' => 'KIMIA_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 4],
            ['name' => 'Biologi', 'code' => 'BIOLOGI_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 5],
            ['name' => 'Ekonomi', 'code' => 'EKONOMI_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 6],
            ['name' => 'Sosiologi', 'code' => 'SOSIOLOGI_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 7],
            ['name' => 'Sejarah', 'code' => 'SEJARAH_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 8],
            ['name' => 'Geografi', 'code' => 'GEOGRAFI_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 9],
            ['name' => 'Bahasa Inggris', 'code' => 'INGGRIS_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 10],
            ['name' => 'Pendidikan Agama', 'code' => 'AGAMA_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 11],
            ['name' => 'Pendidikan Jasmani', 'code' => 'PJOK_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 12],
            ['name' => 'Seni Budaya', 'code' => 'SENIBUD_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 13],
            ['name' => 'Prakarya', 'code' => 'PRAKARYA_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 14],
            ['name' => 'PKN (Pendidikan Kewarganegaraan)', 'code' => 'PKN_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 15],
            ['name' => 'Informatika', 'code' => 'IF_SMA', 'category' => 'academic', 'level_id' => 3, 'order' => 16],
        ];

        // Mata pelajaran ekstrakurikuler
        $extracurricularSubjects = [
            ['name' => 'Basket', 'code' => 'BASKET', 'category' => 'extracurricular', 'level_id' => null, 'order' => 1],
            ['name' => 'Sepak Bola', 'code' => 'SEPAKBOLA', 'category' => 'extracurricular', 'level_id' => null, 'order' => 2],
            ['name' => 'Volley', 'code' => 'VOLLEY', 'category' => 'extracurricular', 'level_id' => null, 'order' => 3],
            ['name' => 'Badminton', 'code' => 'BADMINTON', 'category' => 'extracurricular', 'level_id' => null, 'order' => 4],
            ['name' => 'Musik', 'code' => 'MUSIK', 'category' => 'extracurricular', 'level_id' => null, 'order' => 5],
            ['name' => 'Teater', 'code' => 'TEATER', 'category' => 'extracurricular', 'level_id' => null, 'order' => 6],
            ['name' => 'PMR (Palang Merah Remaja)', 'code' => 'PMR', 'category' => 'extracurricular', 'level_id' => null, 'order' => 7],
            ['name' => 'Pramuka', 'code' => 'PRAMUKA', 'category' => 'extracurricular', 'level_id' => null, 'order' => 8],
            ['name' => 'Rohis', 'code' => 'ROHIS', 'category' => 'extracurricular', 'level_id' => null, 'order' => 9],
            ['name' => 'English Club', 'code' => 'ENGLISHCLUB', 'category' => 'extracurricular', 'level_id' => null, 'order' => 10],
        ];

        $allSubjects = array_merge($sdSubjects, $smpSubjects, $smaSubjects, $extracurricularSubjects);

        // Get the first school to assign to subjects
        $school = \App\Models\School::first();

        if (!$school) {
            // If no school exists, create a default one
            $school = \App\Models\School::create([
                'name' => 'Default School',
                'code' => 'DEFAULT-001',
                'address' => 'Default Address',
                'city' => 'Default City',
                'province' => 'Default Province',
                'postal_code' => '00000',
                'phone' => '000-0000',
                'email' => 'default@school.com',
                'website' => 'https://default.school.com',
                'description' => 'Default school for seeding',
                'is_active' => true,
                'subscription_id' => null,
            ]);
        }

        // Get school levels
        $sdLevel = \App\Models\SchoolLevel::where('code', 'SD')->first();
        $smpLevel = \App\Models\SchoolLevel::where('code', 'SMP')->first();
        $smaLevel = \App\Models\SchoolLevel::where('code', 'SMA')->first();

        foreach ($allSubjects as $subject) {
            // Map level_id to actual school level IDs
            if ($subject['level_id'] == 1) {
                $levelId = $sdLevel ? $sdLevel->id : null;
            } elseif ($subject['level_id'] == 2) {
                $levelId = $smpLevel ? $smpLevel->id : null;
            } elseif ($subject['level_id'] == 3) {
                $levelId = $smaLevel ? $smaLevel->id : null;
            } else {
                $levelId = null; // For extracurricular subjects
            }

            // Only create subject if level_id exists or is null (for extracurricular)
            if ($levelId === null && $subject['level_id'] !== null) {
                continue; // Skip if level is required but not found
            }

            Subject::create([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'description' => 'Mata pelajaran ' . $subject['name'],
                'school_id' => $school->id,
                'level_id' => $levelId,
                'category' => $subject['category'],
                'is_active' => true,
                'order' => $subject['order'],
            ]);
        }
    }
}
