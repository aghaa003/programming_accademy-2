<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonLike;

class LessonLikeController extends Controller
{
    public function toggle($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $userId = auth()->id();

        $like = LessonLike::where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
        } else {
            LessonLike::create([
                'lesson_id' => $lessonId,
                'user_id' => $userId,
            ]);
        }

        $count = $lesson->likes_relation->count();
        $liked = LessonLike::where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->exists();

        return response()->json([
            'count' => $count,
            'liked' => $liked,
        ]);
    }

    public function getLikes($lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $count = $lesson->likes_relation->count();
        $userId = auth()->id();
        $liked = $userId ? LessonLike::where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->exists() : false;

        return response()->json([
            'count' => $count,
            'liked' => $liked,
        ]);
    }
}
