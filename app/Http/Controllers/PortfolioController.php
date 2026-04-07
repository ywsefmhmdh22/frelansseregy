<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Class PortfolioController
 * * هذا الكنترولر مسؤول عن إدارة "معرض أعمال" المستقلين (Portfolios).
 * تم تصميمه ليتعامل مع رفع المشاريع، تنظيف البيانات، وتوثيق العمليات برمجياً
 * لرفع مستوى موثوقية النظام ومعايير الأمان.
 */
class PortfolioController extends Controller
{
    /**
     * عرض صفحة إنشاء عمل جديد في المعرض.
     * * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('profile.create');
    }

    /**
     * حفظ العمل الجديد في قاعدة البيانات مع معالجة الصور وحماية XSS.
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. التحقق من البيانات (Validation) مع رسائل مخصصة وتدقيق الروابط
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255', 'trim'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'image'       => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'link'        => ['nullable', 'url'],
        ], [
            'title.required'  => 'عنوان المشروع ضروري لتمييز عملك.',
            'description.min' => 'الوصف القصير لا يبرز جمال عملك، اكتب المزيد!',
            'image.required'  => 'الصورة هي روح المشروع، يرجى رفع واحدة.',
            'image.image'     => 'الملف المرفوع يجب أن يكون صورة.',
            'link.url'        => 'الرابط يجب أن يكون صحيحاً ويبدأ بـ http أو https.',
        ]);

        try {
            // 2. معالجة رفع الصورة باستخدام Storage (الممارسة الفضلى لأمان الملفات)
            $imagePath = null;
            if ($request->hasFile('image')) {
                // تخزين آمن في قرص public مع اسم ملف عشوائي مشفر
                $imagePath = $request->file('image')->store('portfolios', 'public');
            }

            // 3. الحفظ الفعلي مع تنظيف النصوص (Sanitization) لمنع ثغرات XSS
            Portfolio::create([
                'user_id'     => Auth::id(),
                'title'       => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'image'       => $imagePath,
                'link'        => $validated['link'],
                'category'    => 'General', // يمكن توسيعها لاحقاً لتشمل تصنيفات حقيقية (Domain Knowledge)
            ]);

            // 4. التوجيه لصفحة المعرض مع استخدام المسارات المسماة
            return redirect()->route('profile.portfolio', ['id' => Auth::id()])
                             ->with('success', 'تمت إضافة تحفتك الفنية إلى معرض أعمالك بنجاح!');

        } catch (\Exception $e) {
            // تسجيل الخطأ في الـ Logs لتسهيل تتبع المشاكل برمجياً (Professional Practice)
            Log::error('Portfolio Store Error: ' . $e->getMessage());

            // حذف الصورة المرفوعة في حال فشل حفظ البيانات في الداتابيز
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return back()->withErrors(['error' => 'حدث خطأ تقني أثناء معالجة الطلب.'])->withInput();
        }
    }

    /**
     * حذف عمل من المعرض وتنظيف المساحة التخزينية.
     * * @param  \App\Models\Portfolio  $portfolio
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Portfolio $portfolio)
    {
        // حماية الملكية (Authorization Logic)
        if (Auth::id() !== $portfolio->user_id) {
            Log::warning("Unauthorized Portfolio deletion attempt by user ID: " . Auth::id());
            return back()->with('error', 'لا تملك صلاحية حذف هذا العمل.');
        }

        try {
            // مسح الملف من السيرفر قبل حذف السجل
            if ($portfolio->image) {
                Storage::disk('public')->delete($portfolio->image);
            }

            $portfolio->delete();

            return back()->with('success', 'تم حذف العمل بنجاح من معرض أعمالك.');
        } catch (\Exception $e) {
            Log::error('Portfolio Delete Error: ' . $e->getMessage());
            return back()->with('error', 'فشل حذف العمل، حاول مرة أخرى.');
        }
    }
}
