<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
   protected $table      = 'activitylogs';
    protected $primaryKey = 'log_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['user_id','action','ip_address'];
    protected $casts    = ['created_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
}
