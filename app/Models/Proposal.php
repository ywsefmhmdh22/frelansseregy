<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    // السماح بتخزين البيانات في هذه الأعمدة
     protected $fillable = ['project_id', 'user_id', 'price', 'duration', 'description', 'status'];

    // علاقة: العرض يخص مشروع واحد
    public function project() {
        return $this->belongsTo(Project::class);
    }

    // علاقة: العرض يخص مستخدم واحد (المستقل)
    public function user() {
        return $this->belongsTo(User::class);
    }
}
