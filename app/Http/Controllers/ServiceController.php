<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class ServiceController
 * مسؤول عن إدارة "الخدمات المصغرة" التي يقدمها المستقلون (العامة والجاهزة).
 */
class ServiceController extends Controller
{
    /**
     * عرض صفحة إضافة خدمة جديدة.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * حفظ الخدمة في قاعدة البيانات.
     */
     /**
 * حفظ الخدمة في قاعدة البيانات.
 */
public function store(Request $request)
{
    $validatedData = $this->validateServiceRequest($request);
    $imagePath = null;
    $readyFilePath = null;

    // الحصول على الديسك الافتراضي (سواء كان s3 أو cloud)
    $disk = config('filesystems.default');

    try {
        // 2. رفع صورة الغلاف
        if ($request->hasFile('image')) {
            // أضفنا 'public' لضمان أن الصورة تظهر للجميع على الموقع
            $imagePath = $request->file('image')->store('services/covers/' . date('Y/m'), [
                'disk' => $disk,
                'visibility' => 'public'
            ]);
        }

        // 3. رفع ملف الخدمة الجاهزة
        if ($request->type === 'ready' && $request->hasFile('ready_file')) {
            // هنا نرفع الملف بوضع 'private' (افتراضي) لزيادة الأمان
             $readyFilePath = $request->file('ready_file')->store('services/files/' . date('Y/m'), [
                'disk' => $disk
             ]);
        }

        // 4. الحفظ في قاعدة البيانات
        Service::create([
            'user_id'     => Auth::id(),
            'title'       => strip_tags($validatedData['title']),
            'description' => strip_tags($validatedData['description']),
            'price'       => (float) $validatedData['price'],
            'image'       => $imagePath,
            'type'        => $validatedData['type'],
            'ready_file'  => $readyFilePath,
            'status'      => 'active',
        ]);

        return redirect()->route('freelancer.dashboard')
                         ->with('success', 'تم نشر خدمتك الاحترافية بنجاح!');

    } catch (Exception $processingError) {
        Log::error('Service Store Failure: ' . $processingError->getMessage());

        // حذف الملفات في حالة فشل العملية (Rollback)
        if ($imagePath) { Storage::disk($disk)->delete($imagePath); }
        if ($readyFilePath) { Storage::disk($disk)->delete($readyFilePath); }

        return back()->with('error', 'عذراً، حدث خطأ تقني أثناء حفظ الخدمة.')->withInput();
    }
}

// يمكنك حذف دالة uploadServiceImage القديمة ودمج الرفع داخل store كما فعلت أعلاه لسهولة الإدارة.
    /**
     * عرض صفحة تأكيد الدفع (Checkout).
     */
    public function checkout($id)
    {
        $serviceInstance = Service::with('user')->findOrFail((int)$id);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('services.checkout', ['service' => $serviceInstance]);
    }

    /**
     * معالجة طلب تسليم العمل من قبل المستقل.
     */
    public function requestDelivery(Order $order)
    {
        if (Auth::id() !== (int)$order->seller_id) {
            Log::warning("Unauthorized Delivery Attempt by User ID " . Auth::id() . " for Order ID: {$order->id}");
            return back()->with('error', 'عذراً، لا تملك صلاحية تسليم هذا الطلب.');
        }

        try {
            $order->update(['status' => 'delivered']);
            return back()->with('success', 'تم إرسال طلب تسليم الخدمة للعميل بنجاح.');
        } catch (Exception $updateError) {
            Log::error('Order Status Update Error: ' . $updateError->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث حالة الطلب.');
        }
    }

    /**
     * استلام العميل للخدمة وتحويل الأرباح للمستقل.
     */
    public function completeOrder(Request $request, Order $order)
    {
        if (Auth::id() !== (int)$order->user_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);

                $this->payoutToFreelancer($order);
            });

            return back()->with('success', 'تم استلام الخدمة بنجاح، وتحويل الأرباح لرصيد المستقل المعلق.');
        } catch (Exception $e) {
            Log::error('Service Completion Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إتمام العملية.');
        }
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    private function payoutToFreelancer(Order $order)
    {
        $amount = $order->price;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $order->seller_id],
            ['balance' => 0, 'pending_balance' => 0]
        );

        $wallet->increment('pending_balance', $amount);

        Transaction::create([
            'user_id'         => $order->seller_id,
            'amount'          => $amount,
            'currency'        => 'USD',
            'type'            => 'receive',
            'status'          => 'pending',
            'release_at'      => now()->addDays(7),
            'source_id'       => $order->id,
            'source_type'     => Order::class,
            'details'         => 'أرباح معلقة عن بيع خدمة: ' . strip_tags($order->service_title ?? 'خدمة مصغرة'),
        ]);
    }

    protected function validateServiceRequest(Request $request)
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10|max:5000',
            'price'       => 'required|numeric|min:1|max:99999',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
            'type'        => 'required|in:normal,ready',
            'ready_file'  => 'required_if:type,ready|file|max:20480',
        ]);
    }

     protected function uploadServiceImage(Request $request)
   {
    if ($request->hasFile('image')) {
        // نستخدم config('filesystems.default') عشان يرفع على الديسك اللي متعرف في الـ .env (اللي هو cloud)
        return $request->file('image')->store('services/covers/' . date('Y/m'), config('filesystems.default'));
    }
    return null;
  }
}
