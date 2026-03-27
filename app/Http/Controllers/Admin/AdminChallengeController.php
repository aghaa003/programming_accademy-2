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
        $challenge = Challenge::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'difficulty' => $request->input('difficulty'),
            'points' => (int) $request->input('points', 0),
            'starter_code' => $request->input('starter_code'),
            'code_language' => $request->input('code_language'),
            'test_cases' => $request->input('test_cases'),
            'solution_template' => $request->input('solution_template'),
            'is_active' => (bool) $request->input('is_active', true),
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
        $challenge->fill($request->only(['title', 'description', 'category', 'difficulty', 'points', 'starter_code', 'code_language', 'test_cases', 'solution_template', 'is_active']));
        $challenge->save();

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function destroy($id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }
        // Delete related rows first to avoid FK constraint violations
        DB::table('challenge_attempts')->where('challenge_id', $id)->delete();
        DB::table('user_challenges')->where('challenge_id', $id)->delete();
        $challenge->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف التحدي بنجاح']);
    }
}
