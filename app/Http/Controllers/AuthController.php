<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule; // تغيير الاسم لتجنب التعارض
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password; // مهم جداً لاستعادة كلمة السر
use Illuminate\Auth\Events\PasswordReset;
use Exception;

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
     * معالجة بيانات التسجيل.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
                PasswordRule::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
            'role'     => ['required', 'in:freelancer,client,admin'],
        ], [
            'password.regex' => 'كلمة السر ضعيفة! يجب أن تحتوي على أحرف كبيرة، صغيرة، أرقام، ورموز خاصة.',
            'password.uncompromised' => 'كلمة المرور هذه ظهرت في تسريبات بيانات سابقة، يرجى اختيار واحدة أكثر أماناً.',
        ]);

        try {
            $user = User::create([
                'name'                => strip_tags($request->name),
                'email'               => $request->email,
                'password'            => Hash::make($request->password),
                'role'                => $request->role,
                'verification_status' => 'pending',
                'is_banned'           => false,
            ]);

            Auth::login($user);
            $request->session()->regenerate();

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
     * معالجة تسجيل الدخول.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['email' => "تم حظر محاولات الدخول مؤقتاً. يرجى الانتظار $seconds ثانية."]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user(), true);
        }

        RateLimiter::hit($throttleKey, 600);
        return back()->withErrors(['email' => 'خطأ في بيانات الدخول، يرجى التأكد والمحاولة مرة أخرى.'])->onlyInput('email');
    }

    /*
    |--------------------------------------------------------------------------
    | ميزات استعادة كلمة المرور (الجديدة)
    |--------------------------------------------------------------------------
    */

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * توجيه آمن بناءً على الصلاحيات.
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
     * تسجيل الخروج.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
