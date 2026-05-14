<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    protected $table      = 'vaccines';
    protected $primaryKey = 'vaccine_id';
    public $timestamps    = false;

    protected $fillable = ['vaccine_name','description','manufacturer','doses_required','status'];
    protected $casts    = ['status' => 'boolean', 'doses_required' => 'integer'];



    public function records() { return $this->hasMany(VaccinationRecord::class, 'vaccine_id', 'vaccine_id'); }
}


class Medicine extends Model
{
    protected $table      = 'medicines';
    protected $primaryKey = 'medicine_id';
    public $timestamps    = false;

    protected $fillable = [
        'medicine_code','medicine_name','unit','unit_price',
        'stock_quantity','min_stock','expiry_date','status',
    ];

    protected $casts = [
        'unit_price'     => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock'      => 'integer',
        'expiry_date'    => 'date',
        'status'         => 'boolean',
    ];

    public function transactions() { return $this->hasMany(MedicineTransaction::class, 'medicine_id', 'medicine_id'); }

    public function isLowStock(): bool  { return $this->stock_quantity <= $this->min_stock; }
    public function isExpired(): bool   { return $this->expiry_date && $this->expiry_date->isPast(); }

    public function scopeActive($q)    { return $q->where('status', 1); }
    public function scopeLowStock($q)  { return $q->whereColumn('stock_quantity', '<=', 'min_stock'); }
}


class MedicineTransaction extends Model
{
    protected $table      = 'medicinetransactions';
    protected $primaryKey = 'transaction_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'medicine_id','trans_type','quantity',
        'unit_price','reference_id','note','created_by',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'quantity'    => 'integer',
        'created_at'  => 'datetime',
    ];

    // trans_type: 'Nhập' | 'Xuất'
    public function medicine()   { return $this->belongsTo(Medicine::class, 'medicine_id', 'medicine_id'); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by', 'user_id'); }

    public function scopeImport($q) { return $q->where('trans_type', 'Nhập'); }
    public function scopeExport($q) { return $q->where('trans_type', 'Xuất'); }
}


class ChatRoom extends Model
{
    protected $table      = 'chatrooms';
    protected $primaryKey = 'room_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['user_id','doctor_id','status','closed_at'];
    protected $casts    = ['created_at' => 'datetime', 'closed_at' => 'datetime'];

    public function user()     { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function doctor()   { return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id'); }
    public function messages() { return $this->hasMany(ChatMessage::class, 'room_id', 'room_id'); }

    public function latestMessage() { return $this->hasOne(ChatMessage::class, 'room_id', 'room_id')->latestOfMany('sent_at'); }

    public function scopeOpen($q) { return $q->where('status', 'Mở'); }
}


class ChatMessage extends Model
{
    protected $table      = 'chatmessages';
    protected $primaryKey = 'message_id';

    const CREATED_AT = 'sent_at';
    const UPDATED_AT = null;

    protected $fillable = ['room_id','sender_id','message_text','is_read'];
    protected $casts    = ['is_read' => 'boolean', 'sent_at' => 'datetime'];

    public function chatRoom() { return $this->belongsTo(ChatRoom::class, 'room_id', 'room_id'); }
    public function sender()   { return $this->belongsTo(User::class, 'sender_id', 'user_id'); }

    public function scopeUnread($q) { return $q->where('is_read', false); }
}


class HospitalNews extends Model
{
    protected $table      = 'hospitalnews';
    protected $primaryKey = 'news_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'title','content','category','thumbnail',
        'author_id','is_published','published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
    ];

    public function author() { return $this->belongsTo(User::class, 'author_id', 'user_id'); }

    public function scopePublished($q)         { return $q->where('is_published', true); }
    public function scopeByCategory($q, $cat)  { return $q->where('category', $cat); }
}