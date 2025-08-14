<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'status',
        'enrollment_date',
        'graduation_date',
        'notes',
        'admission_number',
        'class_rank',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
        'class_rank' => 'integer',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function submissions()
    {
        return $this->hasManyThrough(Submission::class, Assignment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }

    public function scopeTransferred($query)
    {
        return $query->where('status', 'transferred');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Accessors
    public function getSemesterAttribute()
    {
        $month = $this->enrollment_date->month;
        return $month <= 6 ? 2 : 1; // January-June = Semester 2, July-December = Semester 1
    }

    public function getSchoolYearAttribute()
    {
        return $this->academicYear->year_code;
    }

    public function getClassNameAttribute()
    {
        return $this->class ? $this->class->name : 'Unknown';
    }

    public function getStudentNameAttribute()
    {
        return $this->student ? $this->student->name : 'Unknown';
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsGraduatedAttribute()
    {
        return $this->status === 'graduated';
    }

    // Mutators
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }
}
