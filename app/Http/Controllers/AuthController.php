<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * عرض صفحة إنشاء حساب جديد
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * معالجة بيانات التسجيل وحفظها بحماية قصوى
     * تم تحديث القواعد لتتوافق مع معايير NIST و OWASP الحديثة
     */
    public function register(Request $request)
    {
        // 1. قواعد تحقق صارمة جداً (Strict Validation)
        $request->validate([
            'name'     => ['required', 'string', 'max:255', 'trim'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                // القواعد الأقوى لضمان عدم ظهور ملاحظات أمنية مستقبلاً
                Password::min(12)
                    ->letters()      // يجب أن تحتوي على أحرف
                    ->mixedCase()    // أحرف كبيرة وصغيرة
                    ->numbers()      // أرقام
                    ->symbols()      // رموز خاصة
                    ->uncompromised(), // التأكد أن كلمة المرور لم تسرب عالمياً
            ],
            'role'     => ['required', 'in:freelancer,client'],
        ]);

        // 2. إنشاء المستخدم وتشفير كلمة المرور باستخدام Bcrypt (الافتراضي والآمن في Laravel)
        $user = User::create([
            'name'                => strip_tags($request->name),
            'email'               => $request->email,
            'password'            => Hash::make($request->password), // يستخدم Argon2id أو Bcrypt حسب إعدادات السيرفر
            'role'                => $request->role,
            'verification_status' => 'pending',
            'is_banned'           => false,
        ]);

        // 3. تسجيل الدخول الفوري وتوليد جلسة جديدة
        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * معالجة تسجيل الدخول مع حماية Rate Limiting و Session Fixation
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // مفتاح التحديد بناءً على الإيميل والـ IP لمنع هجمات التخمين
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "محاولات كثيرة خاطئة. يرجى الانتظار $seconds ثانية.",
            ]);
        }

        // محاولة تسجيل الدخول
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            // حماية من هجمات Session Fixation
            $request->session()->regenerate();

            return $this->redirectBasedOnRole(Auth::user(), true);
        }

        // تسجيل محاولة فاشلة
        RateLimiter::hit($throttleKey, 600);

        return back()->withErrors([
            'email' => 'البيانات المدخلة غير صحيحة أو غير مسجلة لدينا.',
        ])->onlyInput('email');
    }

    /**
     * دالة مساعدة للتوجيه الآمن بناءً على الرتبة
     */
    protected function redirectBasedOnRole($user, $intended = false)
    {
        $paths = [
            'admin'      => route('admin.dashboard'),
            'freelancer' => route('freelancer.dashboard'),
            'client'     => route('client.dashboard'),
        ];

        // التوجيه للمسار المطلوب أو الافتراضي بناءً على الدور
        $path = $paths[$user->role] ?? '/';

        return $intended ? redirect()->intended($path) : redirect($path);
    }

    /**
     * تسجيل الخروج الآمن وتدمير الجلسة
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // تدمير بيانات الجلسة بالكامل لضمان الأمان
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
