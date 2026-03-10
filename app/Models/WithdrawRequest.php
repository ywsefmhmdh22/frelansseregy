<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;

    // السطر ده هو اللي ناقصك عشان البيانات تتسيف
    protected $fillable = ['user_id', 'amount', 'method', 'details', 'status'];
}
