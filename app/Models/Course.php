<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_group_id',
        'tutor_id',
        'company_id',
        'title',
        'slug',
        'short_description',
        'description',
        'level',
        'thumbnail_url',
        'estimated_duration_hours',
        'competency_impact',
        'status',
        'due_date',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'competency_impact' => 'array',
            'estimated_duration_hours' => 'float',
            'due_date' => 'date',
            'points' => 'integer',
        ];
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order', 'asc');
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot(['enrollment_source', 'status', 'progress_percent', 'last_accessed_at', 'completed_at', 'assigned_by'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CourseInvitation::class);
    }
}
