<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
     protected $fillable = [
        'buyer_id',
        'sender_id',
        'receiver_id',
        'message',
        'updated_at',
        'created_at'
    ];
}
