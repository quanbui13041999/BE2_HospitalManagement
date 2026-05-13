<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MedicalDocument extends Model
{
    use HasFactory;

    protected $table = 'medicaldocuments';
    protected $primaryKey = 'doc_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'record_id',
        'doc_type',
        'doc_name',
        'file_path',
        'uploaded_at',
    ];

    // ✅ CHỈ giữ field có trong DB
    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeOfCategory($query, ?string $category)
    {
        return $category ? $query->where('doc_type', $category) : $query;
    }

    public function scopeOfPeriod($query, ?string $period)
    {
        return match ($period) {
            'this_month'    => $query->whereMonth('uploaded_at', now()->month)
                                     ->whereYear('uploaded_at', now()->year),
            'last_3_months' => $query->where('uploaded_at', '>=', now()->subMonths(3)),
            'this_year'     => $query->whereYear('uploaded_at', now()->year),
            default         => $query,
        };
    }

    public function scopeSearch($query, ?string $keyword)
    {
        return $keyword
            ? $query->where('doc_name', 'like', "%{$keyword}%")
            : $query;
    }

    // ── Accessors ───────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(
            strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png']
        );
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_path || !Storage::disk('public')->exists($this->file_path)) {
            return 'N/A';
        }

        $size = Storage::disk('public')->size($this->file_path);

        return $size >= 1024 * 1024
            ? number_format($size / 1024 / 1024, 1) . ' MB'
            : number_format($size / 1024, 1) . ' KB';
    }

    // ── Helpers ─────────────────────────────────────────────────

    public static function categories(): array
    {
        return [
            'xet_nghiem'  => ['label' => 'Xét nghiệm',         'icon' => '🧪', 'badge' => 'blue'],
            'hinh_anh'    => ['label' => 'Chẩn đoán hình ảnh', 'icon' => '🫁', 'badge' => 'teal'],
            'don_thuoc'   => ['label' => 'Đơn thuốc',          'icon' => '💊', 'badge' => 'red'],
            'chuyen_vien' => ['label' => 'Giấy chuyển viện',   'icon' => '📄', 'badge' => 'orange'],
            'khac'        => ['label' => 'Khác',               'icon' => '📋', 'badge' => 'green'],
        ];
    }

    public function getCategoryInfoAttribute(): array
    {
        return static::categories()[$this->doc_type]
            ?? ['label' => 'Khác', 'icon' => '📋', 'badge' => 'green'];
    }
}