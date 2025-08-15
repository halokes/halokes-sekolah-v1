<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'Users', 'role_code' => 'ROLE_USER'],
            ['role_name' => 'Operator', 'role_code' => 'ROLE_OPERATOR'],
            ['role_name' => 'Supervisor', 'role_code' => 'ROLE_SUPERVISOR'],
            ['role_name' => 'Administrator', 'role_code' => 'ROLE_ADMIN'],
            ['role_name' => 'Superintendent', 'role_code' => 'ROLE_SUPERINTENDENT'],
            ['role_name' => 'Teacher', 'role_code' => 'ROLE_TEACHER'],
            ['role_name' => 'Student', 'role_code' => 'ROLE_STUDENT'],
            ['role_name' => 'Parent', 'role_code' => 'ROLE_PARENT'],
            ['role_name' => 'Staff', 'role_code' => 'ROLE_STAFF'],
        ];

        DB::table('role_master')->insert($roles);
    }
}
