<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlogController extends Controller
{
    private function getDetailedArticles()
    {
        $articles = [
            1 => [
                'title' => 'مستقبل العمل الحر في عصر الذكاء الاصطناعي 2026',
                'author' => 'هاني تقي',
                'image' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?q=80&w=2000',
                'category' => 'تقنية واستراتيجية',
                'content' => "في عام 2026، لم يعد الذكاء الاصطناعي مجرد أداة مساعدة، بل أصبح شريكاً استراتيجياً لكل مستقل على منصة ماهر. التحدي الحقيقي اليوم ليس في كيفية منافسة الآلة، بل في كيفية قيادتها. نحن نرى تحولاً جذرياً في طلبات السوق؛ حيث انتقل التركيز من المهام التقليدية إلى 'هندسة الأوامر' والإشراف الإبداعي.
                <br><br>
                المستقبل يفتح أبوابه للمستقلين الذين يدمجون مهاراتهم البشرية الفريدة مع سرعة المعالجة التقنية. في هذا المقال، نستعرض كيف يمكن لمطوري البرمجيات والمصممين إعادة تموضع أنفسهم لضمان الاستمرارية والربحية العالية في سوق يتغير كل ساعة."
            ],
            2 => [
                'title' => 'كيف تبني براند شخصي لا يقاوم على منصة ماهر',
                'author' => 'إدارة ماهر',
                'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=2000',
                'category' => 'تسويق ذاتي',
                'content' => "السمعة الرقمية هي العملة الجديدة. بناء علامة تجارية شخصية (Personal Brand) على منصة ماهر يتطلب أكثر من مجرد قائمة بالمهارات. الأمر يبدأ من 'قصة النجاح' التي ترويها في ملفك الشخصي.
                <br><br>
                العملاء يبحثون عن الثقة قبل الكفاءة. من خلال هذا الدليل، نعلمك كيف تختار تخصصاً دقيقاً (Niche)، وكيف تستخدم تقييمات العملاء السابقين لبناء 'درع الثقة'. تذكر دائماً: البراند ليس ما تقوله عن نفسك، بل ما يقوله العملاء عنك عندما تغادر الغرفة."
            ],
            3 => [
                'title' => 'أسرار البرمجة النظيفة وسرعة الأداء في تطبيقات Laravel',
                'author' => 'كبير المطورين',
                'image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?q=80&w=2000',
                'category' => 'تطوير برمجيات',
                'content' => "السرعة هي الفارق بين تطبيق ناجح وآخر منسي. في لارافل، البرمجة النظيفة (Clean Code) ليست رفاهية، بل هي ضرورة للتحجيم (Scalability). نحن نركز في منصة ماهر على اتباع نمط الـ SOLID Principles واستخدام الـ Eloquent بذكاء لتجنب مشكلة N+1 query.
                <br><br>
                استخدام الكاش (Caching) وتوزيع المهام عبر الـ Queues هي تقنيات أساسية نطبقها لضمان استجابة النظام في أجزاء من الثانية، حتى تحت ضغط الزيارات العالي."
            ],
            4 => [
                'title' => 'سيكولوجية التسعير: كيف ترفع أجرك وتجذب عملاء الجودة',
                'author' => 'سارة كمال',
                'image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?q=80&w=2000',
                'category' => 'إدارة أعمال',
                'content' => "لماذا يتقاضى مستقل 10 دولارات بينما يتقاضى آخر 100 دولار لنفس المهمة؟ السر يكمن في 'القيمة المدركة'. التسعير ليس مجرد رقم، بل هو رسالة تعكس جودتك.
                <br><br>
                نتناول في هذا البحث كيف يمكنك الانتقال من 'التسعير بالساعة' إلى 'التسعير بناءً على القيمة'. العميل لا يشتري وقتك، بل يشتري حل مشاكله وزيادة أرباحه. تعلم كيف تجعل سعرك المرتفع يبدو استثماراً رابحاً للعميل."
            ]
        ];

        return collect($articles)->map(function ($item, $id) {
            return (object)[
                'id' => $id,
                'title' => $item['title'],
                'user' => (object)['name' => $item['author']],
                'image' => $item['image'],
                'category' => (object)['name' => $item['category']],
                'created_at' => now()->subDays($id),
                'content' => $item['content']
            ];
        });
    }

    public function index()
    {
        $blogs = $this->getDetailedArticles();
        return view('blog', compact('blogs'));
    }

    public function show($id)
    {
        $allArticles = $this->getDetailedArticles();
        $blog = $allArticles->firstWhere('id', $id);

        if (!$blog) {
            return redirect()->route('blog.index');
        }

        return view('blog-details', compact('blog'));
    }
}
