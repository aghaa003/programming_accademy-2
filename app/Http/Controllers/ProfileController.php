<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /** GET /api/user/status */
    public function status(Request $request)
    {
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return response()->json(['success' => false], 401);
        }

        $user = User::select('id', 'username', 'firstName', 'lastName', 'email', 'avatar_data', 'avatar_mime_type', 'preferred_language')
            ->find($userId);

        if (!$user) {
            return response()->json(['success' => false]);
        }

        $avatar = null;
        if (!empty($user->avatar_data)) {
            $avatar = 'data:' . $user->avatar_mime_type . ';base64,' . base64_encode($user->avatar_data);
        }

        $request->session()->put('language', $user->preferred_language ?? 'ar');

        return response()->json([
            'success' => true,
            'user' => [
                'id'        => $user->id,
                'username'  => $user->username,
                'firstName' => $user->firstName,
                'lastName'  => $user->lastName,
                'email'     => $user->email,
                'avatar'    => $avatar,
                'language'  => $user->preferred_language ?? 'ar',
            ],
        ]);
    }

    /** GET /api/profile */
    public function show(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user = User::with('roles')->find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $avatar = null;
        if (!empty($user->avatar_data)) {
            $avatar = 'data:' . $user->avatar_mime_type . ';base64,' . base64_encode($user->avatar_data);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id'                 => $user->id,
                'username'           => $user->username,
                'firstName'          => $user->firstName,
                'lastName'           => $user->lastName,
                'email'              => $user->email,
                'phone'              => $user->phone,
                'country'            => $user->country,
                'experience'         => $user->experience,
                'goal'               => $user->goal,
                'interest'           => $user->interest,
                'preferred_language' => $user->preferred_language,
                'joinDate'           => $user->joinDate,
                'avatar'             => $avatar,
                'roles'              => $user->roles->pluck('name'),
            ],
        ]);
    }

    /** POST /api/profile */
    public function update(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $allowed = ['firstName', 'lastName', 'email', 'phone', 'country', 'experience', 'goal', 'interest', 'preferred_language'];
        foreach ($allowed as $field) {
            if ($request->has($field)) {
                $user->$field = $request->input($field);
            }
        }

        // Password change — accept both camelCase and snake_case field names
        $newPassword     = $request->input('newPassword', $request->input('new_password', ''));
        $currentPassword = $request->input('currentPassword', $request->input('current_password', ''));
        if (!empty($newPassword)) {
            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json(['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة.'], 400);
            }
            $user->password = Hash::make($newPassword);
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'تم تحديث الملف الشخصي بنجاح.']);
    }

    /** POST /api/upload-avatar */
    public function uploadAvatar(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        if (!$request->hasFile('avatar')) {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $file     = $request->file('avatar');
        $mimeType = $file->getMimeType();
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowed)) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم.'], 400);
        }

        $imageData = file_get_contents($file->getRealPath());

        $user = User::find($userId);
        $user->avatar_data      = $imageData;
        $user->avatar_mime_type = $mimeType;
        $user->save();

        $avatar = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

        return response()->json(['success' => true, 'message' => 'تم تحديث الصورة بنجاح.', 'avatar' => $avatar]);
    }

    /** GET /api/avatar/{userId} */
    public function getAvatar($userId)
    {
        $user = User::select('avatar_data', 'avatar_mime_type')->find($userId);

        if (!$user || empty($user->avatar_data)) {
            return response()->json(['success' => false, 'message' => 'No avatar'], 404);
        }

        return response($user->avatar_data)
            ->header('Content-Type', $user->avatar_mime_type)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /** POST /api/update-language */
    public function updateLanguage(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $lang = $request->input('language', 'ar');
        User::where('id', $userId)->update(['preferred_language' => $lang]);
        $request->session()->put('language', $lang);

        return response()->json(['success' => true]);
    }

    /** POST /api/save-user-preferences */
    public function savePreferences(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        \App\Models\UserPreference::updateOrCreate(
            ['user_id' => $userId],
            [
                'preferred_level'    => $request->input('level', $request->input('preferred_level')),
                'preferred_language' => $request->input('language', $request->input('preferred_language')),
                'goals'              => $request->input('goal', $request->input('goals')),
                'time_commitment'    => $request->input('time_commitment'),
            ]
        );

        return response()->json(['success' => true, 'message' => 'تم حفظ التفضيلات بنجاح.']);
    }

    /** DELETE /api/profile */
    public function destroy(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        DB::transaction(function () use ($userId) {
            // Delete related data
            DB::table('user_lesson_progress')->where('user_id', $userId)->delete();
            DB::table('user_course_progress')->where('user_id', $userId)->delete();
            DB::table('user_challenges')->where('user_id', $userId)->delete();
            DB::table('user_assignments')->where('user_id', $userId)->delete();
            DB::table('platform_bookmarks')->where('user_id', $userId)->delete();
            DB::table('platform_ratings')->where('user_id', $userId)->delete();
            DB::table('user_roles')->where('user_id', $userId)->delete();
            DB::table('user_preferences')->where('user_id', $userId)->delete();
            User::where('id', $userId)->delete();
        });

        $request->session()->flush();

        return response()->json(['success' => true, 'message' => 'تم حذف الحساب بنجاح.']);
    }
}
