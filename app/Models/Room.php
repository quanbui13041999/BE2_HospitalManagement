<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table      = 'rooms';
    protected $primaryKey = 'room_id';
    public $timestamps    = false;

    protected $fillable = ['room_code','room_name','department_id','room_type','status','notes'];

    public function department() { return $this->belongsTo(Department::class, 'department_id', 'department_id'); }
    public function schedules()  { return $this->hasMany(DoctorSchedule::class, 'room_id', 'room_id'); }

    // Scope lọc trạng thái
    public function scopeAvailable($q) { return $q->where('status', 'Trống'); }
}