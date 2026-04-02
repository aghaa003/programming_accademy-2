<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminChallengeRequest;
use App\Models\Challenge;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChallengeController extends Controller
{
    public function index(Request $request)
    {
        // M4: Paginate admin challenge list
        $limit  = min((int) $request->query('limit', 20), 100);
        $offset = max((int) $request->query('offset', 0), 0);
        $total  = Challenge::count();

        $challenges = Challenge::orderBy('id', 'desc')->skip($offset)->take($limit)->get();

        return response()->json(['success' => true, 'challenges' => $challenges, 'total' => $total]);
    }

    public function store(AdminChallengeRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $challenge = Challenge::create($data);

        AuditLogger::log($request, 'create_challenge', 'Challenge', $challenge->id, ['title' => $challenge->title]);

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

    public function update(AdminChallengeRequest $request, $id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }

        $challenge->fill($request->validated());
        $challenge->save();

        AuditLogger::log($request, 'update_challenge', 'Challenge', $challenge->id, ['title' => $challenge->title]);

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

        AuditLogger::log(request(), 'delete_challenge', 'Challenge', (int) $id, ['title' => $challenge->title]);

        return response()->json(['success' => true, 'message' => 'تم حذف التحدي بنجاح']);
    }
}
