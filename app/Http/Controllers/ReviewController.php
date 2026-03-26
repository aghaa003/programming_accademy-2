<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** GET /api/reviews */
    public function index()
    {
        $reviews = Review::select('academy_reviews.id', 'academy_reviews.rating', 'academy_reviews.review_text',
                'users.id as user_id', 'users.firstName', 'users.lastName', 'users.interest')
            ->join('users', 'academy_reviews.user_id', '=', 'users.id')
            ->orderByDesc('academy_reviews.id')
            ->limit(6)
            ->get();

        return response()->json(['success' => true, 'reviews' => $reviews]);
    }

    /** POST /api/reviews */
    public function store(Request $request)
    {
        // The original PHP used form data with user_id from JS, not session
        $userId     = $request->input('user_id');
        $rating     = (int) $request->input('rating');
        $reviewText = trim($request->input('review_text', ''));

        if (!$userId || $userId < 1) {
            return response()->json(['success' => false, 'message' => 'لم يتم تحديد هوية المستخدم. يرجى تسجيل الدخول.'], 401);
        }

        if ($rating < 1 || $rating > 5) {
            return response()->json(['success' => false, 'message' => 'التقييم غير صالح.'], 400);
        }

        if (empty($reviewText) || strlen($reviewText) > 500) {
            return response()->json(['success' => false, 'message' => 'نص التقييم مطلوب ويجب ألا يتجاوز 500 حرف.'], 400);
        }

        Review::create([
            'user_id'     => $userId,
            'rating'      => $rating,
            'review_text' => $reviewText,
        ]);

        return response()->json(['success' => true, 'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.']);
    }
}
