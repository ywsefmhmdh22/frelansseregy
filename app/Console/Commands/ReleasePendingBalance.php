<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class ReleasePendingBalance extends Command
{
    /**
     * اسم الأمر الذي ستنفذه في الـ Terminal
     */
    protected $signature = 'app:release-pending-balance';

    /**
     * وصف الأمر
     */
    protected $description = 'تحويل الرصيد المعلق إلى رصيد متاح بعد مرور 72 ساعة';

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        // 1. جلب العمليات المعلقة (إيداع) التي مر عليها 72 ساعة أو أكثر
        $pendingTransactions = Transaction::where('status', 'pending')
            ->where('type', 'deposit')
            ->where('created_at', '<=', now()->subHours(72))
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info('لا توجد عمليات معلقة حالياً تحتاج لتحرير.');
            return;
        }

        foreach ($pendingTransactions as $transaction) {
            DB::transaction(function () use ($transaction) {
                // 2. الحصول على المحفظة أو إنشاؤها وزيادة الرصيد
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $transaction->user_id],
                    ['balance' => 0]
                );

                $wallet->increment('balance', $transaction->amount);

                // 3. تحديث حالة المعاملة إلى مكتملة
                $transaction->update(['status' => 'completed']);

                $this->info("Released: {$transaction->amount} to User ID: {$transaction->user_id}");
            });
        }

        $this->info('تمت عملية تحرير الأرصدة بنجاح.');
    }
}
