<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable =[
        'user_id',
        'recipient_name',
        'recipient_acc_num',
        'recipient_bank',
        'amount',
        'transfer_reference',
        'status',
        'description'
    ];
}
