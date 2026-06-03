<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ConcurrentModificationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeviceRequest;
use App\Http\Requests\Admin\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    private function assertFreshVersion(mixed $expectedVersion, Device $device): void
    {
        if (!$expectedVersion) {
            throw new ConcurrentModificationException('Thiếu phiên bản dữ liệu. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
        }

        if ((int) $expectedVersion !== (int) $device->lock_version) {
            throw new ConcurrentModificationException('Thiết bị đã được admin khác thay đổi. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
        }
    }

    public function index()
    {
        $devices = Device::with('type')
            ->latest()
            ->paginate(10);

        return view('admin.devices.index', compact('devices'));
    }

    public function create()
    {
        $types = DeviceType::orderBy('name')->get();

        return view('admin.devices.create', compact('types'));
    }

    public function store(StoreDeviceRequest $request)
    {
        try {
            Device::create($request->validated());
        } catch (QueryException $e) {
            if ($this->isConstraintConflict($e)) {
                return redirect()->route('admin.devices.index')
                    ->with('warning', 'Thiết bị hoặc danh mục vừa được admin khác thay đổi. Trang sẽ được tải lại để cập nhật dữ liệu mới.')
                    ->with('reload_page', true);
            }

            Log::error('Create device failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])->withInput();
        }

        return redirect()->route('admin.devices.index')
            ->with('success', 'Thêm thiết bị thành công.');
    }

    public function edit($id)
    {
        $device = Device::with('type')->find($id);

        if (!$device) {
            return redirect()->route('admin.devices.index')
                ->with('warning', 'Thiết bị vừa bị admin khác xóa. Trang sẽ được tải lại để cập nhật dữ liệu mới.')
                ->with('reload_page', true);
        }

        $types = DeviceType::orderBy('name')->get();

        return view('admin.devices.edit', compact('device', 'types'));
    }

    public function update(UpdateDeviceRequest $request, $id)
    {
        $data = $request->validated();
        unset($data['lock_version']);

        try {
            DB::transaction(function () use ($request, $id, $data) {
                $device = Device::whereKey($id)->lockForUpdate()->first();

                if (!$device) {
                    throw new ConcurrentModificationException('Thiết bị vừa bị admin khác xóa. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
                }

                $this->assertFreshVersion($request->input('lock_version'), $device);

                $device->fill($data);
                $device->lock_version = $device->lock_version + 1;
                $device->save();
            });
        } catch (ConcurrentModificationException $e) {
            return redirect()->route('admin.devices.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        } catch (QueryException $e) {
            if ($this->isConstraintConflict($e)) {
                return redirect()->route('admin.devices.index')
                    ->with('warning', 'Thiết bị hoặc danh mục vừa được admin khác thay đổi. Trang sẽ được tải lại để cập nhật dữ liệu mới.')
                    ->with('reload_page', true);
            }

            Log::error('Update device failed', ['device_id' => $id, 'error' => $e->getMessage()]);

            return back()->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])->withInput();
        }

        return redirect()->route('admin.devices.index')
            ->with('success', 'Cập nhật thiết bị thành công.');
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'lock_version' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $device = Device::whereKey($id)->lockForUpdate()->first();

                if (!$device) {
                    throw new ConcurrentModificationException('Thiết bị vừa bị admin khác xóa. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
                }

                $this->assertFreshVersion($request->input('lock_version'), $device);
                $device->delete();
            });
        } catch (ConcurrentModificationException $e) {
            return redirect()->route('admin.devices.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        } catch (QueryException $e) {
            Log::error('Delete device failed', ['device_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->route('admin.devices.index')
                ->with('error', 'Không thể xóa thiết bị này vì đã có dữ liệu liên quan.');
        }

        return redirect()->route('admin.devices.index')
            ->with('success', 'Xóa thiết bị thành công.');
    }

    private function isConstraintConflict(QueryException $e): bool
    {
        return (string) $e->getCode() === '23000';
    }
}
