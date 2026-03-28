<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /** GET /api/reviews */
    public function index()
    {
        $reviews = Review::select('academy_reviews.id', 'academy_reviews.rating', 'academy_reviews.review_text',
            'users.id as user_id', 'users.username', 'users.interest', 'users.avatar_path')
            ->join('users', 'academy_reviews.user_id', '=', 'users.id')
            ->orderByDesc('academy_reviews.id')
            ->limit(6)
            ->get()
            ->map(function ($review) {
                $review->avatar_url = ! empty($review->avatar_path) ? asset($review->avatar_path) : null;

                return $review;
            });

        return response()->json(['success' => true, 'reviews' => $reviews]);
    }

    /** POST /api/reviews */
    public function store(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $rating = (int) $request->input('rating');
        $reviewText = trim($request->input('review_text', ''));

        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول لإضافة تقييم.'], 401);
        }

        if ($rating < 1 || $rating > 5) {
            return response()->json(['success' => false, 'message' => 'التقييم غير صالح.'], 400);
        }

        if (empty($reviewText) || strlen($reviewText) < 5 || strlen($reviewText) > 500) {
            return response()->json(['success' => false, 'message' => 'نص التقييم مطلوب ويجب أن يكون بين 5 و 500 حرف.'], 400);
        }

        // Prevent duplicate reviews from the same user (PHP-level check)
        if (Review::where('user_id', $userId)->exists()) {
            return response()->json(['success' => false, 'message' => 'لقد أرسلت تقييماً من قبل.'], 409);
        }

        // DB-level guard: UNIQUE(user_id) on the table catches any race condition
        $inserted = DB::table('academy_reviews')->insertOrIgnore([
            'user_id' => $userId,
            'rating' => $rating,
            'review_text' => $reviewText,
        ]);

        if (! $inserted) {
            return response()->json(['success' => false, 'message' => 'لقد أرسلت تقييماً من قبل.'], 409);
        }

        return response()->json(['success' => true, 'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.']);
    }
}
