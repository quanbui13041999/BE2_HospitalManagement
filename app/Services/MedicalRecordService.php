<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\VitalSigns;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Models\MedicalOrder;
use App\Models\MedicalAttachment;
use App\Models\RecordAllergy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MedicalRecordService
{
    /**
     * Tạo hồ sơ bệnh án mới từ lịch hẹn đã hoàn thành
     */
    public function createRecord(array $data): MedicalRecord
    {
        return DB::transaction(function () use ($data) {
            $record = MedicalRecord::create([
                'record_code'     => MedicalRecord::generateRecordCode(),
                'patient_id'      => $data['patient_id'] ?? null,
                'patient_name'    => $data['patient_name'],
                'patient_code'    => $data['patient_code'] ?? null,
                'doctor_id'       => $data['doctor_id'] ?? Auth::id(),
                'doctor_name'     => $data['doctor_name'],
                'appointment_id'  => $data['appointment_id'] ?? null,
                'exam_date'       => $data['exam_date'],
                'exam_time'       => $data['exam_time'] ?? now()->toTimeString(),
                'visit_type'      => $data['visit_type'] ?? 'Khám mới',
                'chief_complaint' => $data['chief_complaint'] ?? null,
            ]);

            // Chỉ số sinh tồn
            if (!empty($data['vitals'])) {
                VitalSigns::create(array_merge(['record_id' => $record->record_id], $data['vitals']));
            }

            // Dị ứng - Lọc bỏ item rỗng
            if (!empty($data['allergies']) && is_array($data['allergies'])) {
                foreach ($data['allergies'] as $allergy) {
                    // Chỉ tạo nếu có allergen
                    if (!empty($allergy['allergen'])) {
                        RecordAllergy::create(array_merge(['record_id' => $record->record_id], $allergy));
                    }
                }
            }

            // Chẩn đoán - Lọc bỏ item rỗng
            if (!empty($data['diagnoses']) && is_array($data['diagnoses'])) {
                foreach ($data['diagnoses'] as $diag) {
                    if (!empty($diag['diagnosis_name'])) {
                        Diagnosis::create(array_merge(['record_id' => $record->record_id], $diag));
                    }
                }
            }

            // Đơn thuốc - Lọc bỏ item rỗng
            if (!empty($data['prescriptions']) && is_array($data['prescriptions'])) {
                foreach ($data['prescriptions'] as $rx) {
                    if (!empty($rx['drug_name'])) {
                        Prescription::create(array_merge(['record_id' => $record->record_id], $rx));
                    }
                }
            }

            // Chỉ định - Lọc bỏ item rỗng
            if (!empty($data['orders']) && is_array($data['orders'])) {
                foreach ($data['orders'] as $order) {
                    if (!empty($order['order_name'])) {
                        MedicalOrder::create(array_merge(['record_id' => $record->record_id], $order));
                    }
                }
            }

            // THAY bằng — thêm update status trước khi return:
            // ✅ Cập nhật trạng thái appointment → "Đã Khám"
            if (!empty($data['appointment_id'])) {
                \App\Models\Appointment::where('appointment_id', $data['appointment_id'])
                    ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
                    ->update(['status' => 'Đã Khám']);
            }

            return $record->load([
                'vitalSigns',
                'diagnoses',
                'prescriptions',
                'medicalOrders',
                'attachments',
                'allergies',
            ]);
        });
    }

    /**
     * Cập nhật hồ sơ bệnh án
     */
    public function updateRecord(MedicalRecord $record, array $data): MedicalRecord
    {
        return DB::transaction(function () use ($record, $data) {
            // Cập nhật thông tin chính
            $record->update($data);

            // Cập nhật chỉ số sinh tồn
            if (!empty($data['vitals'])) {
                $record->vitalSigns()->updateOrCreate(
                    ['record_id' => $record->record_id],
                    $data['vitals']
                );
            }

            // 👉 THÊM: Sync dị ứng (xóa cũ, thêm mới - lọc bỏ rỗng)
            if (isset($data['allergies'])) {
                $record->allergies()->delete();
                if (is_array($data['allergies']) && !empty($data['allergies'])) {
                    foreach ($data['allergies'] as $allergy) {
                        // Chỉ tạo nếu có allergen
                        if (!empty($allergy['allergen'])) {
                            RecordAllergy::create(array_merge(['record_id' => $record->record_id], $allergy));
                        }
                    }
                }
            }

            // Sync chẩn đoán (xóa cũ, thêm mới - lọc bỏ rỗng)
            if (isset($data['diagnoses'])) {
                $record->diagnoses()->delete();
                if (is_array($data['diagnoses']) && !empty($data['diagnoses'])) {
                    foreach ($data['diagnoses'] as $diag) {
                        if (!empty($diag['diagnosis_name'])) {
                            Diagnosis::create(array_merge(['record_id' => $record->record_id], $diag));
                        }
                    }
                }
            }

            // Sync đơn thuốc (xóa cũ, thêm mới - lọc bỏ rỗng)
            if (isset($data['prescriptions'])) {
                $record->prescriptions()->delete();
                if (is_array($data['prescriptions']) && !empty($data['prescriptions'])) {
                    foreach ($data['prescriptions'] as $rx) {
                        if (!empty($rx['drug_name'])) {
                            Prescription::create(array_merge(['record_id' => $record->record_id], $rx));
                        }
                    }
                }
            }

            // Sync chỉ định (xóa cũ, thêm mới - lọc bỏ rỗng)
            if (isset($data['orders'])) {
                $record->medicalOrders()->delete();
                if (is_array($data['orders']) && !empty($data['orders'])) {
                    foreach ($data['orders'] as $order) {
                        if (!empty($order['order_name'])) {
                            MedicalOrder::create(array_merge(['record_id' => $record->record_id], $order));
                        }
                    }
                }
            }

            return $record->fresh([
                'vitalSigns',
                'diagnoses',
                'prescriptions',
                'medicalOrders',
                'attachments',
                'allergies',
            ]);
        });
    }

    /**
     * Upload file đính kèm
     */
    public function uploadAttachment(MedicalRecord $record, UploadedFile $file): MedicalAttachment
    {
        $path = $file->store("medical-records/{$record->record_id}", 'public');

        return MedicalAttachment::create([
            'record_id'           => $record->record_id,
            'file_name'           => $file->getClientOriginalName(),
            'file_path'           => $path,
            'file_type'           => $file->getClientOriginalExtension(),
            'file_size'           => $file->getSize(),
            'attachment_category' => 'document',
        ]);
    }

    /**
     * Xóa file đính kèm
     */
    public function deleteAttachment(MedicalAttachment $attachment): void
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }

    /**
     * Lấy hồ sơ với đầy đủ thông tin
     */
    public function getRecordDetail(int|string $id): MedicalRecord
    {
        return MedicalRecord::with([
            'vitalSigns',
            'diagnoses',
            'prescriptions',
            'medicalOrders',
            'attachments',
            'allergies',
        ])->findOrFail($id);
    }

    /**
     * Danh sách hồ sơ bệnh nhân
     */
    public function getPatientRecords(int $patientId, int $perPage = 10)
    {
        return MedicalRecord::with(['vitalSigns', 'diagnoses'])
            ->where('patient_id', $patientId)
            ->orderByDesc('exam_date')
            ->paginate($perPage);
    }

    /**
     * Danh sách hồ sơ bác sĩ phụ trách
     */
    public function getDoctorRecords(int $doctorId, int $perPage = 15)
    {
        return MedicalRecord::with(['vitalSigns', 'diagnoses'])
            ->where('doctor_id', $doctorId)
            ->orderByDesc('exam_date')
            ->paginate($perPage);
    }
}
