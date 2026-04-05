<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // استدعاء مباشر للقواعد
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
     * تم تحديث قواعد الباسورد لتكون الأقوى برمجياً
     */
    public function register(Request $request)
    {
        // 1. قواعد تحقق (Strict Password Validation) - حل مشكلة الـ MEDIUM issue
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12) // رفعنا الحد لـ 12 حرف لضمان أعلى درجة أمان في التقارير
                    ->letters()    // حروف
                    ->mixedCase()  // حروف كبيرة وصغيرة
                    ->numbers()    // أرقام
                    ->symbols()    // رموز خاصة
                    ->uncompromised(), // التأكد أن الباسورد لم تسرب من قبل
            ],
            'role' => ['required', 'in:freelancer,client'],
        ]);

        // 2. إنشاء المستخدم وتشفير الباسورد
        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'verification_status' => 'pending',
            'is_banned' => false,
        ]);

        // 3. تسجيل الدخول والتوجيه
        Auth::login($user);

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
     * معالجة تسجيل الدخول مع حماية Rate Limiting
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // مفتاح التحديد بناءً على الإيميل والـ IP
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "محاولات كثيرة خاطئة. يرجى الانتظار $seconds ثانية.",
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate(); // حماية Session Fixation

            return $this->redirectBasedOnRole(Auth::user(), true);
        }

        RateLimiter::hit($throttleKey, 600);

        return back()->withErrors([
            'email' => 'البيانات المدخلة غير صحيحة.',
        ])->onlyInput('email');
    }

    /**
     * دالة مساعدة للتوجيه الآمن بناءً على الرتبة
     */
    protected function redirectBasedOnRole($user, $intended = false)
    {
        $paths = [
            'admin'      => '/admin/dashboard',
            'freelancer' => '/freelancer/dashboard',
            'client'     => '/client/dashboard',
        ];

        $path = $paths[$user->role] ?? '/';

        return $intended ? redirect()->intended($path) : redirect($path);
    }

    /**
     * تسجيل الخروج الآمن
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
