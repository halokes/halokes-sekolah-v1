<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'description',
        'school_id',
        'level_id',
        'category',
        'is_active',
        'order',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function level()
    {
        return $this->belongsTo(SchoolLevel::class, 'level_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
            ->withPivot(['class_id', 'academic_year_id', 'teaching_role', 'notes'])
            ->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'teacher_subjects', 'subject_id', 'class_id')
            ->withPivot(['teacher_id', 'academic_year_id', 'teaching_role', 'notes'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForLevel($query, $levelId)
    {
        return $query->where('level_id', $levelId);
    }

    public function scopeAcademic($query)
    {
        return $query->where('category', 'academic');
    }

    public function scopeExtracurricular($query)
    {
        return $query->where('category', 'extracurricular');
    }

    public function scopeSkill($query)
    {
        return $query->where('category', 'skill');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $levelName = $this->level ? $this->level->name . ' - ' : '';
        return $levelName . $this->name;
    }
}
