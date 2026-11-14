<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'course_module_id',
        'title',
        'slug',
        'description',
        'sort_order',
        'duration_minutes',
        'content_type',
        'video_url',
        'audio_url',
        'content_text',
        'pdf_url',
        'attachments',
        'is_preview',
        'due_date',
        'pre_test',
        'post_test',
        'quiz_questions',
        'passing_score',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_preview' => 'boolean',
            'sort_order' => 'integer',
            'duration_minutes' => 'integer',
            'due_date' => 'date',
            'pre_test' => 'array',
            'post_test' => 'array',
            'quiz_questions' => 'array',
            'passing_score' => 'integer',
            'points' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function userProgress(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_user')
            ->withPivot(['is_completed', 'last_watched_seconds', 'completed_at'])
            ->withTimestamps();
    }
}
