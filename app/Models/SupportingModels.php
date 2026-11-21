<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model {
    protected $primaryKey = 'diagnosis_id';
    protected $fillable = ['record_id','diagnosis_name','icd_code','diagnosis_type','note'];
    public function getBorderColorAttribute(): string {
        return match($this->diagnosis_type) {
            'primary'     => '#e74c3c',
            'secondary'   => '#f39c12',
            'complication'=> '#8e44ad',
            default       => '#3498db',
        };
    }
}

class Prescription extends Model {
    protected $primaryKey = 'prescription_id';
    protected $fillable = ['record_id','drug_name','dosage','instructions','duration_days','quantity','unit'];
}

class MedicalOrder extends Model {
    protected $primaryKey = 'order_id';
    protected $fillable = ['record_id','order_type','order_name','description','result_status','result_note'];
    public function hasResult(): bool {
        return $this->result_status === 'Có kết quả';
    }
    public function getIconAttribute(): string {
        return match($this->order_type) {
            'lab'     => '🧪',
            'imaging' => '🩻',
            default   => '📋',
        };
    }
}

class MedicalAttachment extends Model {
    protected $primaryKey = 'attachment_id';
    protected $fillable = ['record_id','file_name','file_path','file_type','file_size','attachment_category'];
    public function getFileSizeFormattedAttribute(): string {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1024, 1) . ' KB';
    }
    public function isPdf(): bool {
        return strtolower($this->file_type) === 'pdf';
    }
    public function isImage(): bool {
        return in_array(strtolower($this->file_type), ['jpg','jpeg','png','gif','webp']);
    }
}

class RecordAllergy extends Model {
     protected $table = 'record_allergies';      // ← THÊM DÒNG 1
    protected $primaryKey = 'id';
    protected $fillable = ['record_id','allergen','severity','reaction'];
}
