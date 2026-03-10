<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    /**
     * 1. عرض صفحة نموذج السحب (Blade)
     * مسببة الخطأ لأنها كانت ممسوحة من عندك
     */
    public function create()
    {
        return view('withdraw.create');
    }

    /**
     * 2. استقبال البيانات وحفظ الطلب
     * بتطبق شروط الـ 50 جنيه والانتظار حسب العملة
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $currency = $request->currency;
        $amount = $request->amount;

        // التحقق من المدخلات
        $request->validate([
            'amount' => 'required|numeric',
            'method' => 'required|string',
            'details' => 'required|string',
            'currency' => 'required|in:EGP,USD',
        ]);

        // 1. شروط خاصة بالدولار
        if ($currency == 'USD') {
            if ($amount < 10) {
                return back()->with('error', 'أقل مبلغ للسحب بالدولار هو 10$');
            }
            // تحديد وقت الانتظار حسب الطريقة
            $processingTime = ($request->method == 'vodafone_cash') ? '72 ساعة (لتدبير العملة)' : '24 ساعة';
        }
        // 2. شروط خاصة بالجنيه
        else {
            if ($amount < 50) {
                return back()->with('error', 'أقل مبلغ للسحب بالجنيه هو 50 ج.م');
            }
            $processingTime = '24 ساعة';
        }

        // التحقق من الرصيد الكافي في المحفظة
        if (!$user->wallet || $user->wallet->balance < $amount) {
            return back()->with('error', 'عفواً، رصيدك الحالي غير كافٍ لإتمام العملية');
        }

        // 3. حفظ الطلب في قاعدة البيانات
        WithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            // بنخزن الطريقة والعملة في عمود الطريقة لو معندكش عمود للعملة
            'method' => $request->method . " ($currency)",
            'details' => $request->details,
            'status' => 'pending',
        ]);

        // خصم الرصيد من محفظة المستخدم
        $user->wallet->decrement('balance', $amount);

        // العودة للرئيسية مع رسالة نجاح واضحة
        return redirect()->route('home')->with('success', "تم استلام طلب السحب بنجاح. وقت الانتظار المتوقع: $processingTime");
    }
}
