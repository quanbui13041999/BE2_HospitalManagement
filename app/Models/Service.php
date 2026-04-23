<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table      = 'services';
    protected $primaryKey = 'service_id';
    public $timestamps    = false;

    protected $fillable = [
        'service_code','service_name','department_id',
        'description','duration_minutes','status',
    ];

    protected $casts = [
        'status'           => 'boolean',
        'duration_minutes' => 'integer',
    ];

    // ── Relations ──
    public function department() { return $this->belongsTo(Department::class, 'department_id', 'department_id'); }
    public function prices()     { return $this->hasMany(ServicePrice::class, 'service_id', 'service_id'); }
    public function appointments(){ return $this->hasMany(Appointment::class, 'service_id', 'service_id'); }

    // Lấy giá hiện tại theo loại (mặc định: Thường)
    public function currentPrice(string $type = 'Thường')
    {
        return $this->prices()
            ->where('price_type', $type)
            ->whereNull('end_date')
            ->latest('effective_date')
            ->value('price') ?? 0;
    }

    // ── Scopes ──
    public function scopeActive($q) { return $q->where('services.status', 1); }
}


class ServicePrice extends Model
{
    protected $table      = 'serviceprices';
    protected $primaryKey = 'price_id';
    public $timestamps    = false;

    protected $fillable = [
        'service_id','price_type','price',
        'effective_date','end_date','created_by','created_at',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'effective_date' => 'date',
        'end_date'       => 'date',
        'created_at'     => 'datetime',
    ];

    public function service()    { return $this->belongsTo(Service::class, 'service_id', 'service_id'); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by', 'user_id'); }

    public function scopeCurrent($q)
    {
        return $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
    }
}