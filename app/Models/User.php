<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table      = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps    = false;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'role_id',
        'avatar_url',
        'status',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'date_of_birth' => 'date',
        'status'        => 'boolean',
        'created_at'    => 'datetime',
    ];

    // ── Relations ──
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id', 'user_id');
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'user_id', 'user_id');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'user_id', 'user_id');
    }
    public function insuranceCards()
    {
        return $this->hasMany(InsuranceCard::class, 'user_id', 'user_id');
    }
    public function membershipCard()
    {
        return $this->hasOne(MembershipCard::class, 'user_id', 'user_id');
    }
    public function allergies()
    {
        return $this->hasMany(PatientAllergy::class, 'user_id', 'user_id');
    }
    public function medicalHistory()
    {
        return $this->hasMany(PatientMedicalHistory::class, 'user_id', 'user_id');
    }
    public function vaccinationRecords()
    {
        return $this->hasMany(VaccinationRecord::class, 'user_id', 'user_id');
    }
    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class, 'user_id', 'user_id');
    }
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }
    public function treatmentReminders()
    {
        return $this->hasMany(TreatmentReminder::class, 'user_id', 'user_id');
    }
    public function medicalDocuments()
    {
        return $this->hasMany(MedicalDocument::class, 'user_id', 'user_id');
    }

    // ── Accessors ──
    public function getIsAdminAttribute()
    {
        return $this->isAdmin();
    }

    public function getIsDoctorAttribute()
    {
        return $this->isDoctor();
    }

    public function getIsPatientAttribute()
    {
        return $this->isPatient();
    }

    // ── Helpers ──
    public function isAdmin()
    {
        return $this->role_id === 1;
    }

    public function isDoctor()
    {
        return $this->role_id === 2;
    }

    public function isPatient()
    {
        return $this->role_id === 3;
    }

    /**
     * Tạo thông báo cho user
     */
    public function notify(string $type, string $title, string $content, int $refId = null, string $refType = null)
    {
        return $this->notifications()->create([
            'notif_type' => $type,
            'title' => $title,
            'content' => $content,
            'ref_id' => $refId,
            'ref_type' => $refType,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    /**
     * Ghi log hoạt động
     */
    public function logActivity(string $action, string $ipAddress = null)
    {
        return $this->activityLogs()->create([
            'action' => $action,
            'ip_address' => $ipAddress,
            'created_at' => now(),
        ]);
    }
}
