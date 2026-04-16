<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style="max-width: 800px; margin: 20px auto; font-family: sans-serif;">
    <h3>Lịch khám của tôi</h3>
    @if(session('success')) <p style="color: green;">{{ session('success') }}</p> @endif

    <table border="1" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: #eee;">
                <th>Bác sĩ</th>
                <th>Dịch vụ</th>
                <th>Ngày khám</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $item)
            <tr>
                <td>{{ $item->doctor_name }}</td>
                <td>{{ $item->service_name }}</td>
                <td>{{ $item->work_date }} ({{ $item->start_time }})</td>
                <td>{{ $item->status }}</td>
                <td>
                    @if($item->status != 'Đã hủy')
                        <a href="{{ route('booking.edit', $item->appointment_id) }}">Dời lịch</a> |
                        <form action="{{ route('booking.cancel', $item->appointment_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" onclick="return confirm('Bạn có chắc muốn hủy?')" style="color:red; border:none; background:none; cursor:pointer;">Hủy</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>