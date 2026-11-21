<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    public $timestamps = false;

    protected $fillable = ['department_name', 'description', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'department_id', 'department_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'department_id', 'department_id');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'department_id', 'department_id');
    }
}
