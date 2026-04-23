<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipCard extends Model
{
    protected $table = 'membershipcards'; 

    // BẮT BUỘC: Vì thực tế DB của bạn dùng card_id chứ không phải id
    protected $primaryKey = 'card_id';

    // BẮT BUỘC: Vì DB thực tế không có created_at và updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'card_number', 'tier', 'points'
    ];
}