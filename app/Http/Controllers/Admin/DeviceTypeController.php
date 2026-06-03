<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ConcurrentModificationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeviceTypeRequest;
use App\Http\Requests\Admin\UpdateDeviceTypeRequest;
use App\Models\DeviceType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeviceTypeController extends Controller
{
    private function assertFreshVersion(mixed $expectedVersion, DeviceType $type): void
    {
        if (!$expectedVersion) {
            throw new ConcurrentModificationException('Thiếu phiên bản dữ liệu. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
        }

        if ((int) $expectedVersion !== (int) $type->lock_version) {
            throw new ConcurrentModificationException('Danh mục thiết bị đã được admin khác thay đổi. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
        }
    }

    public function index()
    {
        $types = DeviceType::withCount('devices')
            ->latest()
            ->paginate(10);

        return view('admin.device_types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.device_types.create');
    }

    public function store(StoreDeviceTypeRequest $request)
    {
        try {
            DeviceType::create($request->validated());
        } catch (QueryException $e) {
            if ($this->isUniqueConstraint($e)) {
                return redirect()->route('admin.device-types.index')
                    ->with('warning', 'Danh mục này vừa được admin khác tạo. Trang sẽ được tải lại để cập nhật danh sách.')
                    ->with('reload_page', true);
            }

            Log::error('Create device type failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])->withInput();
        }

        return redirect()->route('admin.device-types.index')
            ->with('success', 'Thêm danh mục thiết bị thành công.');
    }

    public function edit($id)
    {
        $id = $this->validRouteId($id);

        if (!$id) {
            return $this->redirectToDeviceTypesIndexNotFound();
        }

        $type = DeviceType::find($id);

        if (!$type) {
            return $this->redirectToDeviceTypesIndexNotFound();
        }

        return view('admin.device_types.edit', compact('type'));
    }

    public function update(UpdateDeviceTypeRequest $request, $id)
    {
        $id = $this->validRouteId($id);

        if (!$id) {
            return $this->redirectToDeviceTypesIndexNotFound();
        }

        $data = $request->validated();
        unset($data['lock_version']);

        try {
            DB::transaction(function () use ($request, $id, $data) {
                $type = DeviceType::whereKey($id)->lockForUpdate()->first();

                if (!$type) {
                    throw new ConcurrentModificationException('Danh mục thiết bị vừa bị admin khác xóa. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
                }

                $this->assertFreshVersion($request->input('lock_version'), $type);

                $type->fill($data);
                $type->lock_version = $type->lock_version + 1;
                $type->save();
            });
        } catch (ConcurrentModificationException $e) {
            return redirect()->route('admin.device-types.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        } catch (QueryException $e) {
            if ($this->isUniqueConstraint($e)) {
                return redirect()->route('admin.device-types.index')
                    ->with('warning', 'Tên danh mục vừa được admin khác sử dụng. Trang sẽ được tải lại để cập nhật dữ liệu mới.')
                    ->with('reload_page', true);
            }

            Log::error('Update device type failed', ['device_type_id' => $id, 'error' => $e->getMessage()]);

            return back()->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])->withInput();
        }

        return redirect()->route('admin.device-types.index')
            ->with('success', 'Cập nhật danh mục thiết bị thành công.');
    }

    public function destroy(Request $request, $id)
    {
        $id = $this->validRouteId($id);

        if (!$id) {
            return $this->redirectToDeviceTypesIndexNotFound();
        }

        $request->validate([
            'lock_version' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $type = DeviceType::whereKey($id)->lockForUpdate()->first();

                if (!$type) {
                    throw new ConcurrentModificationException('Danh mục thiết bị vừa bị admin khác xóa. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
                }

                $this->assertFreshVersion($request->input('lock_version'), $type);

                if ($type->devices()->exists()) {
                    throw new RuntimeException('Không thể xóa danh mục đang có thiết bị. Vui lòng chuyển hoặc xóa thiết bị thuộc danh mục này trước.');
                }

                $type->delete();
            });
        } catch (ConcurrentModificationException $e) {
            return redirect()->route('admin.device-types.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        } catch (RuntimeException $e) {
            return redirect()->route('admin.device-types.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        } catch (QueryException $e) {
            Log::error('Delete device type failed', ['device_type_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->route('admin.device-types.index')
                ->with('error', 'Không thể xóa danh mục này vì đã có dữ liệu liên quan.');
        }

        return redirect()->route('admin.device-types.index')
            ->with('success', 'Xóa danh mục thiết bị thành công.');
    }

    private function isUniqueConstraint(QueryException $e): bool
    {
        return (string) $e->getCode() === '23000';
    }

    private function validRouteId($id): ?int
    {
        $id = trim((string) $id);

        return preg_match('/\A[1-9][0-9]*\z/', $id) ? (int) $id : null;
    }

    private function redirectToDeviceTypesIndexNotFound()
    {
        return redirect()
            ->route('admin.device-types.index')
            ->with('warning', 'Không tìm thấy trang danh mục thiết bị.')
            ->with('reload_page', true);
    }
}
