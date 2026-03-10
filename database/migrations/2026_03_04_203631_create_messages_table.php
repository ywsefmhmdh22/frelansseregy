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
     Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

    // خلينا الرسالة nullable عشان لو باعت صورة بس
    $table->text('message')->nullable();

    // ضيفنا الأعمدة دي عشان الصور والريكوردات
    $table->string('type')->default('text'); // text, image, audio, file
    $table->string('file_path')->nullable();

    $table->boolean('is_read')->default(false);
    $table->timestamps();
});

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
