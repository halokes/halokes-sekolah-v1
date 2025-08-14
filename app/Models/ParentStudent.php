<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentStudent extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'parent_id',
        'student_id',
        'relationship',
        'guardian_type',
        'is_primary',
        'notes',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Constants
    const RELATIONSHIP_FATHER = 'father';
    const RELATIONSHIP_MOTHER = 'mother';
    const RELATIONSHIP_GUARDIAN = 'guardian';
    const RELATIONSHIP_GRANDFATHER = 'grandfather';
    const RELATIONSHIP_GRANDMOTHER = 'grandmother';
    const RELATIONSHIP_UNCLE = 'uncle';
    const RELATIONSHIP_AUNT = 'aunt';
    const RELATIONSHIP_BROTHER = 'brother';
    const RELATIONSHIP_SISTER = 'sister';
    const RELATIONSHIP_OTHER = 'other';

    const GUARDIAN_TYPE_BIOLOGICAL = 'biological';
    const GUARDIAN_TYPE_ADOPTIVE = 'adoptive';
    const GUARDIAN_TYPE_FOSTER = 'foster';
    const GUARDIAN_TYPE_OTHER = 'other';

    // Relationships
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
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

    // Accessors
    public function getRelationshipLabelAttribute()
    {
        return self::getRelationships()[$this->relationship] ?? $this->relationship;
    }

    public function getGuardianTypeLabelAttribute()
    {
        return self::getGuardianTypes()[$this->guardian_type] ?? $this->guardian_type;
    }

    public function getIsPrimaryGuardianAttribute()
    {
        return $this->is_primary;
    }

    // Mutators
    public function setRelationshipAttribute($value)
    {
        $this->attributes['relationship'] = strtolower($value);
    }

    public function setGuardianTypeAttribute($value)
    {
        $this->attributes['guardian_type'] = strtolower($value);
    }

    // Helper methods
    public static function getRelationships()
    {
        return [
            self::RELATIONSHIP_FATHER => 'Father',
            self::RELATIONSHIP_MOTHER => 'Mother',
            self::RELATIONSHIP_GUARDIAN => 'Guardian',
            self::RELATIONSHIP_GRANDFATHER => 'Grandfather',
            self::RELATIONSHIP_GRANDMOTHER => 'Grandmother',
            self::RELATIONSHIP_UNCLE => 'Uncle',
            self::RELATIONSHIP_AUNT => 'Aunt',
            self::RELATIONSHIP_BROTHER => 'Brother',
            self::RELATIONSHIP_SISTER => 'Sister',
            self::RELATIONSHIP_OTHER => 'Other',
        ];
    }

    public static function getGuardianTypes()
    {
        return [
            self::GUARDIAN_TYPE_BIOLOGICAL => 'Biological',
            self::GUARDIAN_TYPE_ADOPTIVE => 'Adoptive',
            self::GUARDIAN_TYPE_FOSTER => 'Foster',
            self::GUARDIAN_TYPE_OTHER => 'Other',
        ];
    }

    public function getStudentFullNameAttribute()
    {
        return $this->student ? $this->student->name : 'Unknown Student';
    }

    public function getParentFullNameAttribute()
    {
        return $this->parent ? $this->parent->name : 'Unknown Parent';
    }

    public function getContactInfoAttribute()
    {
        return $this->parent ? [
            'name' => $this->parent->name,
            'email' => $this->parent->email,
            'phone' => $this->parent->profile->phone_number ?? null,
        ] : null;
    }

    // Check if this parent has access to the student's academic information
    public function canViewStudentInfo()
    {
        return $this->parent && $this->student;
    }

    // Get all enrollments this parent can view
    public function viewableEnrollments()
    {
        if (!$this->student) {
            return collect();
        }

        return $this->student->enrollments()->active()->get();
    }

    // Get all classes this parent can view
    public function viewableClasses()
    {
        return $this->viewableEnrollments()->pluck('class')->unique();
    }

    // Get all subjects this parent can view
    public function viewableSubjects()
    {
        $classes = $this->viewableClasses();
        return Subject::whereIn('class_id', $classes->pluck('id'))->get();
    }
}
