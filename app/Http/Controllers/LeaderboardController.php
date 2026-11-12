<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    /**
     * Display leaderboard for employees (scoped to their company/office)
     */
    public function employeeIndex(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $leaderboardData = $this->buildLeaderboard($companyId, $user->id);

        return Inertia::render('Leaderboard', [
            'isAdmin' => false,
            'currentCompany' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'code' => $user->company->code,
            ] : null,
            'companies' => [],
            'selectedCompanyId' => $companyId,
            'leaderboard' => $leaderboardData['rankings'],
            'podium' => $leaderboardData['podium'],
            'currentUserStats' => $leaderboardData['currentUserStats'],
        ]);
    }

    /**
     * Display leaderboard for admins (with office / company switcher)
     */
    public function adminIndex(Request $request): Response
    {
        $user = $request->user();
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        // Selected company from query or default to user company or first company
        $selectedCompanyId = $request->query('company_id');
        if ($selectedCompanyId === 'all') {
            $companyFilter = null;
        } elseif ($selectedCompanyId) {
            $companyFilter = (int) $selectedCompanyId;
        } elseif ($user->company_id) {
            $companyFilter = $user->company_id;
        } else {
            $companyFilter = $companies->first()?->id;
        }

        $leaderboardData = $this->buildLeaderboard($companyFilter, $user->id);

        $currentCompany = $companyFilter ? Company::find($companyFilter) : null;

        return Inertia::render('Leaderboard', [
            'isAdmin' => true,
            'currentCompany' => $currentCompany ? [
                'id' => $currentCompany->id,
                'name' => $currentCompany->name,
                'code' => $currentCompany->code,
            ] : null,
            'companies' => $companies,
            'selectedCompanyId' => $companyFilter ?? 'all',
            'leaderboard' => $leaderboardData['rankings'],
            'podium' => $leaderboardData['podium'],
            'currentUserStats' => $leaderboardData['currentUserStats'],
        ]);
    }

    /**
     * Build ranking list and statistics
     */
    protected function buildLeaderboard(?int $companyId, int $currentUserId): array
    {
        $query = User::query()
            ->where('role', 'employee')
            ->with('company:id,name,code')
            ->withCount([
                'enrolledCourses as completed_courses_count' => function ($q) {
                    $q->where('course_user.status', 'completed');
                },
            ]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $users = $query->orderByDesc('total_points')
            ->orderByDesc('completed_courses_count')
            ->orderBy('name')
            ->get();

        $rankings = [];
        $currentUserStats = null;
        $rank = 1;

        foreach ($users as $index => $u) {
            $tier = $u->gamification_tier;

            $item = [
                'rank' => $rank,
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar' => $u->avatar,
                'position' => $u->position ?? $u->job_title ?? 'Karyawan',
                'department' => $u->department ?? 'Umum',
                'company_name' => $u->company?->name ?? 'Pusat',
                'company_code' => $u->company?->code ?? 'HQ',
                'total_points' => (int) ($u->total_points ?? 0),
                'completed_courses_count' => (int) ($u->completed_courses_count ?? 0),
                'tier' => $tier,
                'badge' => $u->badge ?? $tier['badge'],
                'is_current_user' => $u->id === $currentUserId,
            ];

            if ($u->id === $currentUserId) {
                $currentUserStats = $item;
            }

            $rankings[] = $item;
            $rank++;
        }

        // Podium: Top 3
        $podium = [
            'first' => $rankings[0] ?? null,
            'second' => $rankings[1] ?? null,
            'third' => $rankings[2] ?? null,
        ];

        return [
            'rankings' => $rankings,
            'podium' => $podium,
            'currentUserStats' => $currentUserStats,
        ];
    }
}
