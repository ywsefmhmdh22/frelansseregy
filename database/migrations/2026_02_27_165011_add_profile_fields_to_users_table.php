 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // البيانات الأساسية
            $table->string('phone')->nullable();
            $table->string('skills')->nullable(); // المهارات
            $table->text('bio')->nullable(); // النبذة

            // بيانات الموقع والتوثيق
            $table->string('country')->nullable(); // الدولة
            $table->string('city')->nullable();    // المدينة
            $table->string('id_number')->nullable(); // رقم البطاقة/الهوية
            $table->string('id_image')->nullable();  // مسار صورة البطاقة (الوجه)
            $table->string('id_image_back')->nullable(); // مسار صورة البطاقة (الظهر)

            // حالة التوثيق
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('is_profile_completed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'skills', 'bio', 'country', 'city',
                'id_number', 'id_image', 'id_image_back',
                'verification_status', 'is_profile_completed'
            ]);
        });
    }
};
