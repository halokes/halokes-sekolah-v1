<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentStudent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parent_students';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'student_id',
        'relationship',
        'guardian_type',
        'is_primary',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scopeForParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeRelationship($query, $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    public function scopeGuardianType($query, $guardianType)
    {
        return $query->where('guardian_type', $guardianType);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereHas('parent', function ($parentQuery) use ($keyword) {
                $parentQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhereHas('student', function ($studentQuery) use ($keyword) {
                $studentQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
            })->orWhere('relationship', 'like', '%' . $keyword . '%')
              ->orWhere('guardian_type', 'like', '%' . $keyword . '%')
              ->orWhere('notes', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the relationship name in a more readable format
     */
    public function getRelationshipNameAttribute()
    {
        $relationships = [
            'father' => 'Father',
            'mother' => 'Mother',
            'guardian' => 'Guardian',
            'uncle' => 'Uncle',
            'aunt' => 'Aunt',
            'other' => 'Other'
        ];

        return $relationships[$this->relationship] ?? ucfirst($this->relationship);
    }

    /**
     * Get the guardian type name in a more readable format
     */
    public function getGuardianTypeNameAttribute()
    {
        $types = [
            'biological' => 'Biological',
            'adoptive' => 'Adoptive',
            'foster' => 'Foster',
            'other' => 'Other'
        ];

        return $types[$this->guardian_type] ?? ucfirst($this->guardian_type);
    }

    /**
     * Get the parent's full information
     */
    public function getParentInfoAttribute()
    {
        return $this->parent;
    }

    /**
     * Get the student's full information
     */
    public function getStudentInfoAttribute()
    {
        return $this->student;
    }
}
