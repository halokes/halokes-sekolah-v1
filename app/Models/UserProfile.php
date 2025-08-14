<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    // Fillable attributes for mass assignment
    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'country',
        'profile_picture',
        'school_id',
        'employee_id',
        'student_id',
        'nisn',
        'nik',
        'phone_number',
        'emergency_contact',
        'emergency_phone',
        'education_background',
        'work_experience',
        'skills',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    // Define the one-to-one relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getRoleAttribute()
    {
        return $this->user->roles->first()->role_name ?? 'Unknown';
    }

    public function getRoleCodeAttribute()
    {
        return $this->user->roles->first()->role_code ?? 'ROLE_USER';
    }

    public function getIsStudentAttribute()
    {
        return $this->user->isStudent();
    }

    public function getIsTeacherAttribute()
    {
        return $this->user->isTeacher();
    }

    public function getIsParentAttribute()
    {
        return $this->user->isParent();
    }

    public function getIsSuperintendentAttribute()
    {
        return $this->user->isSuperintendent();
    }

    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : 'Not assigned';
    }

    public function getEmployeeIdLabelAttribute()
    {
        return $this->employee_id ? 'Employee ID: ' . $this->employee_id : 'No Employee ID';
    }

    public function getStudentIdLabelAttribute()
    {
        return $this->student_id ? 'Student ID: ' . $this->student_id : 'No Student ID';
    }

    public function getNisnLabelAttribute()
    {
        return $this->nisn ? 'NISN: ' . $this->nisn : 'No NISN';
    }

    // Helper methods
    public function canAccessSchool($schoolId)
    {
        return $this->school_id === $schoolId || $this->user->hasRole('ROLE_ADMIN');
    }

    public function getCurrentSchool()
    {
        return $this->school;
    }

    public function getSchoolsAttribute()
    {
        if ($this->user->hasRole('ROLE_ADMIN')) {
            return School::active()->get();
        }

        return $this->school ? collect([$this->school]) : collect();
    }
}
