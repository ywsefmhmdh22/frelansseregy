<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio; // تم التغيير من Project إلى Portfolio ليتناسب مع جدولك
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    /**
     * عرض صفحة إنشاء عمل جديد في المعرض
     */
    public function create()
    {
        // تأكد أن المسار يطابق مكان ملف الـ Blade الخاص بك
        return view('profile.create');
    }

    /**
     * حفظ العمل الجديد في جدول portfolios في قاعدة البيانات
     */
    public function store(Request $request)
    {
        // 1. التحقق من البيانات (Validation)
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // تم إضافة webp لمرونة أكبر
            'link'        => 'nullable|url',
        ], [
            'title.required' => 'عنوان المشروع ضروري لتمييز عملك.',
            'description.min' => 'الوصف القصير لا يبرز جمال عملك، اكتب المزيد!',
            'image.required' => 'الصورة هي روح المشروع، يرجى رفع واحدة.',
            'image.image'    => 'الملف المرفوع يجب أن يكون صورة.',
            'link.url'       => 'الرابط يجب أن يكون صحيحاً ويبدأ بـ http أو https.',
        ]);

        try {
            // 2. معالجة رفع الصورة وتخزينها في المجلد العام (Public Storage)
            $imagePath = null;
            if ($request->hasFile('image')) {
                // تخزين الصورة في مجلد public/portfolios
                $imagePath = $request->file('image')->store('portfolios', 'public');
            }

            // 3. الحفظ الفعلي في قاعدة البيانات (جدول portfolios)
            // قمنا باستخدام الموديل مباشرة وتمرير الأعمدة التي ذكرتها
            Portfolio::create([
                'user_id'     => Auth::id(),        // ربط العمل بالمستخدم الحالي (id)
                'title'       => $request->title,    // العنوان
                'description' => $request->description, // الوصف
                'image'       => $imagePath,        // مسار الصورة
                'link'        => $request->link,     // الرابط الاختياري
                'category'    => 'General',         // قيمة افتراضية للعمود (يمكنك جعلها متغيرة لاحقاً)
            ]);

            // 4. التوجيه لصفحة المعرض مع رسالة نجاح
            // التعديل في PortfolioController.php
return redirect()->route('profile.portfolio', ['id' => Auth::id()])
                 ->with('success', 'تمت إضافة تحفتك الفنية إلى معرض أعمالك بنجاح!');
        } catch (\Exception $e) {
            // العودة مع إظهار الخطأ الحقيقي وحفظ المدخلات السابقة لعدم مسحها
            return back()->withErrors(['error' => 'حدث خطأ تقني أثناء الحفظ: ' . $e->getMessage()])->withInput();
        }
    }
}
