<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BhytLookupRequest;
use App\Services\Admin\BhytService;
use Illuminate\Http\Request;

class BhytController extends Controller
{
    public function __construct(protected BhytService $bhytService) {}

    // ----------------------------------------------------------------
    // Trang quản lý BHYT (danh sách sắp hết hạn + form tra cứu)
    // ----------------------------------------------------------------
    public function index()
    {
        return view('admin.bhyt.index', $this->bhytService->buildIndexData());
    }

    // ----------------------------------------------------------------
    // Tra cứu thẻ BHYT (AJAX hoặc POST form)
    // ----------------------------------------------------------------
    public function lookup(BhytLookupRequest $request)
    {
        $result = $this->bhytService->lookup($request->card_number);

        if (!$result) {
            return $request->wantsJson()
                ? response()->json(['found' => false, 'message' => 'Không tìm thấy thẻ BHYT.'], 404)
                : back()->withErrors(['card_number' => 'Không tìm thấy thẻ BHYT với mã này.'])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json(['found' => true, 'data' => $result]);
        }

        return back()->with('bhyt_result', $result);
    }

    // ----------------------------------------------------------------
    // Áp dụng BHYT vào hóa đơn (AJAX)
    // ----------------------------------------------------------------
    public function apply(Request $request)
    {
        $request->validate([
            'invoice_id'  => 'required|exists:Invoices,invoice_id',
            'card_number' => 'required|string',
        ]);

        $result = $this->bhytService->applyToInvoice(
            $request->invoice_id,
            $request->card_number
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể áp dụng BHYT. Kiểm tra thẻ và hóa đơn.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
            'message' => $result['already_applied'] ?? false
                ? 'BHYT đã được áp dụng trước đó.'
                : 'Áp dụng BHYT thành công!',
        ]);
    }
}
