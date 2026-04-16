<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style="max-width: 500px; margin: 50px auto; font-family: sans-serif;">
    <h3>Dời lịch khám</h3>
    <form action="{{ route('booking.update', $appointment->appointment_id) }}" method="POST">
        @csrf
        <label>Chọn thời gian mới:</label><br><br>
        <select name="schedule_id" required style="width: 100%; padding: 10px;">
            @foreach($schedules as $s)
                <option value="{{ $s->schedule_id }}" {{ $s->schedule_id == $appointment->schedule_id ? 'selected' : '' }}>
                    {{ $s->work_date }} | {{ $s->start_time }} - BS: {{ $s->full_name }}
                </option>
            @endforeach
        </select>
        <br><br>
        <button type="submit" style="background: blue; color: white; padding: 10px;">Cập nhật lịch mới</button>
        <a href="{{ route('booking.index') }}">Quay lại</a>
    </form>
</div>
</body>
</html>