<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class PortfolioController
 * مسؤول عن إدارة "معرض أعمال" المستقلين مع دعم كامل للتخزين السحابي (S3).
 */
class PortfolioController extends Controller
{
    /**
     * عرض صفحة إنشاء عمل جديد في المعرض.
     */
    public function create()
    {
        return view('profile.create');
    }

    /**
     * حفظ العمل الجديد مع رفع الصورة إلى Laravel Cloud (S3).
     */
    public function store(Request $request)
    {
        // 1. التحقق من البيانات (رفع الحد لـ 5 ميجا للتوافق مع السحاب)
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
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
            $imagePath = null;

            // 2. معالجة رفع الصورة إلى Laravel Cloud (S3)
            if ($request->hasFile('image')) {
                // التخزين في مجلد 'portfolios' داخل S3 مع جعل الملف متاحاً للعامة (Public)
                $imagePath = $request->file('image')->store('portfolios', 's3');

                // التأكد من أن الصورة قابلة للعرض فور الرفع
                Storage::disk('s3')->setVisibility($imagePath, 'public');
            }

            // 3. الحفظ في قاعدة البيانات مع تنظيف النصوص
            Portfolio::create([
                'user_id'     => Auth::id(),
                'title'       => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'image'       => $imagePath,
                'link'        => $validated['link'],
                'category'    => 'Creative Work',
            ]);

            // 4. التوجيه لصفحة المعرض
            return redirect()->route('profile.portfolio', ['id' => Auth::id()])
                             ->with('success', 'تمت إضافة تحفتك الفنية إلى معرض أعمالك سحابياً بنجاح!');

        } catch (Exception $e) {
            // تسجيل الخطأ وحذف الصورة من S3 في حال فشل العملية
            Log::error('Cloud Portfolio Store Error: ' . $e->getMessage());

            if (isset($imagePath)) {
                Storage::disk('s3')->delete($imagePath);
            }

            return back()->withErrors(['error' => 'حدث خطأ أثناء الرفع للسحاب، يرجى المحاولة لاحقاً.'])->withInput();
        }
    }

    /**
     * حذف عمل من المعرض وتنظيف المساحة من S3.
     */
    public function destroy(Portfolio $portfolio)
    {
        // حماية الملكية
        if (Auth::id() !== $portfolio->user_id) {
            Log::warning("Unauthorized Portfolio deletion attempt by user ID: " . Auth::id());
            return back()->with('error', 'لا تملك صلاحية حذف هذا العمل.');
        }

        try {
            // مسح الملف من S3 قبل حذف السجل
            if ($portfolio->image) {
                Storage::disk('s3')->delete($portfolio->image);
            }

            $portfolio->delete();

            return back()->with('success', 'تم حذف العمل وتنظيف المساحة السحابية بنجاح.');
        } catch (Exception $e) {
            Log::error('S3 Portfolio Delete Error: ' . $e->getMessage());
            return back()->with('error', 'فشل حذف العمل من السحاب، حاول مرة أخرى.');
        }
    }
}
