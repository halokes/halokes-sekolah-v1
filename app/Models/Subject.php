<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subjects';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'school_id',
        'level_id',
        'category',
        'is_active',
        'order',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class, 'level_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
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

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%')
              ->orWhere('code', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForLevel($query, $levelId)
    {
        return $query->where('level_id', $levelId);
    }

    /**
     * Get the category name in a more readable format
     */
    public function getCategoryNameAttribute()
    {
        $categories = [
            'academic' => 'Academic',
            'extracurricular' => 'Extracurricular',
            'skill' => 'Skill'
        ];

        return $categories[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get the number of teachers assigned to this subject
     */
    public function getTeacherCountAttribute()
    {
        return $this->teacherSubjects()->distinct('teacher_id')->count('teacher_id');
    }

    /**
     * Get the number of classes this subject is taught in
     */
    public function getClassCountAttribute()
    {
        return $this->teacherSubjects()->distinct('class_id')->count('class_id');
    }

    /**
     * Get the number of assignments for this subject
     */
    public function getAssignmentCountAttribute()
    {
        return $this->assignments()->count();
    }

    /**
     * Get the average grade for this subject
     */
    public function getAverageGradeAttribute()
    {
        $grades = $this->grades()->whereNotNull('score')->pluck('score');
        if ($grades->isEmpty()) {
            return null;
        }

        return round($grades->avg(), 2);
    }

    /**
     * Scope to get subjects by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
