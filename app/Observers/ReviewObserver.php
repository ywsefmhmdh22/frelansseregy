<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\User;

class ReviewObserver
{
    // الكود ده بيشتغل "تلقائياً" أول ما تقييم جديد يتسيف في القاعدة
    public function created(Review $review)
    {
        $freelancer = User::find($review->freelancer_id);

        if ($freelancer) {
            // 1. تحديث إجمالي عدد المشاريع المقيمة
            $freelancer->total_projects_completed = $freelancer->reviews()->count();

            // 2. تحديث عدد المشاريع الممتازة (التي تقييمها 4 أو أكثر)
            $freelancer->excellent_projects_count = $freelancer->reviews()->where('rating', '>=', 4)->count();

            // 3. تحديث متوسط التقييم العام للفريلانسر
            $freelancer->freelancer_rating = $freelancer->reviews()->avg('rating') ?? 0;

            $freelancer->save();
        }
    }
}
