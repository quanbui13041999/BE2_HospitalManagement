<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VitalSigns extends Model {
    protected $primaryKey = 'vital_id';
    protected $fillable = [
        'record_id','blood_pressure','bp_status','heart_rate','hr_status',
        'temperature','temp_status','spo2','spo2_status','weight','blood_sugar','sugar_status',
    ];
    public function getStatusIcon(string $field): string {
        $status = $this->{$field . '_status'} ?? 'normal';
        return match($status) {
            'high'  => '▲',
            'low'   => '▼',
            default => '✓',
        };
    }
    public function getStatusClass(string $field): string {
        $status = $this->{$field . '_status'} ?? 'normal';
        return match($status) {
            'high'  => 'text-danger',
            'low'   => 'text-warning',
            default => 'text-success',
        };
    }
}
