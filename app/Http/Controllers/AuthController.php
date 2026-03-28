<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    // 1. عرض صفحة إنشاء حساب جديد
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // 2. معالجة بيانات التسجيل وحفظها
    public function register(Request $request)
    {
        // التأكد من صحة البيانات والسماح بـ admin
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:freelancer,client,admin'],
        ]);

        // تجهيز بيانات المستخدم الأساسية
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ];

        // منطق الأدمن: التوثيق الفوري بناءً على مسميات موديل اليوزر الخاص بك
        if ($request->role === 'admin') {
            $userData['verification_status'] = 'verified'; // القيمة التي تجعل الحساب مقبولاً فوراً
            $userData['is_profile_completed'] = true;     // تخطي مرحلة إكمال الملف
            $userData['email_verified_at'] = now();       // تفعيل البريد الإلكتروني تلقائياً
            $userData['is_banned'] = false;               // التأكد من عدم حظره
        }

        // إنشاء المستخدم في قاعدة البيانات
        $user = User::create($userData);

        // تسجيل الدخول مباشرة بعد إنشاء الحساب
        Auth::login($user);

        // التوجيه بناءً على الرتبة (Role)
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($user->role === 'freelancer') {
            return redirect('/freelancer/dashboard');
        }

        return redirect('/client/dashboard');
    }

    // 3. عرض صفحة تسجيل الدخول
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 4. معالجة تسجيل الدخول
    public function login(Request $request)
    {
        // التأكد من الحقول
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // محاولة تسجيل الدخول
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // التوجيه بناءً على نوع الحساب عند تسجيل الدخول العادي
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }

            if ($user->role === 'freelancer') {
                return redirect()->intended('/freelancer/dashboard');
            }

            return redirect()->intended('/client/dashboard');
        }

        // في حال كانت البيانات خاطئة
        return back()->withErrors([
            'email' => 'البيانات المدخلة غير صحيحة أو لا تتطابق مع سجلاتنا.',
        ])->onlyInput('email');
    }

    // 5. تسجيل الخروج
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
