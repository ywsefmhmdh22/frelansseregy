<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            // id العميل اللي فاتح الحساب
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // id المستقل اللي العميل اختاره
            $table->foreignId('freelancer_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('favorites');
    }
};
