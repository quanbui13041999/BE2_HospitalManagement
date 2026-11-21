<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertEmergencyContactsRequest;
use App\Models\EmergencyContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmergencyContactController extends Controller
{
    /**
     * Hiển thị form liên hệ khẩn cấp của người dùng hiện tại.
     */
    public function index(): View
    {
        // Lấy tối đa 3 liên hệ đã lưu, sắp xếp theo priority
        $saved = EmergencyContact::where('user_id', Auth::id())
            ->ordered()
            ->get()
            ->keyBy('priority');   // key = 1, 2, 3

        // Luôn trả về mảng 3 phần tử để blade render đủ 3 card
        $contacts = [];
        for ($priority = 1; $priority <= 3; $priority++) {
            $contact = $saved->get($priority);
            $contacts[] = [
                'id'                 => $contact?->id,
                'name'               => $contact?->name ?? '',
                'relationship'       => $contact?->relationship ?? '',
                'phone'              => $contact?->phone ?? '',
                'email'              => $contact?->email ?? '',
                'lab_notifications'  => $contact?->lab_notifications ?? false,
                'recovery_updates'   => $contact?->recovery_updates ?? false,
            ];
        }

        $relationshipOptions = [
            'Vợ/Chồng',
            'Mẹ',
            'Cha',
            'Con',
            'Anh/Chị em',
            'Người giám hộ',
            'Khác',
        ];

      return view('emergency.emergency-contacts', compact('contacts', 'relationshipOptions'));
    }

    /**
     * Lưu / cập nhật toàn bộ danh sách liên hệ khẩn cấp (upsert).
     *
     * Logic: duyệt qua 3 slot, nếu có name+phone thì upsert,
     * nếu trống thì xoá bản ghi đó (nếu tồn tại).
     */
    public function store(UpsertEmergencyContactsRequest $request): RedirectResponse
    {
        $userId   = Auth::id();
        $contacts = $request->validated()['contacts'];

        foreach ($contacts as $index => $data) {
            $priority = $index + 1;
            $hasData  = filled($data['name']) && filled($data['phone']);

            if ($hasData) {
                // Upsert: tạo mới hoặc cập nhật theo user_id + priority
                EmergencyContact::updateOrCreate(
                    [
                        'user_id'  => $userId,
                        'priority' => $priority,
                    ],
                    [
                        'name'              => $data['name'],
                        'relationship'      => $data['relationship'] ?? null,
                        'phone'             => $data['phone'],
                        'email'             => $data['email'] ?? null,
                        'lab_notifications' => $data['lab_notifications'],
                        'recovery_updates'  => $data['recovery_updates'],
                    ]
                );
            } else {
                // Xoá mềm bản ghi nếu người dùng để trống slot này
                EmergencyContact::where('user_id', $userId)
                    ->where('priority', $priority)
                    ->delete();
            }
        }

        return redirect()
            ->route('emergency-contacts.index')
            ->with('success', 'Danh sách liên hệ khẩn cấp đã được lưu thành công!');
    }
}