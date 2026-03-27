<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $identifier = $request->input('identifier', '');
        $password   = $request->input('password', '');

        if (empty($identifier) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'الرجاء إدخال اسم المستخدم وكلمة المرور.'], 400);
        }

        $user = User::where('username', $identifier)->orWhere('email', $identifier)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'بيانات الاعتماد غير صحيحة.'], 401);
        }

        // Load roles
        $roles = $user->roles()->pluck('name')->toArray();

        // Store in session
        $request->session()->put('user_id', $user->id);
        $request->session()->put('roles', $roles);

        // Build avatar
        $avatar = !empty($user->avatar_path) ? asset($user->avatar_path) : null;

        return response()->json([
            'success'   => true,
            'message'   => 'تم تسجيل الدخول بنجاح!',
            'id'        => $user->id,
            'username'  => $user->username,
            'firstName' => $user->firstName,
            'lastName'  => $user->lastName,
            'email'     => $user->email,
            'avatar'    => $avatar,
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->all();

        if (empty($data['firstName']) || empty($data['email']) || empty($data['username']) || empty($data['password'])) {
            return response()->json(['success' => false, 'message' => 'الرجاء ملء جميع الحقول المطلوبة.'], 400);
        }

        $phone = isset($data['phone']) && $data['phone'] !== '' ? trim($data['phone']) : null;

        // Check uniqueness
        $query = User::where('email', $data['email'])->orWhere('username', $data['username']);
        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        if ($query->exists()) {
            return response()->json(['success' => false, 'message' => 'اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف مسجل بالفعل.'], 409);
        }

        $user = User::create([
            'firstName'  => trim($data['firstName']),
            'lastName'   => trim($data['lastName'] ?? ''),
            'email'      => trim($data['email']),
            'phone'      => $phone,
            'username'   => trim($data['username']),
            'password'   => Hash::make($data['password']),
            'country'    => $data['country'] ?? null,
            'experience' => $data['experience'] ?? null,
            'goal'       => $data['goal'] ?? null,
            'interest'   => $data['interest'] ?? null,
        ]);

        // Assign default 'student' role (role_id=1)
        $user->roles()->attach(1);

        return response()->json(['success' => true, 'message' => 'تم التسجيل بنجاح!', 'user_id' => $user->id], 201);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return response()->json(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح.']);
    }

    public function checkAvailability(Request $request)
    {
        $email    = trim($request->input('email', ''));
        $username = trim($request->input('username', ''));
        $phone    = trim($request->input('phone', ''));

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
            if (in_array('email',    $conflicts)) $parts[] = 'البريد الإلكتروني';
            if (in_array('username', $conflicts)) $parts[] = 'اسم المستخدم';
            if (in_array('phone',    $conflicts)) $parts[] = 'رقم الهاتف';
            $message = implode('، ', $parts) . ' مسجل بالفعل. الرجاء اختيار قيمة أخرى.';
            return response()->json(['success' => false, 'message' => $message, 'fields' => $conflicts], 409);
        }

        return response()->json(['success' => true, 'message' => 'البيانات متوفرة. يمكنك المتابعة.']);
    }

    public function forgotPassword(Request $request)
    {
        $email = $request->input('email', '');
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Security: don't reveal whether email exists
            return response()->json(['success' => true, 'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور.']);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addHours(2);

        PasswordReset::updateOrCreate(
            ['email' => $email],
            ['token' => $token, 'expires_at' => $expiresAt]
        );

        // Send password reset email via configured mailer (Gmail SMTP)
        $resetLink = config('app.url') . '/reset-password.html?token=' . $token;
        $subject   = 'إعادة تعيين كلمة المرور - أكاديمية البرمجة';
        $body      = "مرحباً،\n\nلقد طلبت إعادة تعيين كلمة المرور لحسابك في أكاديمية البرمجة.\n\n"
                   . "انقر على الرابط التالي لإعادة تعيين كلمة المرور:\n{$resetLink}\n\n"
                   . "هذا الرابط صالح لمدة ساعتين.\n\nإذا لم تطلب هذا، يرجى تجاهل هذا البريد.\n\n"
                   . "مع خالص التحية،\nفريق أكاديمية البرمجة";

        Mail::raw($body, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject);
        });

        return response()->json(['success' => true, 'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور.']);
    }

    public function resetPassword(Request $request)
    {
        $token    = $request->input('token', '');
        $password = $request->input('password', '');

        if (empty($token) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'بيانات غير مكتملة'], 400);
        }

        if (mb_strlen($password) < 6) {
            return response()->json(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
        }

        $reset = PasswordReset::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$reset) {
            return response()->json(['success' => false, 'message' => 'الرابط غير صالح أو منتهي الصلاحية.'], 400);
        }

        $user = User::where('email', $reset->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $user->password = Hash::make($password);
        $user->save();

        $reset->delete();

        return response()->json(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح.']);
    }
}
