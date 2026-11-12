<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'position',
        'department',
        'avatar',
        'phone',
        'competency_scores',
        'total_points',
        'badge',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'competency_scores' => 'array',
            'total_points' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function classGroups(): BelongsToMany
    {
        return $this->belongsToMany(ClassGroup::class, 'class_group_user')
            ->withPivot(['assigned_at', 'assigned_by']);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withPivot(['enrollment_source', 'status', 'progress_percent', 'last_accessed_at', 'completed_at', 'assigned_by'])
            ->withTimestamps();
    }

    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')
            ->withPivot(['is_completed', 'last_watched_seconds', 'completed_at'])
            ->wherePivot('is_completed', true)
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CourseInvitation::class, 'user_id');
    }

    /**
     * Check if this user has access to a given course
     */
    public function hasAccessToCourse(int|Course $course): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $courseId = $course instanceof Course ? $course->id : $course;

        // Check direct enrollment
        $hasDirect = $this->enrolledCourses()->where('courses.id', $courseId)->exists();
        if ($hasDirect) {
            return true;
        }

        // Check group enrollment
        $courseModel = $course instanceof Course ? $course : Course::find($courseId);
        if ($courseModel && $courseModel->class_group_id) {
            return $this->classGroups()->where('class_groups.id', $courseModel->class_group_id)->exists();
        }

        return false;
    }

    /**
     * Get all courses accessible by this employee (both direct & group assigned)
     */
    public function getAccessibleCourses()
    {
        if ($this->isAdmin()) {
            return Course::with(['classGroup', 'tutor', 'company'])->get();
        }

        $groupCourseIds = Course::whereIn('class_group_id', $this->classGroups()->pluck('class_groups.id'))
            ->pluck('id');

        $directCourseIds = $this->enrolledCourses()->pluck('courses.id');

        $allCourseIds = $groupCourseIds->merge($directCourseIds)->unique();

        return Course::whereIn('id', $allCourseIds)
            ->with(['classGroup', 'tutor', 'company'])
            ->get();
    }

    /**
     * Get radar competency scores formatted for employee spider chart
     */
    public function getFormattedCompetencies(): array
    {
        $defaultCategories = [
            'Kepemimpinan' => 78,
            'Komunikasi' => 84,
            'Pemecahan Masalah' => 90,
            'Keahlian Teknis' => 88,
            'Manajemen Proyek' => 74,
            'Literasi Data & AI' => 82,
        ];

        $scores = $this->competency_scores ?? [];

        $result = [];
        foreach ($defaultCategories as $key => $defaultVal) {
            $result[] = [
                'subject' => $key,
                'score' => isset($scores[$key]) ? (int) $scores[$key] : $defaultVal,
                'fullMark' => 100,
            ];
        }

        return $result;
    }

    /**
     * Get gamification rank, badge and tier progress
     */
    public function getGamificationTierAttribute(): array
    {
        $points = $this->total_points ?? 0;

        if ($points >= 2000) {
            return [
                'level' => 5,
                'name' => 'Eduja Mastermind',
                'badge' => '👑',
                'color' => 'amber',
                'min_points' => 2000,
                'next_points' => null,
                'progress_percent' => 100,
            ];
        }

        if ($points >= 1000) {
            return [
                'level' => 4,
                'name' => 'Expert Specialist',
                'badge' => '💎',
                'color' => 'purple',
                'min_points' => 1000,
                'next_points' => 2000,
                'progress_percent' => round((($points - 1000) / 1000) * 100),
            ];
        }

        if ($points >= 500) {
            return [
                'level' => 3,
                'name' => 'Pro Practitioner',
                'badge' => '🥇',
                'color' => 'indigo',
                'min_points' => 500,
                'next_points' => 1000,
                'progress_percent' => round((($points - 500) / 500) * 100),
            ];
        }

        if ($points >= 200) {
            return [
                'level' => 2,
                'name' => 'Rising Apprentice',
                'badge' => '🥈',
                'color' => 'blue',
                'min_points' => 200,
                'next_points' => 500,
                'progress_percent' => round((($points - 200) / 300) * 100),
            ];
        }

        return [
            'level' => 1,
            'name' => 'Novice Learner',
            'badge' => '🥉',
            'color' => 'slate',
            'min_points' => 0,
            'next_points' => 200,
            'progress_percent' => round(($points / 200) * 100),
        ];
    }
}
