<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChallengeController extends Controller
{
    public function index()
    {
        $challenges = Challenge::orderBy('id', 'desc')->get();

        return response()->json(['success' => true, 'challenges' => $challenges]);
    }

    public function store(Request $request)
    {
        if (empty(trim($request->input('title', '')))) {
            return response()->json(['success' => false, 'message' => 'عنوان التحدي مطلوب.'], 400);
        }
        if (empty(trim($request->input('description', '')))) {
            return response()->json(['success' => false, 'message' => 'وصف التحدي مطلوب.'], 400);
        }

        $allowedCategories  = ['algorithms', 'data-structures', 'web', 'database'];
        $allowedDifficulties = ['easy', 'medium', 'hard'];
        $category   = $request->input('category');
        $difficulty = $request->input('difficulty');
        $points     = (int) $request->input('points', 0);

        if (!in_array($category, $allowedCategories, true)) {
            return response()->json(['success' => false, 'message' => 'الفئة غير صالحة.'], 422);
        }
        if (!in_array($difficulty, $allowedDifficulties, true)) {
            return response()->json(['success' => false, 'message' => 'مستوى الصعوبة غير صالح.'], 422);
        }
        if ($points < 0) {
            return response()->json(['success' => false, 'message' => 'النقاط لا يمكن أن تكون سالبة.'], 422);
        }

        $challenge = Challenge::create([
            'title'             => $request->input('title'),
            'description'       => $request->input('description'),
            'category'          => $category,
            'difficulty'        => $difficulty,
            'points'            => $points,
            'starter_code'      => $request->input('starter_code'),
            'code_language'     => $request->input('code_language'),
            'test_cases'        => $request->input('test_cases'),
            'solution_template' => $request->input('solution_template'),
            'is_active'         => (bool) $request->input('is_active', true),
        ]);

        return response()->json(['success' => true, 'challenge' => $challenge], 201);
    }

    public function show($id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function update(Request $request, $id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }
        $allowedCategories  = ['algorithms', 'data-structures', 'web', 'database'];
        $allowedDifficulties = ['easy', 'medium', 'hard'];
        $data = $request->only(['title', 'description', 'category', 'difficulty', 'points', 'starter_code', 'code_language', 'test_cases', 'solution_template', 'is_active']);

        // Validate required fields if being changed — same rules as store()
        if (isset($data['title']) && empty(trim($data['title']))) {
            return response()->json(['success' => false, 'message' => 'عنوان التحدي مطلوب.'], 400);
        }
        if (isset($data['description']) && empty(trim($data['description']))) {
            return response()->json(['success' => false, 'message' => 'وصف التحدي مطلوب.'], 400);
        }

        if (isset($data['category']) && !in_array($data['category'], $allowedCategories, true)) {
            return response()->json(['success' => false, 'message' => 'الفئة غير صالحة.'], 422);
        }
        if (isset($data['difficulty']) && !in_array($data['difficulty'], $allowedDifficulties, true)) {
            return response()->json(['success' => false, 'message' => 'مستوى الصعوبة غير صالح.'], 422);
        }
        if (isset($data['points']) && (int)$data['points'] < 0) {
            return response()->json(['success' => false, 'message' => 'النقاط لا يمكن أن تكون سالبة.'], 422);
        }

        $challenge->fill($data);
        $challenge->save();

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function destroy($id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }
        // Delete related rows atomically to avoid partial deletion on failure
        DB::transaction(function () use ($id, $challenge) {
            DB::table('challenge_attempts')->where('challenge_id', $id)->delete();
            DB::table('user_challenges')->where('challenge_id', $id)->delete();
            $challenge->delete();
        });

        return response()->json(['success' => true, 'message' => 'تم حذف التحدي بنجاح']);
    }
}
