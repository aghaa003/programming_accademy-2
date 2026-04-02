<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
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
    public function store(ReviewRequest $request)
    {
        $userId     = auth()->id();
        $rating     = (int) $request->validated()['rating'];
        $reviewText = trim($request->validated()['review_text']);

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
