<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Jobs\SendEmailVerifyEmailJob;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids;

    protected $table = 'users';


	public $sortable = ['id', 'name', 'email','phone_number', 'status'];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'roles' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(RoleMaster::class, 'role_user', 'user_id', 'role_id');
    }

    public function hasRole($roleCode)
    {
        return $this->roles()->where('role_code', $roleCode)->exists();
    }

    public function hasAnyRole($roleCodes)
    {
        return $this->roles()->whereIn('role_code', $roleCodes)->exists();
    }

    public function printRoles()
    {
        return $this->roles()->pluck('role_name')->implode(', ');
    }

    // Define the one-to-one relationship with UserProfile
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // Student relationships
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function attendances()
    {
        return $this->hasManyThrough(Attendance::class, Enrollment::class, 'student_id', 'enrollment_id');
    }

    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class, 'student_id', 'enrollment_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function parentStudents()
    {
        return $this->hasMany(ParentStudent::class, 'student_id');
    }

    // Teacher relationships
    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function assignedGrades()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    public function createdAssignments()
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function gradedSubmissions()
    {
        return $this->hasMany(Submission::class, 'graded_by');
    }

    // Parent relationships
    public function parentStudentsAsParent()
    {
        return $this->hasMany(ParentStudent::class, 'parent_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'parent_students', 'parent_id', 'student_id');
    }

    // Superintendent/Admin relationships
    public function schools()
    {
        return $this->belongsToMany(School::class, 'user_profiles', 'user_id', 'school_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'sender_id');
    }

    // Helper methods for user roles
    public function isStudent()
    {
        return $this->hasRole('ROLE_STUDENT') || $this->enrollments()->exists();
    }

    public function isTeacher()
    {
        return $this->hasRole('ROLE_TEACHER') || $this->teacherSubjects()->exists();
    }

    public function isParent()
    {
        return $this->hasRole('ROLE_PARENT') || $this->parentStudentsAsParent()->exists();
    }

    public function isSuperintendent()
    {
        return $this->hasRole('ROLE_SUPERINTENDENT');
    }

    public function getStudentClassesAttribute()
    {
        if (!$this->isStudent()) {
            return collect();
        }

        return $this->enrollments()->with('class')->get()->pluck('class');
    }

    public function getTeacherClassesAttribute()
    {
        if (!$this->isTeacher()) {
            return collect();
        }

        return $this->teacherSubjects()->with('class')->get()->pluck('class')->unique();
    }

    public function getParentStudentsAttribute()
    {
        if (!$this->isParent()) {
            return collect();
        }

        return $this->parentStudentsAsParent()->with('student')->get();
    }

    public function getCurrentAcademicYearAttribute()
    {
        return AcademicYear::current()->first();
    }

    public function getCurrentEnrollmentsAttribute()
    {
        if (!$this->isStudent()) {
            return collect();
        }

        return $this->enrollments()->active()
            ->where('academic_year_id', $this->current_academic_year->id ?? null)
            ->get();
    }

    public function listRoles()
    {
        return $this->roles()->pluck('role_name');
    }

    public function sendEmailVerificationNotification()
    {
        SendEmailVerifyEmailJob::dispatch($this);
    }

}
