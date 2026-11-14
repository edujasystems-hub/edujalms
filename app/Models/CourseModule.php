<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($module) {
            if (empty($module->slug)) {
                $module->slug = Str::slug($module->title) . '-' . Str::random(4);
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'course_module_id')->orderBy('sort_order', 'asc');
    }

    public function preTestLesson(): ?Lesson
    {
        return $this->lessons()->where('content_type', 'pre_test')->first();
    }

    public function postTestLesson(): ?Lesson
    {
        return $this->lessons()->where('content_type', 'post_test')->first();
    }
}
