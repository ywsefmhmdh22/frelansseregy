<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class AuthController
 * نظام مصادقة متطور يعتمد معايير NIST و OWASP للأمن السيبراني.
 * يتضمن حماية ضد Brute Force، Session Fixation، وكلمات المرور الضعيفة.
 */
class AuthController extends Controller
{
    /**
     * عرض صفحة إنشاء حساب جديد.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * معالجة بيانات التسجيل بحماية "عسكرية" ضد التخمين.
     * تم دمج الـ Regex المعقد مع Laravel Password Rule لضمان أعلى Entropy.
     */
    public function register(Request $request)
    {
        // 1. قواعد تحقق صارمة جداً (Strict Validation)
        // تم حذف 'trim' من حقل name لأنها ليست قاعدة تحقق وتسبب خطأ، ولارافيل يقوم بالـ trim تلقائياً عبر Middleware
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                'min:12',
                // حل المشكلة الجذري: الـ Regex الذي يفرض (كبير، صغير، رقم، رمز خاص)
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
                // ميزات لارافيل الإضافية للأمان القصوى
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), // حماية ضد كلمات المرور المسربة في الاختراقات العالمية
            ],
            'role'     => ['required', 'in:freelancer,client,admin'],
        ], [
            'password.regex' => 'كلمة السر ضعيفة! يجب أن تحتوي على أحرف كبيرة، صغيرة، أرقام، ورموز خاصة.',
            'password.uncompromised' => 'كلمة المرور هذه ظهرت في تسريبات بيانات سابقة، يرجى اختيار واحدة أكثر أماناً.',
        ]);

        try {
            // 2. إنشاء المستخدم وتشفير كلمة المرور
            $user = User::create([
                'name'                => strip_tags($request->name),
                'email'               => $request->email,
                'password'            => Hash::make($request->password),
                'role'                => $request->role,
                'verification_status' => 'pending',
                'is_banned'           => false,
            ]);

            // 3. تأمين الجلسة فوراً
            Auth::login($user);
            $request->session()->regenerate(); // منع Session Fixation

            return $this->redirectBasedOnRole($user);

        } catch (Exception $e) {
            Log::error("Registration Security Failure: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني أثناء إنشاء الحساب.')->withInput();
        }
    }

    /**
     * عرض صفحة تسجيل الدخول.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * معالجة تسجيل الدخول مع حماية متقدمة (Rate Limiting).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // تحديد الهوية لمحاصرة هجمات Brute Force
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "تم حظر محاولات الدخول مؤقتاً. يرجى الانتظار $seconds ثانية.",
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // تنظيف سجل المحاولات عند النجاح
            RateLimiter::clear($throttleKey);

            // تأمين الجلسة الجديدة
            $request->session()->regenerate();

            return $this->redirectBasedOnRole(Auth::user(), true);
        }

        // تسجيل محاولة فاشلة وزيادة العداد
        RateLimiter::hit($throttleKey, 600); // حظر 10 دقائق بعد الفشل المتكرر

        return back()->withErrors([
            'email' => 'خطأ في بيانات الدخول، يرجى التأكد والمحاولة مرة أخرى.',
        ])->onlyInput('email');
    }

    /**
     * توجيه آمن بناءً على صلاحيات المستخدم (Role-Based Access Control).
     */
    protected function redirectBasedOnRole($user, $intended = false)
    {
        $redirectPaths = [
            'admin'      => route('admin.dashboard'),
            'freelancer' => route('freelancer.dashboard'),
            'client'     => route('client.dashboard'),
        ];

        $target = $redirectPaths[$user->role] ?? '/';

        return $intended ? redirect()->intended($target) : redirect($target);
    }

    /**
     * تسجيل الخروج وتطهير الجلسة بالكامل (Total Session Purge).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // تدمير الجلسة الحالية وتوليد Token جديد لمنع أي استغلال لاحق
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
