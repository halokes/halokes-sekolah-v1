<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'announcements';
    protected $keyType = 'string';
    public $incrementing = false;

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
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'target_audience' => 'array',
        'publish_at' => 'datetime',
        'expire_at' => 'datetime',
        'is_published' => 'boolean',
        'is_sent_to_parents' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany('App\Models\File', 'fileable');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where('publish_at', '<=', now())
                     ->where(function ($query) {
                         $query->whereNull('expire_at')
                               ->orWhere('expire_at', '>=', now());
                     });
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false)
                     ->orWhere('publish_at', '>', now())
                     ->orWhere('expire_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->published();
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expire_at')->where('expire_at', '<', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('publish_at', '>', now())->where('is_published', false);
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

    public function scopeForSender($query, $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAudienceType($query, $audienceType)
    {
        return $query->where('audience_type', $audienceType);
    }

    public function scopeSentToParents($query)
    {
        return $query->where('is_sent_to_parents', true);
    }

    public function scopeNotSentToParents($query)
    {
        return $query->where('is_sent_to_parents', false);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('publish_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('content', 'like', '%' . $keyword . '%')
              ->orWhere('priority', 'like', '%' . $keyword . '%')
              ->orWhere('audience_type', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * Get the priority name in a more readable format
     */
    public function getPriorityNameAttribute()
    {
        $priorities = [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        return $priorities[$this->priority] ?? ucfirst($this->priority);
    }

    /**
     * Get the audience type name in a more readable format
     */
    public function getAudienceTypeNameAttribute()
    {
        $types = [
            'all' => 'All Users',
            'school_level' => 'School Level',
            'class' => 'Specific Class',
            'specific' => 'Specific Users/Roles'
        ];

        return $types[$this->audience_type] ?? ucfirst($this->audience_type);
    }

    /**
     * Check if the announcement is currently active
     */
    public function getIsActiveAttribute()
    {
        return $this->is_published &&
               $this->publish_at <= now() &&
               (!$this->expire_at || $this->expire_at >= now());
    }

    /**
     * Get the formatted publish date
     */
    public function getFormattedPublishDateAttribute()
    {
        return $this->publish_at->format('d M Y H:i');
    }

    /**
     * Get the formatted expire date
     */
    public function getFormattedExpireDateAttribute()
    {
        return $this->expire_at ? $this->expire_at->format('d M Y H:i') : 'Never';
    }

    /**
     * Get the file URL if attachment exists
     */
    public function getFileUrlAttribute()
    {
        if ($this->attachment_path) {
            return asset('storage/' . $this->attachment_path);
        }
        return null;
    }

    /**
     * Check if the announcement has an attachment
     */
    public function getHasAttachmentAttribute()
    {
        return !is_null($this->attachment_path);
    }

    /**
     * Get the sender's information
     */
    public function getSenderInfoAttribute()
    {
        return $this->sender;
    }

    /**
     * Get the school information
     */
    public function getSchoolInfoAttribute()
    {
        return $this->school;
    }

    /**
     * Get the academic year information
     */
    public function getAcademicYearInfoAttribute()
    {
        return $this->academicYear;
    }

    /**
     * Get the class information
     */
    public function getClassInfoAttribute()
    {
        return $this->class;
    }

    /**
     * Get the remaining time until expiration
     */
    public function getTimeRemainingAttribute()
    {
        if (!$this->expire_at || $this->is_expired) {
            return 'N/A';
        }

        $diff = now()->diff($this->expire_at);
        return $diff->days . ' days ' . $diff->h . ' hours';
    }
}
