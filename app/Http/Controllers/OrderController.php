<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $buyer = Auth::user();

        // 1. التأكد أن المشتري ليس هو نفسه صاحب الخدمة
        if ($buyer->id === $service->user_id) {
            return back()->with('error', 'لا يمكنك شراء خدمتك الخاصة!');
        }

        // 2. التأكد من وجود رصيد كافٍ
        if ($buyer->wallet->balance < $service->price) {
            return back()->with('error', 'رصيدك غير كافٍ لشراء هذه الخدمة.');
        }

        // 3. البدء في عملية الخصم والإنشاء (Transaction لضمان الأمان)
        DB::transaction(function () use ($buyer, $service) {
            // خصم المبلغ من محفظة المشتري
            $buyer->wallet->decrement('balance', $service->price);

            // إنشاء الطلب
            Order::create([
                'service_id' => $service->id,
                'buyer_id'   => $buyer->id,
                'seller_id'  => $service->user_id,
                'price'      => $service->price,
                'status'     => 'pending',
            ]);

            // هنا الفلوس تم خصمها وحجزها في النظام (لم تذهب للبائع بعد)
        });

        return back()->with('success', 'تم شراء الخدمة بنجاح! يمكنك الآن التواصل مع المستقل من لوحة التحكم.');
    }
}
