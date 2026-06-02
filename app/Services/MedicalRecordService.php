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
                'status'          => MedicalRecord::STATUS_COMPLETED,
            ]);

            // Chỉ số sinh tồn
            if (!empty($data['vitals'])) {
                VitalSigns::create(array_merge(['record_id' => $record->record_id], $data['vitals']));
            }

            // Dị ứng - Lọc bỏ item rỗng
            if (!empty($data['allergies']) && is_array($data['allergies'])) {
                foreach ($data['allergies'] as $allergy) {
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

            // Cập nhật trạng thái appointment → "Đã Khám"
            if (!empty($data['appointment_id'])) {
                \App\Models\Appointment::where('appointment_id', $data['appointment_id'])
                    ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
                    ->update(['status' => 'Đã Khám']);
            }

            $this->logRecordCreated($record, $data);

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
            $before = $record->only(['patient_name', 'doctor_name', 'exam_date', 'exam_time', 'visit_type', 'status']);

            $record->update($data);

            if (!empty($data['vitals'])) {
                $record->vitalSigns()->updateOrCreate(
                    ['record_id' => $record->record_id],
                    $data['vitals']
                );
            }

            if (isset($data['allergies'])) {
                $record->allergies()->delete();
                if (is_array($data['allergies']) && !empty($data['allergies'])) {
                    foreach ($data['allergies'] as $allergy) {
                        if (!empty($allergy['allergen'])) {
                            RecordAllergy::create(array_merge(['record_id' => $record->record_id], $allergy));
                        }
                    }
                }
            }

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

            $fresh = $record->fresh([
                'vitalSigns',
                'diagnoses',
                'prescriptions',
                'medicalOrders',
                'attachments',
                'allergies',
            ]);

            $this->logRecordUpdated($fresh, $before, $data);

            return $fresh;
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

    // ========== THÊM CÁC METHOD MỚI SAU ĐÂY ==========

    /**
     * Danh sách hồ sơ bệnh nhân với bộ lọc nâng cao
     */
    public function getPatientRecords(int $patientId, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = MedicalRecord::with(['vitalSigns', 'diagnoses', 'doctor'])
            ->where('patient_id', $patientId);
        
        $query = $this->applyFilters($query, $filters);
        
        $perPage = $filters['per_page'] ?? 10;
        return $query->orderByDesc('exam_date')->paginate($perPage)->withQueryString();
    }

    /**
     * Danh sách hồ sơ bác sĩ phụ trách với bộ lọc nâng cao
     */
    public function getDoctorRecords(int $doctorId, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = MedicalRecord::with(['vitalSigns', 'diagnoses', 'patient'])
            ->where('doctor_id', $doctorId);
        
        $query = $this->applyFilters($query, $filters);
        
        $perPage = $filters['per_page'] ?? 10;
        return $query->orderByDesc('exam_date')->paginate($perPage)->withQueryString();
    }

    /**
     * Danh sách hồ sơ cho Admin (xem tất cả) với bộ lọc nâng cao
     */
    public function getAllRecords(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = MedicalRecord::with(['vitalSigns', 'diagnoses', 'doctor', 'patient']);
        
        $query = $this->applyFilters($query, $filters);
        
        $perPage = $filters['per_page'] ?? 10;
        return $query->orderByDesc('exam_date')->paginate($perPage)->withQueryString();
    }

    /**
     * Áp dụng các bộ lọc cho query
     */
    protected function applyFilters($query, array $filters)
    {
        // 1. Tìm kiếm từ khóa (mã phiếu, tên bệnh nhân, tên bác sĩ)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('record_code', 'like', "%{$search}%")
                  ->orWhere('patient_name', 'like', "%{$search}%")
                  ->orWhere('doctor_name', 'like', "%{$search}%");
            });
        }
        
        // 2. Lọc theo loại khám
        if (!empty($filters['visit_type'])) {
            $query->where('visit_type', $filters['visit_type']);
        }
        
        // 3. Lọc theo trạng thái
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // 4. Lọc theo ngày khám (từ ngày)
        if (!empty($filters['date_from'])) {
            $query->whereDate('exam_date', '>=', $filters['date_from']);
        }
        
        // 5. Lọc theo ngày khám (đến ngày)
        if (!empty($filters['date_to'])) {
            $query->whereDate('exam_date', '<=', $filters['date_to']);
        }
        
        // 6. Sắp xếp
        $sortBy = $filters['sort_by'] ?? 'exam_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['exam_date', 'record_code', 'patient_name', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        return $query;
    }

    /**
     * Lấy danh sách loại khám (cho dropdown filter)
     */
    public function getVisitTypes($userId = null, $roleId = null): array
    {
        $query = MedicalRecord::select('visit_type')->distinct();
        
        // Lọc theo role nếu cần
        if ($roleId == 2 && $userId) { // Doctor
            $query->where('doctor_id', $userId);
        } elseif ($roleId == 3 && $userId) { // Patient
            $query->where('patient_id', $userId);
        }
        
        return $query->pluck('visit_type')->toArray();
    }

    /**
     * Lấy danh sách trạng thái (cho dropdown filter)
     */
    public function getStatuses(): array
    {
        return [
            'pending' => 'Chờ khám',
            'examining' => 'Đang khám',
            'completed' => 'Đã khám xong',
            'prescribed' => 'Đã kê đơn',
            'follow_up' => 'Cần tái khám',
            'emergency' => 'Cấp cứu',
            'cancelled' => 'Đã hủy'
        ];
    }

    /**
     * Thống kê số lượng hồ sơ theo trạng thái
     */
    public function getStatistics($userId = null, $roleId = null): array
    {
        $query = MedicalRecord::query();
        
        if ($roleId == 2 && $userId) {
            $query->where('doctor_id', $userId);
        } elseif ($roleId == 3 && $userId) {
            $query->where('patient_id', $userId);
        }
        
        $total = $query->count();
        $statusCounts = [];
        
        foreach ($this->getStatuses() as $key => $label) {
            $statusCounts[$key] = (clone $query)->where('status', $key)->count();
        }
        
        return [
            'total' => $total,
            'by_status' => $statusCounts,
            'latest' => (clone $query)->orderBy('exam_date', 'desc')->first(),
            'oldest' => (clone $query)->orderBy('exam_date', 'asc')->first(),
        ];
    }

    private function logRecordCreated(MedicalRecord $record, array $data): void
    {
        $actor = Auth::user();
        $doctorName = $record->doctor_name ?: $actor?->full_name ?: 'Bác sĩ';
        $patientName = $record->patient_name ?: 'bệnh nhân';

        ActivityLogService::log(
            'Tạo hồ sơ bệnh án',
            'BS. ' . $doctorName . ' đã tạo hồ sơ bệnh án cho bệnh nhân ' . $patientName . '.',
            'medical_record',
            $record->record_id,
            [
                'record_code' => $record->record_code,
                'patient_id' => $record->patient_id,
                'doctor_id' => $record->doctor_id,
                'appointment_id' => $record->appointment_id,
                'exam_date' => optional($record->exam_date)->toDateString(),
                'diagnosis_count' => $this->countFilled($data['diagnoses'] ?? [], 'diagnosis_name'),
                'prescription_count' => $this->countFilled($data['prescriptions'] ?? [], 'drug_name'),
                'order_count' => $this->countFilled($data['orders'] ?? [], 'order_name'),
            ],
            'success',
            $actor
        );

        ActivityLogService::log(
            'Bác sĩ khám bệnh',
            'BS. ' . $doctorName . ' đã khám cho bệnh nhân ' . $patientName . '.',
            'appointment',
            $record->appointment_id,
            [
                'record_id' => $record->record_id,
                'patient_id' => $record->patient_id,
                'doctor_id' => $record->doctor_id,
            ],
            'success',
            $actor
        );

        $this->logClinicalSubActions($record, $data, $actor);
    }

    private function logRecordUpdated(MedicalRecord $record, array $before, array $data): void
    {
        $actor = Auth::user();
        $after = $record->only(['patient_name', 'doctor_name', 'exam_date', 'exam_time', 'visit_type', 'status']);

        ActivityLogService::log(
            'Cập nhật hồ sơ bệnh án',
            ($actor?->full_name ?: 'Người dùng') . ' đã cập nhật hồ sơ bệnh án #' . $record->record_id . ' của bệnh nhân ' . ($record->patient_name ?: 'không rõ') . '.',
            'medical_record',
            $record->record_id,
            [
                'record_code' => $record->record_code,
                'changes' => ActivityLogService::summarizeChanges($before, $after, ['patient_name', 'doctor_name', 'exam_date', 'exam_time', 'visit_type', 'status']),
                'diagnosis_count' => $this->countFilled($data['diagnoses'] ?? [], 'diagnosis_name'),
                'prescription_count' => $this->countFilled($data['prescriptions'] ?? [], 'drug_name'),
                'order_count' => $this->countFilled($data['orders'] ?? [], 'order_name'),
            ],
            'success',
            $actor
        );

        $this->logClinicalSubActions($record, $data, $actor);
    }

    private function logClinicalSubActions(MedicalRecord $record, array $data, $actor): void
    {
        $doctorName = $record->doctor_name ?: $actor?->full_name ?: 'Bác sĩ';
        $patientName = $record->patient_name ?: 'bệnh nhân';

        $diagnosisCount = $this->countFilled($data['diagnoses'] ?? [], 'diagnosis_name');
        if ($diagnosisCount > 0) {
            ActivityLogService::log(
                'Tạo chẩn đoán',
                'BS. ' . $doctorName . ' đã tạo ' . $diagnosisCount . ' chẩn đoán cho bệnh nhân ' . $patientName . '.',
                'medical_record',
                $record->record_id,
                ['diagnosis_count' => $diagnosisCount],
                'success',
                $actor
            );
        }

        $prescriptionCount = $this->countFilled($data['prescriptions'] ?? [], 'drug_name');
        if ($prescriptionCount > 0) {
            ActivityLogService::log(
                'Kê đơn thuốc',
                'BS. ' . $doctorName . ' đã kê ' . $prescriptionCount . ' thuốc cho bệnh nhân ' . $patientName . '.',
                'medical_record',
                $record->record_id,
                ['prescription_count' => $prescriptionCount],
                'success',
                $actor
            );
        }

        $orderCount = $this->countFilled($data['orders'] ?? [], 'order_name');
        if ($orderCount > 0) {
            ActivityLogService::log(
                'Thêm chỉ định xét nghiệm / hình ảnh',
                'BS. ' . $doctorName . ' đã thêm ' . $orderCount . ' chỉ định cho bệnh nhân ' . $patientName . '.',
                'medical_record',
                $record->record_id,
                ['order_count' => $orderCount],
                'success',
                $actor
            );
        }
    }

    private function countFilled(mixed $items, string $field): int
    {
        if (!is_array($items)) {
            return 0;
        }

        return collect($items)->filter(fn($item) => is_array($item) && !empty($item[$field]))->count();
    }
}
