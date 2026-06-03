<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntityStatusController extends Controller
{
    /**
     * Check the current state of an entity in the database.
     * Used for realtime polling to detect concurrent modifications or deletions.
     */
    public function checkStatus(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');
        $lockVersion = $request->query('lock_version');

        if (!$type || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu tham số bắt buộc.'
            ], 400);
        }

        $table = null;
        $primaryKey = null;

        switch ($type) {
            case 'room':
                $table = 'rooms';
                $primaryKey = 'room_id';
                break;
            case 'schedule':
                $table = 'doctorschedules';
                $primaryKey = 'schedule_id';
                break;
            case 'service':
                $table = 'services';
                $primaryKey = 'service_id';
                break;
            case 'price':
                $table = 'serviceprices';
                $primaryKey = 'price_id';
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Loại thực thể không hợp lệ.'
                ], 400);
        }

        $entity = DB::table($table)->where($primaryKey, $id)->first();

        if (!$entity) {
            return response()->json([
                'success' => true,
                'status' => 'deleted',
                'message' => 'Dữ liệu này đã bị xóa trong database bởi một phiên làm việc khác.'
            ]);
        }

        if ($lockVersion !== null && $lockVersion !== '') {
            $dbTimestamp = isset($entity->updated_at) ? strtotime($entity->updated_at) : null;
            if ($dbTimestamp !== null && (string)$lockVersion !== (string)$dbTimestamp) {
                return response()->json([
                    'success' => true,
                    'status' => 'modified',
                    'message' => 'Dữ liệu này đã được thay đổi trong database bởi một phiên làm việc khác. Vui lòng tải lại trang.'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'status' => 'unchanged'
        ]);
    }
}
