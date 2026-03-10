<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
{
    Schema::create('services', function (Blueprint $header) {
        $header->id();
        $header->foreignId('user_id')->constrained()->onDelete('cascade'); // ربط الخدمة بالمستقل
        $header->string('title');
        $header->text('description');
        $header->decimal('price', 10, 2);
        $header->string('image'); // مسار الصورة
        $header->enum('status', ['active', 'hidden'])->default('active'); // حالة الخدمة
        $header->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
