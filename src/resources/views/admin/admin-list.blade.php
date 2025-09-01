@extends('layouts.default')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/list.css')  }}">
@endsection

@section('content')

@include('components.header')

<div class="attendance-wrapper">
    <div class="attendance-title">
        <h1 class="title">{{ $currentDate->format('Y年m月d日') }}の勤怠</h1>
    </div>

    <div class="month-nav">
        <a href="{{ route('admin.attendance.list', $currentDate->copy()->subDay()->toDateString()) }}" class="prev-month">&larr; 前日</a>
        <div class="current-month"><img src="{{ asset('img/calendar.png') }}" alt="" class="img"><span>{{ $currentDate->format('Y/m/d') }}</span></div>
        <a href="{{ route('admin.attendance.list', $currentDate->copy()->addDay()->toDateString()) }}" class="next-month">翌日 &rarr;</a>
    </div>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @php
            $attendance = $attendances->get($user->id);
            @endphp
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $attendance?->clock_in_formatted ?? '' }}</td>
                <td>{{ $attendance?->clock_out_formatted ?? '' }}</td>
                <td>{{ $attendance?->break_formatted ?? '' }}</td>
                <td>{{ $attendance?->total_formatted ?? '' }}</td>
                <td>
                    @if ($attendance)
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id,
        'date' => $currentDate]) }}" class="detail-link">詳細</a>
                    @else
                    <a href="{{ route('admin.attendance.detail', ['user_id' => $user->id, 'date' => $currentDate->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>

    </table>

</div>

@endsection