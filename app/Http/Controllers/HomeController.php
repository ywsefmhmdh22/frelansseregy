<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Work; // افترضنا أن هذا اسم موديل المشاريع/الأعمال
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 1. حساب عدد المستخدمين
        $usersCount = User::count();

        // 2. حساب عدد الأعمال المنجزة (تأكد من اسم الموديل والجدول عندك)
        $projectsCount = projects::count();

        // 3. حساب إجمالي الاستثمارات (مجموع المبالغ في جدول المعاملات)
        // إذا لم يكن لديك جدول معاملات بعد، يمكنك وضع رقم افتراضي مؤقتاً
        $totalInvestments = 2.5; // أو قم بحسابها ديناميكياً

        return view('welcome', compact('usersCount', 'projectsCount', 'totalInvestments'));
    }
}
