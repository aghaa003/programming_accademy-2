<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonComment;
use Illuminate\Http\Request;

class LessonCommentController extends Controller
{
    public function store(Request $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);

        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:lesson_comments,id',
        ]);

        $validated['lesson_id'] = $lessonId;
        $validated['course_id'] = $lesson->course_id;
        $validated['user_id'] = auth()->id();

        $comment = LessonComment::create($validated);

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, $lessonId, $commentId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $comment = LessonComment::where('id', $commentId)
            ->where('lesson_id', $lessonId)
            ->firstOrFail();

        // Check ownership
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);

        return response()->json($comment->load('user'));
    }

    public function destroy($lessonId, $commentId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $comment = LessonComment::where('id', $commentId)
            ->where('lesson_id', $lessonId)
            ->firstOrFail();

        // Check ownership
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
