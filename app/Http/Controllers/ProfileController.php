<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /** GET /api/user/status */
    public function status(Request $request)
    {
        $userId = auth()->id();

        if (! $userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = User::select('id', 'username', 'firstName', 'lastName', 'email', 'avatar_path', 'preferred_language')
            ->find($userId);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $avatar = ! empty($user->avatar_path) ? asset($user->avatar_path) : null;

        $request->session()->put('language', $user->preferred_language ?? 'ar');

        $roles = $user->roles()->pluck('name')->toArray();

        return response()->json([
            'roles' => $roles,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'avatar' => $avatar,
                'language' => $user->preferred_language ?? 'ar',
                'roles' => $roles,
            ],
        ]);
    }

    /** GET /api/profile */
    public function show(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = User::with('roles')->find($userId);
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $avatar = ! empty($user->avatar_path) ? asset($user->avatar_path) : null;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'experience' => $user->experience,
                'goal' => $user->goal,
                'interest' => $user->interest,
                'preferred_language' => $user->preferred_language,
                'joinDate' => $user->joinDate,
                'avatar' => $avatar,
                'imageUrl' => $avatar,
                'roles' => $user->roles->pluck('name'),
                'role' => $user->roles->first()?->name ?? 'user',  // ← add this
            ],
        ]);
    }

    /** POST /api/profile */
    public function update(ProfileUpdateRequest $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check phone uniqueness (exclude current user)
        if ($request->has('phone') && ! empty($request->input('phone'))) {
            $phoneExists = User::where('phone', $request->input('phone'))
                ->where('id', '!=', $userId)
                ->exists();
            if ($phoneExists) {
                return response()->json(['error' => 'رقم الهاتف مستخدم بالفعل من قبل حساب آخر.'], 409);
            }
        }

        // Check email uniqueness (exclude current user)
        if ($request->has('email') && ! empty($request->input('email'))) {
            $emailExists = User::where('email', $request->input('email'))
                ->where('id', '!=', $userId)
                ->exists();
            if ($emailExists) {
                return response()->json(['error' => 'البريد الإلكتروني مستخدم بالفعل من قبل حساب آخر.'], 409);
            }
        }

        $allowed = ['firstName', 'lastName', 'email', 'phone', 'country', 'experience', 'goal', 'interest', 'preferred_language'];
        foreach ($allowed as $field) {
            if ($request->has($field)) {
                $user->$field = $request->input($field);
            }
        }

        // Password change — accept both camelCase and snake_case field names
        $newPassword = $request->input('newPassword', $request->input('new_password', ''));
        $currentPassword = $request->input('currentPassword', $request->input('current_password', ''));
        if (! empty($newPassword)) {
            if (mb_strlen($newPassword) < 6) {
                return response()->json(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.'], 400);
            }
            if (mb_strlen($newPassword) > 72) {
                return response()->json(['error' => 'كلمة المرور يجب أن لا تتجاوز 72 حرفاً.'], 400);
            }
            if (! Hash::check($currentPassword, $user->password)) {
                return response()->json(['error' => 'كلمة المرور الحالية غير صحيحة.'], 400);
            }
            $user->password = Hash::make($newPassword);
        }

        $user->save();

        return response()->json(['message' => 'تم تحديث الملف الشخصي بنجاح.']);
    }

    /** POST /api/upload-avatar */
    public function uploadAvatar(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        if (! $request->hasFile('avatar')) {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $file = $request->file('avatar');

        // N-AUD1: Enforce file size limit (2 MB) — OWASP File Upload CS: "Ensure the uploaded file
        // is not larger than a defined maximum file size."
        if ($file->getSize() > 2 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'حجم الملف يتجاوز 2 ميجابايت.'], 400);
        }

        $mimeType = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (! in_array($mimeType, $allowed)) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم.'], 400);
        }

        $user = User::find($userId);

        // Derive extension from validated mime type (never trust client filename)
        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $ext = $mimeToExt[$mimeType] ?? 'jpg';
        $filename = 'avatar_'.$userId.'_'.uniqid().'.'.$ext;
        $dest = public_path('uploads/avatars');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $file->move($dest, $filename);

        $avatarPath = '/uploads/avatars/'.$filename;
        $oldAvatarPath = $user->avatar_path;

        // Save new path to DB first, then delete old file
        $user->avatar_path = $avatarPath;
        $user->save();

        // Delete old avatar file only after DB write succeeded
        if (! empty($oldAvatarPath)) {
            $oldFile = public_path(ltrim($oldAvatarPath, '/'));
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        return response()->json(['success' => true, 'message' => 'تم تحديث الصورة بنجاح.', 'avatar' => asset($avatarPath)]);
    }

    /** GET /api/avatar/{userId} */
    public function getAvatar($userId)
    {
        $user = User::select('avatar_path')->find($userId);

        if (! $user || empty($user->avatar_path)) {
            return response()->json(['success' => false, 'message' => 'No avatar'], 404);
        }

        $avatarsBase = realpath(public_path('uploads/avatars'));
        $resolved = realpath(public_path(ltrim(str_replace('\\', '/', $user->avatar_path), '/')));

        // Path confinement: ensure resolved path stays within uploads/avatars/
        if (! $avatarsBase || ! $resolved || ! str_starts_with($resolved, $avatarsBase) || ! is_file($resolved)) {
            return response()->json(['success' => false, 'message' => 'No avatar'], 404);
        }

        return response()->file($resolved, ['Cache-Control' => 'public, max-age=3600']);
    }

    /** POST /api/update-language */
    public function updateLanguage(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $lang = $request->input('language', 'ar');
        $allowedLanguages = ['ar', 'en'];
        if (! in_array($lang, $allowedLanguages, true)) {
            $lang = 'ar';
        }
        User::where('id', $userId)->update(['preferred_language' => $lang]);
        $request->session()->put('language', $lang);

        return response()->json(['success' => true]);
    }

    /** POST /api/save-user-preferences */
    public function savePreferences(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        // N35: Validate preference values before storing.
        // Frontend now sends English canonical values for level and language.
        $allowedLevels = ['beginner', 'intermediate', 'advanced', ''];
        $allowedLanguages = ['ar', 'en', ''];

        $level = $request->input('level', $request->input('preferred_level', ''));
        $language = $request->input('language', $request->input('preferred_language', ''));
        $goals = $request->input('goal', $request->input('goals', ''));
        $timeCommitment = $request->input('time_commitment', '');

        if (! in_array($level, $allowedLevels, true)) {
            return response()->json(['success' => false, 'message' => 'مستوى غير صالح.'], 422);
        }
        if (! in_array($language, $allowedLanguages, true)) {
            return response()->json(['success' => false, 'message' => 'لغة غير صالحة.'], 422);
        }
        if (strlen($goals) > 500) {
            return response()->json(['success' => false, 'message' => 'حقل الأهداف طويل جداً.'], 422);
        }
        if (strlen($timeCommitment) > 100) {
            return response()->json(['success' => false, 'message' => 'حقل الوقت المخصص طويل جداً.'], 422);
        }

        // Map English canonical values → Arabic display values for DB storage.
        $levelMap = ['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم', '' => ''];
        $languageMap = ['ar' => 'العربية', 'en' => 'الإنجليزية', '' => ''];

        UserPreference::updateOrCreate(
            ['user_id' => $userId],
            [
                'preferred_level' => $levelMap[$level],
                'preferred_language' => $languageMap[$language],
                'goals' => $goals,
                'time_commitment' => $timeCommitment,
            ]
        );

        return response()->json(['success' => true, 'message' => 'تم حفظ التفضيلات بنجاح.']);
    }

    /** DELETE /api/profile */
    public function destroy(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Collect avatar path before transaction so we can delete the file after
        $avatarFile = null;
        if (! empty($user->avatar_path)) {
            $avatarFile = public_path(ltrim($user->avatar_path, '/'));
        }

        DB::transaction(function () use ($userId, $user) {
            // Delete related data
            DB::table('user_lesson_progress')->where('user_id', $userId)->delete();
            DB::table('user_course_progress')->where('user_id', $userId)->delete();
            DB::table('user_challenges')->where('user_id', $userId)->delete();
            DB::table('challenge_attempts')->where('user_id', $userId)->delete();
            DB::table('user_assignments')->where('user_id', $userId)->delete();
            DB::table('platform_bookmarks')->where('user_id', $userId)->delete();
            DB::table('platform_ratings')->where('user_id', $userId)->delete();
            DB::table('user_roles')->where('user_id', $userId)->delete();
            DB::table('user_preferences')->where('user_id', $userId)->delete();
            DB::table('academy_reviews')->where('user_id', $userId)->delete();
            DB::table('password_resets')->where('email', $user->email)->delete();
            User::where('id', $userId)->delete();
        });

        // Delete avatar file only after DB transaction succeeded
        if ($avatarFile && is_file($avatarFile)) {
            @unlink($avatarFile);
        }

        $request->session()->flush();

        return response()->json(['success' => true, 'message' => 'تم حذف الحساب بنجاح.']);
    }

    /** GET /api/users/{userId} - Public user profile (no auth required) */
    public function publicProfile($userId)
    {
        $user = User::select('id', 'username', 'firstName', 'lastName', 'email', 'avatar_path', 'created_at')
            ->find($userId);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $avatar = ! empty($user->avatar_path) ? asset($user->avatar_path) : null;
        $roles = $user->roles()->pluck('name')->toArray();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'avatar' => $avatar,
                'joinDate' => $user->created_at,
                'roles' => $roles,
            ],
        ]);
    }
}
