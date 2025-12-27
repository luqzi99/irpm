<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * List all users (for admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount(['classes', 'evaluations', 'teachingSchedules']);
        
        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }
        
        // Filter by subscription
        if ($request->subscription) {
            $query->where('subscription_plan', $request->subscription);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($users);
    }

    /**
     * Get single user details
     */
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['classes', 'evaluations', 'teachingSchedules']);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'subscription_plan' => $user->subscription_plan,
            'subscription_label' => $user->getSubscriptionLabel(),
            'subscription_expires_at' => $user->subscription_expires_at,
            'is_active' => $user->is_active,
            'classes_count' => $user->classes_count,
            'evaluations_count' => $user->evaluations_count,
            'teaching_schedules_count' => $user->teaching_schedules_count,
            'limits' => $user->getPlanLimits(),
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Update user subscription
     */
    public function updateSubscription(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'subscription_plan' => 'required|in:free,basic,pro',
            'months' => 'nullable|integer|min:1|max:12',
        ]);

        $oldPlan = $user->subscription_plan;
        $user->subscription_plan = $request->subscription_plan;
        
        // Set expiration
        if ($request->subscription_plan !== 'free' && $request->months) {
            $user->subscription_expires_at = now()->addMonths($request->months);
        } elseif ($request->subscription_plan === 'free') {
            $user->subscription_expires_at = null;
        }
        
        $user->save();

        // Audit log
        AuditLog::log(
            $request->user()->id,
            'subscription_change',
            'User',
            $user->id,
            "Changed from {$oldPlan} to {$request->subscription_plan}"
        );

        return response()->json([
            'message' => 'Langganan berjaya dikemas kini.',
            'user' => [
                'id' => $user->id,
                'subscription_plan' => $user->subscription_plan,
                'subscription_label' => $user->getSubscriptionLabel(),
                'subscription_expires_at' => $user->subscription_expires_at,
            ],
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(Request $request, User $user): JsonResponse
    {
        // Don't allow deactivating self
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak boleh nyahaktifkan akaun sendiri.',
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        AuditLog::log(
            $request->user()->id,
            $user->is_active ? 'user_activated' : 'user_deactivated',
            'User',
            $user->id
        );

        return response()->json([
            'message' => $user->is_active ? 'Pengguna diaktifkan.' : 'Pengguna dinyahaktifkan.',
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Get subscription stats for admin dashboard
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'by_subscription' => [
                'free' => User::where('subscription_plan', 'free')->count(),
                'basic' => User::where('subscription_plan', 'basic')->count(),
                'pro' => User::where('subscription_plan', 'pro')->count(),
            ],
            'by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'guru' => User::where('role', 'guru')->count(),
            ],
            'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'new_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
            
            // Evaluation stats
            'total_evaluations' => \App\Models\Evaluation::count(),
            'evaluations_this_week' => \App\Models\Evaluation::where('created_at', '>=', now()->startOfWeek())->count(),
            'evaluations_this_month' => \App\Models\Evaluation::where('created_at', '>=', now()->startOfMonth())->count(),
            
            // Class stats
            'total_classes' => \App\Models\ClassRoom::count(),
            'total_students' => \App\Models\Student::count(),
            
            // Top subjects
            'top_subjects' => \App\Models\Evaluation::selectRaw('subject_id, count(*) as count')
                ->with('subject:id,name')
                ->groupBy('subject_id')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'name' => $p->subject?->name ?? 'Unknown',
                    'count' => $p->count,
                ]),
            
            // Monthly growth (last 6 months)
            'monthly_growth' => $this->getMonthlyGrowth(),
        ];

        return response()->json($stats);
    }

    /**
     * Get monthly user growth for last 6 months
     */
    private function getMonthlyGrowth(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'evaluations' => \App\Models\Evaluation::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $months;
    }
}
