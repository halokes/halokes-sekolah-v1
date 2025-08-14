<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'id' => Str::uuid(),
                'name' => 'SD (Sekolah Dasar)',
                'code' => 'SD',
                'description' => 'Tingkat Sekolah Dasar untuk jenjang pendidikan dasar',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'SMP (Sekolah Menengah Pertama)',
                'code' => 'SMP',
                'description' => 'Tingkat Sekolah Menengah Pertama untuk jenjang pendidikan menengah pertama',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'SMA (Sekolah Menengah Atas)',
                'code' => 'SMA',
                'description' => 'Tingkat Sekolah Menengah Atas untuk jenjang pendidikan menengah atas',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        DB::table('school_levels')->insert($levels);
    }
}
