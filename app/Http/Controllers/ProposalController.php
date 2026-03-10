<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Project;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function store(Request $request, $projectId)
    {
        // 1. التأكد من صحة البيانات (لاحظ غيرنا amount لـ price لتطابق الفورم)
        $request->validate([
            'price' => 'required|numeric|min:1',
            'duration' => 'required|integer|min:1',
            'description' => 'required|string|min:10', // قللتها لـ 10 حروف للتجربة الأسرع
        ]);

        // 2. التأكد من عدم تكرار العرض
        $exists = Proposal::where('project_id', $projectId)
                          ->where('user_id', auth()->id())
                          ->exists();

        if($exists) {
            return back()->with('error', 'لقد قدمت عرضاً على هذا المشروع بالفعل!');
        }

        // 3. حفظ العرض في الداتابيز
        // ملاحظة: لو اسم العمود في جدولك هو 'amount' سيب السطر اللي تحت زي ما هو
        Proposal::create([
            'project_id'  => $projectId,
            'user_id'     => auth()->id(),
            'price'       => $request->price, // لو العمود في الداتابيز اسمه amount، غير 'price' اللي عالشمال لـ 'amount'
            'duration'    => $request->duration,
            'description' => $request->description,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'تم تقديم عرضك بنجاح!');
    }
}
