<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
     */
    public function register(Request $request)
    {
        // 1. قواعد تحقق صارمة جداً (Password Security 100%)
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'trim'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(10) // زدنا الطول لـ 10 لمزيد من الأمان
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised() // حماية ضد الباسوردات المسربة
            ],
            'role' => ['required', 'in:freelancer,client'], // منع تسجيل الأدمن من الفورم العام (أمنياً)
        ]);

        // 2. تشفير الباسورد باستخدام أفضل الممارسات
        $user = User::create([
            'name' => strip_tags($request->name), // حماية XSS
            'email' => $request->email,
            'password' => Hash::make($request->password), // لارفل تستخدم Bcrypt/Argon2 آلياً
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
     * معالجة تسجيل الدخول مع حماية Brute Force (Rate Limiting)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // مفتاح التحديد بناءً على الإيميل والـ IP
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // 1. حماية ضد محاولات التخمين المتكررة
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "محاولات كثيرة خاطئة. يرجى الانتظار $seconds ثانية.",
            ]);
        }

        // 2. محاولة تسجيل الدخول
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // مسح محاولات الفشل عند النجاح
            RateLimiter::clear($throttleKey);

            // حماية ضد Session Fixation
            $request->session()->regenerate();

            return $this->redirectBasedOnRole(Auth::user(), true);
        }

        // 3. تسجيل فشل المحاولة
        RateLimiter::hit($throttleKey, 600); // منع لمدة 10 دقائق بعد 5 محاولات

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

        // تدمير السيشن تماماً
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
