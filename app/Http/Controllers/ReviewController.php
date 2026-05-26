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

        return response()->json($reviews);
    }

    /** GET /api/courses/{courseId}/reviews */
    public function courseIndex($courseId)
    {
        $reviews = DB::table('course_reviews')
            ->select(
                'course_reviews.id',
                'course_reviews.course_id',
                'course_reviews.user_id',
                'course_reviews.rating',
                'course_reviews.comment',
                'course_reviews.created_at',
                'users.username',
                'users.avatar_path'
            )
            ->join('users', 'users.id', '=', 'course_reviews.user_id')
            ->where('course_reviews.course_id', $courseId)
            ->orderByDesc('course_reviews.created_at')
            ->get()
            ->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'courseId'   => $review->course_id,
                    'userId'     => (string) $review->user_id,
                    'userName'   => $review->username,
                    'userAvatar' => ! empty($review->avatar_path) ? asset($review->avatar_path) : null,
                    'rating'     => (int) $review->rating,
                    'comment'    => $review->comment,
                    'createdAt'  => $review->created_at,
                ];
            });

        return response()->json($reviews);
    }

    /** POST /api/courses/{courseId}/reviews */
    public function storeForCourse($courseId, ReviewRequest $request)
    {
        $userId  = auth()->id();
        $rating  = (int) $request->validated()['rating'];
        $comment = trim($request->validated()['comment'] ?? $request->validated()['review_text']);

        if (DB::table('course_reviews')->where(['course_id' => $courseId, 'user_id' => $userId])->exists()) {
            return response()->json(['error' => 'لقد أرسلت تقييماً لهذا الكورس من قبل.'], 409);
        }

        $now = now();
        $reviewId = DB::table('course_reviews')->insertGetId([
            'course_id'   => $courseId,
            'user_id'     => $userId,
            'rating'      => $rating,
            'comment'     => $comment,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $user = DB::table('users')->select('username', 'avatar_path')->where('id', $userId)->first();

        return response()->json([
            'id'         => $reviewId,
            'courseId'   => (int) $courseId,
            'userId'     => (string) $userId,
            'userName'   => $user->username,
            'userAvatar' => ! empty($user->avatar_path) ? asset($user->avatar_path) : null,
            'rating'     => $rating,
            'comment'    => $comment,
            'createdAt'  => $now,
        ], 201);
    }

    /** POST /api/reviews */
    public function store(ReviewRequest $request)
    {
        $userId     = auth()->id();
        $rating     = (int) $request->validated()['rating'];
        $reviewText = trim($request->validated()['review_text'] ?? $request->validated()['comment']);

        // Prevent duplicate reviews from the same user (PHP-level check)
        if (Review::where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'لقد أرسلت تقييماً من قبل.'], 409);
        }

        // DB-level guard: UNIQUE(user_id) on the table catches any race condition
        $inserted = DB::table('academy_reviews')->insertOrIgnore([
            'user_id' => $userId,
            'rating' => $rating,
            'review_text' => $reviewText,
        ]);

        if (! $inserted) {
            return response()->json(['error' => 'لقد أرسلت تقييماً من قبل.'], 409);
        }

        return response()->json(['success' => true, 'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.']);
    }
}
