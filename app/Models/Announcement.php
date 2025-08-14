<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_id',
        'sender_id',
        'title',
        'content',
        'priority',
        'audience_type',
        'target_audience',
        'attachment_path',
        'attachment_name',
        'publish_at',
        'expire_at',
        'is_published',
        'is_sent_to_parents',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'publish_at' => 'datetime',
        'expire_at' => 'datetime',
        'is_published' => 'boolean',
        'is_sent_to_parents' => 'boolean',
        'target_audience' => 'array',
    ];

    // Constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const AUDIENCE_ALL = 'all';
    const AUDIENCE_SCHOOL_LEVEL = 'school_level';
    const AUDIENCE_CLASS = 'class';
    const AUDIENCE_SPECIFIC = 'specific';

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeLow($query)
    {
        return $query->where('priority', self::PRIORITY_LOW);
    }

    public function scopeNormal($query)
    {
        return $query->where('priority', self::PRIORITY_NORMAL);
    }

    public function scopeHigh($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeSentToParents($query)
    {
        return $query->where('is_sent_to_parents', true);
    }

    public function scopeNotSentToParents($query)
    {
        return $query->where('is_sent_to_parents', false);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_published', true)
                    ->where('publish_at', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('expire_at')->orWhere('expire_at', '>=', now());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expire_at')
                    ->where('expire_at', '<', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('publish_at', '>', now());
    }

    public function scopeForAudience($query, $audienceType)
    {
        return $query->where('audience_type', $audienceType);
    }

    // Accessors
    public function getPriorityLabelAttribute()
    {
        return self::getPriorities()[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            self::PRIORITY_LOW => 'text-gray-600 bg-gray-100',
            self::PRIORITY_NORMAL => 'text-blue-600 bg-blue-100',
            self::PRIORITY_HIGH => 'text-orange-600 bg-orange-100',
            self::PRIORITY_URGENT => 'text-red-600 bg-red-100',
        ];

        return $colors[$this->priority] ?? $colors[self::PRIORITY_NORMAL];
    }

    public function getAudienceTypeLabelAttribute()
    {
        return self::getAudienceTypes()[$this->audience_type] ?? $this->audience_type;
    }

    public function getIsCurrentAttribute()
    {
        return $this->is_published &&
               $this->publish_at &&
               $this->publish_at->lte(now()) &&
               (!$this->expire_at || $this->expire_at->gte(now()));
    }

    public function getIsExpiredAttribute()
    {
        return $this->expire_at && $this->expire_at->lt(now());
    }

    public function getIsUpcomingAttribute()
    {
        return $this->publish_at && $this->publish_at->gt(now());
    }

    public function getHasAttachmentAttribute()
    {
        return !empty($this->attachment_path) && !empty($this->attachment_name);
    }

    public function getFormattedPublishAtAttribute()
    {
        return $this->publish_at ? $this->publish_at->format('d M Y H:i') : 'Not scheduled';
    }

    public function getFormattedExpireAtAttribute()
    {
        return $this->expire_at ? $this->expire_at->format('d M Y H:i') : 'Never expires';
    }

    public function getExpiryStatusAttribute()
    {
        if (!$this->expire_at) {
            return 'No expiry';
        }

        if ($this->expire_at->isPast()) {
            return 'Expired';
        }

        $daysUntilExpiry = now()->diffInDays($this->expire_at, false);
        return "Expires in {$daysUntilExpiry} days";
    }

    // Mutators
    public function setPriorityAttribute($value)
    {
        $this->attributes['priority'] = strtolower($value);
    }

    public function setAudienceTypeAttribute($value)
    {
        $this->attributes['audience_type'] = strtolower($value);
    }

    public function setPublishAtAttribute($value)
    {
        $this->attributes['publish_at'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function setExpireAtAttribute($value)
    {
        $this->attributes['expire_at'] = $value ? \Carbon\Carbon::parse($value) : null;
    }

    // Helper methods
    public static function getPriorities()
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    public static function getAudienceTypes()
    {
        return [
            self::AUDIENCE_ALL => 'All',
            self::AUDIENCE_SCHOOL_LEVEL => 'School Level',
            self::AUDIENCE_CLASS => 'Specific Class',
            self::AUDIENCE_SPECIFIC => 'Specific Users',
        ];
    }

    // Get target audience based on audience type
    public function getTargetAudienceList()
    {
        if ($this->audience_type === self::AUDIENCE_SPECIFIC && $this->target_audience) {
            return $this->target_audience;
        }

        return [];
    }

    // Check if announcement is visible to specific user
    public function isVisibleToUser(User $user)
    {
        if (!$this->is_current) {
            return false;
        }

        // School-level check
        if ($this->school_id && $user->profile->school_id !== $this->school_id) {
            return false;
        }

        // Academic year check
        if ($this->academic_year_id) {
            // Check if user is related to this academic year
            $hasRelatedEnrollment = false;
            if ($user->hasRole('ROLE_STUDENT')) {
                $hasRelatedEnrollment = $user->enrollments()
                    ->where('academic_year_id', $this->academic_year_id)
                    ->exists();
            } elseif ($user->hasRole('ROLE_TEACHER')) {
                $hasRelatedEnrollment = $user->teacherSubjects()
                    ->where('academic_year_id', $this->academic_year_id)
                    ->exists();
            }

            if (!$hasRelatedEnrollment) {
                return false;
            }
        }

        // Class-specific check
        if ($this->class_id) {
            $hasRelatedClass = false;
            if ($user->hasRole('ROLE_STUDENT')) {
                $hasRelatedClass = $user->enrollments()
                    ->where('class_id', $this->class_id)
                    ->exists();
            } elseif ($user->hasRole('ROLE_TEACHER')) {
                $hasRelatedClass = $user->teacherSubjects()
                    ->where('class_id', $this->class_id)
                    ->exists();
            }

            if (!$hasRelatedClass) {
                return false;
            }
        }

        // Specific audience check
        if ($this->audience_type === self::AUDIENCE_SPECIFIC && $this->target_audience) {
            return in_array($user->id, $this->target_audience);
        }

        return true;
    }

    // Get the URL for the attachment
    public function getAttachmentUrlAttribute()
    {
        if (!$this->has_attachment) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }

    // Mark as sent to parents
    public function markAsSentToParents()
    {
        $this->update(['is_sent_to_parents' => true]);
    }

    // Get announcements that should be sent to parents
    public static function getAnnouncementsToSendToParents()
    {
        return self::current()
            ->where('is_sent_to_parents', false)
            ->where(function ($query) {
                $query->where('audience_type', self::AUDIENCE_ALL)
                      ->orWhere('audience_type', self::AUDIENCE_SCHOOL_LEVEL)
                      ->orWhere('audience_type', self::AUDIENCE_CLASS);
            })
            ->get();
    }
}
