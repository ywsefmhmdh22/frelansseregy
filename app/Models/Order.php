<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['service_id', 'buyer_id', 'seller_id', 'price', 'status'];

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function buyer() {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller() {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
