<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\PlatformBookmark;
use App\Models\PlatformRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlatformController extends Controller
{
    /** GET /api/platforms */
    public function index(Request $request)
    {
        $userId   = auth()->id();
        $category = $request->query('category', 'all');
        $level    = $request->query('level', 'all');
        $language = $request->query('language', 'all');
        $search   = $request->query('search', '');
        $favorites = filter_var($request->query('favorites', false), FILTER_VALIDATE_BOOLEAN);
        $limit  = min(max((int) $request->query('limit', 50), 1), 100);
        $offset = max((int) $request->query('offset', 0), 0);

        $query = DB::table('platforms as p')
            ->select(
                'p.*',
                DB::raw('COALESCE(AVG(pr.rating), 0) as avg_rating'),
                DB::raw('COUNT(DISTINCT pr.id) as rating_count'),
                DB::raw('COUNT(DISTINCT pb.id) as bookmark_count'),
                DB::raw('CASE WHEN pb_user.id IS NOT NULL THEN 1 ELSE 0 END as is_bookmarked'),
                DB::raw('COALESCE(upr.rating, 0) as user_rating'),
                DB::raw('COUNT(CASE WHEN pr.rating = 1 THEN 1 END) as rating_1_count'),
                DB::raw('COUNT(CASE WHEN pr.rating = 2 THEN 1 END) as rating_2_count'),
                DB::raw('COUNT(CASE WHEN pr.rating = 3 THEN 1 END) as rating_3_count'),
                DB::raw('COUNT(CASE WHEN pr.rating = 4 THEN 1 END) as rating_4_count'),
                DB::raw('COUNT(CASE WHEN pr.rating = 5 THEN 1 END) as rating_5_count')
            )
            ->leftJoin('platform_ratings as pr', 'p.id', '=', 'pr.platform_id')
            ->leftJoin('platform_bookmarks as pb', 'p.id', '=', 'pb.platform_id')
            ->leftJoin('platform_bookmarks as pb_user', function ($join) use ($userId) {
                $join->on('p.id', '=', 'pb_user.platform_id')
                     ->where('pb_user.user_id', '=', $userId);
            })
            ->leftJoin('platform_ratings as upr', function ($join) use ($userId) {
                $join->on('p.id', '=', 'upr.platform_id')
                     ->where('upr.user_id', '=', $userId);
            })
            ->where('p.is_active', 1)
            ->groupBy('p.id', 'pb_user.id', 'upr.rating')
            ->orderBy('p.created_at', 'desc')
            ->limit($limit)
            ->offset($offset);

        if ($category !== 'all') $query->where('p.category', $category);
        if ($level !== 'all')    $query->where('p.level', $level);
        if ($language !== 'all') {
            if ($language === 'arabic')  $query->whereIn('p.language', ['arabic', 'both']);
            if ($language === 'english') $query->whereIn('p.language', ['english', 'both']);
        }
        if (!empty($search)) {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(fn($q) => $q->where('p.name', 'like', "%{$escapedSearch}%")->orWhere('p.description', 'like', "%{$escapedSearch}%"));
        }
        if ($favorites && $userId) {
            $query->whereExists(fn($q) => $q->from('platform_bookmarks')->whereColumn('platform_id', 'p.id')->where('user_id', $userId));
        } elseif ($favorites && !$userId) {
            $query->whereRaw('1 = 0');
        }

        $platforms = $query->get()->map(function ($p) {
            $p = (array) $p;
            $p['features']     = json_decode($p['features'] ?? '[]', true) ?? [];
            $p['avg_rating']   = (float) $p['avg_rating'];
            $p['rating']       = (float) $p['rating'];
            $p['is_bookmarked']= (bool) $p['is_bookmarked'];
            $p['user_rating']  = (int) $p['user_rating'];
            return $p;
        });

        // Count total matching records for pagination
        $countQuery = DB::table('platforms as p')
            ->where('p.is_active', 1);
        if ($category !== 'all') $countQuery->where('p.category', $category);
        if ($level !== 'all')    $countQuery->where('p.level', $level);
        if ($language !== 'all') {
            if ($language === 'arabic')  $countQuery->whereIn('p.language', ['arabic', 'both']);
            if ($language === 'english') $countQuery->whereIn('p.language', ['english', 'both']);
        }
        if (!empty($search)) {
            $countQuery->where(fn($q) => $q->where('p.name', 'like', "%{$escapedSearch}%")->orWhere('p.description', 'like', "%{$escapedSearch}%"));
        }
        if ($favorites && $userId) {
            $countQuery->whereExists(fn($q) => $q->from('platform_bookmarks')->whereColumn('platform_id', 'p.id')->where('user_id', $userId));
        } elseif ($favorites && !$userId) {
            $countQuery->whereRaw('1 = 0');
        }
        $total = $countQuery->count();

        return response()->json([
            'platforms'  => $platforms,
            'pagination' => [
                'total'    => $total,
                'limit'    => $limit,
                'offset'   => $offset,
                'has_more' => ($offset + $limit) < $total,
            ],
        ]);
    }

    /** POST /api/toggle-bookmark */
    public function toggleBookmark(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $platformId = $request->input('platform_id');
        if (!$platformId || !\App\Models\Platform::where('id', $platformId)->exists()) {
            return response()->json(['error' => 'المنصة غير موجودة.'], 404);
        }

        $existing = PlatformBookmark::where('user_id', $userId)->where('platform_id', $platformId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false, 'message' => 'تم إزالة الإشارة المرجعية']);
        }

        // insertOrIgnore guards against duplicate inserts from concurrent requests (UNIQUE key is the final guard)
        DB::table('platform_bookmarks')->insertOrIgnore(['user_id' => $userId, 'platform_id' => $platformId]);
        return response()->json(['bookmarked' => true, 'message' => 'تمت إضافة الإشارة المرجعية']);
    }

    /** POST /api/rate-platform */
    public function ratePlatform(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $platformId = $request->input('platform_id');
        $rating     = (int) $request->input('rating');

        if (!$platformId || !\App\Models\Platform::where('id', $platformId)->exists()) {
            return response()->json(['error' => 'المنصة غير موجودة.'], 404);
        }

        if ($rating < 1 || $rating > 5) {
            return response()->json(['success' => false, 'message' => 'Invalid rating'], 400);
        }

        // Atomic: upsert rating and recalculate average in one transaction
        DB::transaction(function () use ($userId, $platformId, $rating) {
            DB::table('platform_ratings')->updateOrInsert(
                ['user_id' => $userId, 'platform_id' => $platformId],
                ['rating' => $rating]
            );
            DB::table('platforms')
                ->where('id', $platformId)
                ->update(['rating' => DB::raw(
                    '(SELECT ROUND(AVG(r.rating), 2) FROM platform_ratings r WHERE r.platform_id = '.(int)$platformId.')'
                )]);
        });

        $avgRating   = Platform::where('id', $platformId)->value('rating');
        $ratingCount = DB::table('platform_ratings')->where('platform_id', $platformId)->count();

        return response()->json([
            'success'      => true,
            'message'      => 'تم حفظ التقييم',
            'rating'       => $rating,
            'avg_rating'   => round($avgRating, 2),
            'rating_count' => $ratingCount,
        ]);
    }

    /** GET /api/platform-stats */
    public function stats(Request $request)
    {
        $stats = DB::table('platforms as p')
            ->select(
                DB::raw('COUNT(DISTINCT p.id) as total_platforms'),
                DB::raw('COUNT(DISTINCT pb.user_id) as total_users_with_bookmarks'),
                DB::raw('COALESCE(AVG(pr.rating), 0) as overall_avg_rating')
            )
            ->leftJoin('platform_bookmarks as pb', 'p.id', '=', 'pb.platform_id')
            ->leftJoin('platform_ratings as pr', 'p.id', '=', 'pr.platform_id')
            ->where('p.is_active', 1)
            ->first();

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /** GET /api/platform-recommendations */
    public function recommendations(Request $request)
    {
        $levelInput    = $request->query('level', '');
        $goalInput     = $request->query('goal', '');
        $languageInput = $request->query('language', '');

        // Map Arabic answers to English
        $levelMap    = ['مبتدئ' => 'beginner', 'متوسط' => 'intermediate', 'متقدم' => 'advanced'];
        $goalMap     = ['تحضير لمقابلات العمل' => 'interviews', 'تحسين المهارات الخوارزمية' => 'algorithms', 'التعلم والممارسة' => 'learning'];
        $languageMap = ['العربية' => 'arabic', 'الإنجليزية' => 'english', 'لا يهم' => 'any'];

        $userLevel    = $levelMap[$levelInput] ?? 'beginner';
        $userGoal     = $goalMap[$goalInput] ?? 'learning';
        $userLanguage = $languageMap[$languageInput] ?? 'any';

        $query = Platform::where('is_active', 1);

        // Language filter
        if ($userLanguage === 'arabic') {
            $query->whereIn('language', ['arabic', 'both']);
        } elseif ($userLanguage === 'english') {
            $query->whereIn('language', ['english', 'both']);
        }

        // Level filter
        if ($userLevel === 'beginner') {
            $query->whereIn('level', ['beginner', 'intermediate']);
        } elseif ($userLevel === 'intermediate') {
            $query->whereIn('level', ['intermediate', 'advanced']);
        } else {
            $query->where('level', 'advanced');
        }

        // Goal-based filtering
        if ($userGoal === 'interviews') {
            $query->where(fn($q) => $q->where('features', 'like', '%مقابلات%')->orWhere('features', 'like', '%interviews%'));
        } elseif ($userGoal === 'algorithms') {
            $query->where(fn($q) => $q->where('features', 'like', '%خوارزميات%')->orWhere('features', 'like', '%algorithms%'));
        }

        $platforms = $query->orderByDesc('rating')->orderByDesc('user_count')->limit(4)->get();

        // If no specific matches, fallback to general
        if ($platforms->isEmpty()) {
            $platforms = Platform::where('is_active', 1)
                ->where('level', $userLevel)
                ->orderByDesc('rating')
                ->limit(4)
                ->get();
        }

        // Format recommendations with match_reason
        $recommendations = $platforms->map(function ($p) use ($userLevel, $userGoal, $userLanguage) {
            $matchReasons = [];

            if ($p->level === $userLevel) {
                $matchReasons[] = 'مستوى مناسب لك';
            }
            if (($userLanguage === 'arabic' && in_array($p->language, ['arabic', 'both'])) ||
                ($userLanguage === 'english' && in_array($p->language, ['english', 'both'])) ||
                $userLanguage === 'any') {
                $matchReasons[] = 'لغة مناسبة';
            }
            $features = is_string($p->features) ? $p->features : json_encode($p->features ?? []);
            if ($userGoal === 'interviews' && str_contains($features, 'مقابلات')) {
                $matchReasons[] = 'مناسب للمقابلات';
            } elseif ($userGoal === 'algorithms' && str_contains($features, 'خوارزميات')) {
                $matchReasons[] = 'مناسب للخوارزميات';
            }

            $arr = $p->toArray();
            $arr['features']     = $p->features ?? [];
            $arr['match_reason'] = !empty($matchReasons) ? implode(' • ', $matchReasons) : 'مناسب لمستواك العام';
            return $arr;
        });

        $explanation = 'بناءً على إجاباتك في الاستبيان';

        return response()->json([
            'success'         => true,
            'recommendations' => $recommendations,
            'actual_level'    => $userLevel,
            'explanation'     => $explanation,
            'personalized'    => !empty($levelInput) || !empty($goalInput) || !empty($languageInput),
        ]);
    }
}
