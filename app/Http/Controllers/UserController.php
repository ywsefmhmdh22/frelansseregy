<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * عرض الملف الشخصي العام للمستقل (الصفحة العالمية)
     */
    public function show($id)
    {
        // بنحمل المستخدم مع كل علاقاته المهمة (أعماله، تقييماته، وعدد مشاريع المكافأة)
        $user = User::with(['portfolios', 'receivedReviews.reviewer'])
                    ->withCount(['receivedReviews as high_rated_count' => function($query) {
                        $query->where('rating', '>=', 4); // بنعد التقييمات اللي أعلى من 4 عشان تحدي المكافأة
                    }])
                    ->findOrFail($id);

        return view('profile.portfolio', compact('user'));
    }

    /**
     * عرض التقييمات فقط
     */
    public function showReviews($id)
    {
        // بنجيب المستخدم مع التقييمات بتاعته والناس اللي قيموه
        $user = User::with(['receivedReviews.reviewer'])->findOrFail($id);

        return view('profile.reviews', compact('user'));
    }

    /**
     * عرض معرض الأعمال (Portfolio)
     */
    public function showPortfolio($id)
    {
        // بنجيب المستخدم مع أعماله المضافة في الـ Portfolio
        // ملاحظة: استبدلت projects بـ portfolios لأننا أضفنا العلاقة دي في موديل User
        $user = User::with('portfolios')->findOrFail($id);

        return view('profile.portfolio', compact('user'));
    }
}
