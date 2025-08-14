<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Saas\SubscriptionUser;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a subscription for the school
        $subscription = SubscriptionUser::first();

        $schools = [
            [
                'name' => 'SD Islam Al-Furqan',
                'code' => 'SIA-001',
                'address' => 'Jl. Pendidikan No. 1',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'phone' => '021-1234567',
                'email' => 'info@sisf-alquran.sch.id',
                'website' => 'https://sisf-alquran.sch.id',
                'description' => 'SD Islam Al-Furqan merupakan lembaga pendidikan yang berkomitmen mencetak generasi islami yang berkarakter.',
                'is_active' => true,
                'subscription_id' => $subscription->id ?? null,
            ],
            [
                'name' => 'SMP Negeri 1 Jakarta',
                'code' => 'SMPN1JKT-001',
                'address' => 'Jl. Pemuda No. 100',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'postal_code' => '10150',
                'phone' => '021-7398765',
                'email' => 'info@smpn1jakarta.sch.id',
                'website' => 'https://smpn1jakarta.sch.id',
                'description' => 'SMP Negeri 1 Jakarta sekolah unggulan dengan prestasi akademik dan non-akademik tingkat nasional.',
                'is_active' => true,
                'subscription_id' => $subscription->id ?? null,
            ],
            [
                'name' => 'SMA Al-Azhar 1 Jakarta',
                'code' => 'SMAAA1JKT-001',
                'address' => 'Jl. Teuku Umar No. 50',
                'city' => 'Jakarta Barat',
                'province' => 'DKI Jakarta',
                'postal_code' => '11210',
                'phone' => '021-5467890',
                'email' => 'info@al-azhar1.sch.id',
                'website' => 'https://al-azhar1.sch.id',
                'description' => 'SMA Al-Azhar 1 Jakarta sekolah berbasis keislaman dengan kurikulum internasional.',
                'is_active' => true,
                'subscription_id' => $subscription->id ?? null,
            ],
        ];

        foreach ($schools as $school) {
            $createdSchool = School::create($school);

            // Create academic year for the school
            $currentYear = date('Y');

            // Check if academic year already exists
            $yearCode = $currentYear . '-' . ($currentYear + 1);
            $existingYear = AcademicYear::where('year_code', $yearCode)->first();

            if (!$existingYear) {
                $academicYear = AcademicYear::create([
                    'name' => 'Tahun Ajaran ' . $currentYear . '/' . ($currentYear + 1),
                    'year_code' => $yearCode,
                    'start_date' => date('Y-m-d', strtotime($currentYear . '-07-01')),
                    'end_date' => date('Y-m-d', strtotime(($currentYear + 1) . '-06-30')),
                    'school_id' => $createdSchool->id,
                    'is_active' => true,
                    'is_current' => true,
                    'description' => 'Tahun ajaran aktif untuk ' . $createdSchool->name,
                ]);
            }

            // Create previous academic year
            $previousYear = $currentYear - 1;
            $previousYearCode = $previousYear . '-' . $currentYear;
            $existingPreviousYear = AcademicYear::where('year_code', $previousYearCode)->first();

            if (!$existingPreviousYear) {
                AcademicYear::create([
                    'name' => 'Tahun Ajaran ' . $previousYear . '/' . $currentYear,
                    'year_code' => $previousYearCode,
                    'start_date' => date('Y-m-d', strtotime($previousYear . '-07-01')),
                    'end_date' => date('Y-m-d', strtotime($currentYear . '-06-30')),
                    'school_id' => $createdSchool->id,
                    'is_active' => false,
                    'is_current' => false,
                    'description' => 'Tahun ajaran sebelumnya untuk ' . $createdSchool->name,
                ]);
            }
        }
    }
}
