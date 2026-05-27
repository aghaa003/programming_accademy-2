<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\PasswordResetMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Accept both 'identifier' (legacy) and 'emailAddress' (React) field names
        $identifier = $request->input('emailAddress') ?? $request->input('identifier', '');
        $password = $request->input('password', '');
        $remember = (bool) $request->input('remember', false);

        // Empty check now handled by LoginRequest validation above

        // M7: Brute-force lockout — 10 attempts per identifier+IP per 15 minutes
        $lockKey = 'login_fails_'.sha1($identifier.'|'.$request->ip());
        if (Cache::get($lockKey, 0) >= 10) {
            return response()->json(['error' => 'تم تجاوز الحد المسموح من المحاولات. يرجى المحاولة بعد 15 دقيقة.'], 429);
        }

        $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            Cache::put($lockKey, Cache::get($lockKey, 0) + 1, now()->addMinutes(15));

            return response()->json(['error' => 'بيانات الاعتماد غيرصحيحة.'], 401);
        }

        // Block suspended accounts — same generic message to avoid account enumeration
        if ($user->is_suspended) {
            return response()->json(['error' => 'تم تعليق هذا الحساب. يرجى التواصل مع الإدارة.'], 403);
        }

        // M7: Clear lockout counter on successful login
        Cache::forget($lockKey);

        // Use Sanctum SPA auth — stores user in session via the 'web' guard
        Auth::login($user, $remember);

        // Load roles for the response payload (no longer stored in session)
        $roles = $user->roles()->pluck('name')->toArray();

        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // Build avatar
        $avatar = ! empty($user->avatar_path) ? asset($user->avatar_path) : null;

        $response = response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح!',
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        if ($remember) {
            // 30-day encrypted cookie so the session can be restored automatically
            $response->withCookie(Cookie::make(
                'remember_user',
                Crypt::encryptString((string) $user->id),
                60 * 24 * 30, // minutes
                '/',
                null,
                (bool) config('session.secure'), // HTTPS-only in production
                true   // HttpOnly
            ));
        }

        return $response;
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // RegisterRequest already enforces min:8, max:72, required fields — no duplicate checks needed

        $phone = isset($data['phone']) && $data['phone'] !== '' ? trim($data['phone']) : null;

        // Check uniqueness
        $query = User::where('email', $data['email'])->orWhere('username', $data['username']);
        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        if ($query->exists()) {
            return response()->json(['error' => 'اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف مسجل بالفعل.'], 409);
        }

        try {
            $user = User::create([
                'firstName' => trim($data['firstName']),
                'lastName' => trim($data['lastName'] ?? ''),
                'email' => trim($data['email']),
                'phone' => $phone,
                'username' => trim($data['username']),
                'password' => Hash::make($data['password']),
                'country' => $data['country'] ?? null,
                'experience' => $data['experience'] ?? null,
                'goal' => $data['goal'] ?? null,
                'interest' => $data['interest'] ?? null,
                'joinDate' => now(),
            ]);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                return response()->json(['error' => 'اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف مسجل بالفعل.'], 409);
            }
            throw $e;
        }

        // Assign default 'student' role (role_id=1)
        $user->roles()->attach(1);

        return response()->json(['message' => 'تم التسجيل بنجاح!', 'user_id' => $user->id], 201);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح.'])
            ->withCookie(Cookie::forget('remember_user'));
    }

    public function checkAvailability(Request $request)
    {
        // Accept both 'email' (legacy) and 'emailAddress' (React) field names
        $email = trim($request->input('emailAddress') ?? $request->input('email', ''));
        $username = trim($request->input('username', ''));
        $phone = trim($request->input('phone', ''));

        $conflicts = [];

        if ($email !== '' && User::where('email', $email)->exists()) {
            $conflicts[] = 'email';
        }
        if ($username !== '' && User::where('username', $username)->exists()) {
            $conflicts[] = 'username';
        }
        if ($phone !== '' && User::where('phone', $phone)->exists()) {
            $conflicts[] = 'phone';
        }

        if (count($conflicts) > 0) {
            $parts = [];
            if (in_array('email', $conflicts)) {
                $parts[] = 'البريد الإلكتروني';
            }
            if (in_array('username', $conflicts)) {
                $parts[] = 'اسم المستخدم';
            }
            if (in_array('phone', $conflicts)) {
                $parts[] = 'رقم الهاتف';
            }
            $message = implode('، ', $parts).' مسجل بالفعل. الرجاء اختيار قيمة أخرى.';

            return response()->json(['success' => false, 'message' => $message, 'fields' => $conflicts], 409);
        }

        return response()->json(['success' => true, 'message' => 'البيانات متوفرة. يمكنك المتابعة.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $email = $request->input('email', '');
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Security: don't reveal whether email exists
            return response()->json(['success' => true, 'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور.']);
        }

        // Clean up any expired tokens for this email
        PasswordReset::where('email', $email)->where('expires_at', '<', now())->delete();

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addHours(2);

        PasswordReset::updateOrCreate(
            ['email' => $email],
            ['token' => hash('sha256', $token), 'expires_at' => $expiresAt]
        );

        $resetLink = config('app.url').'/reset-password.html?token='.$token;

        Mail::to($email)->queue(new PasswordResetMail($resetLink));

        return response()->json(['success' => true, 'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور.']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $token = $request->input('token', '');
        $password = $request->input('password', '');

        // ResetPasswordRequest already enforces token and password validation

        $reset = PasswordReset::where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $reset) {
            return response()->json(['success' => false, 'message' => 'الرابط غير صالح أو منتهي الصلاحية.'], 400);
        }

        $user = User::where('email', $reset->email)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $user->password = Hash::make($password);
        $user->save();

        // N-AUD2: Invalidate all existing sessions for this user — OWASP Forgot Password CS:
        // "Ask the user if they want to invalidate all of their existing sessions,
        // or invalidate the sessions automatically."
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $reset->delete();

        return response()->json(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح.']);
    }
}
