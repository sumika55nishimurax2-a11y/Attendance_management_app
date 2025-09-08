@extends('layouts.default')

@section('title','スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/list.css')  }}">
@endsection

@section('content')

@include('components.header')
<div class="attendance-wrapper">
    <div class="attendance-title">
        <h1 class="title">{{ $user->name }}さんの勤怠</h1>
    </div>
    <div class="month-nav">
        <a href="{{ route('admin.staffs.attendance', [
            'user_id' => $user->id,
            'year' => $currentDate->copy()->subMonth()->year,
            'month' => $currentDate->copy()->subMonth()->month
        ]) }}" class="prev-month">&larr; 前月</a>
        <div class="current-month"><img src="{{ asset('img/calendar.png') }}" alt="" class="img"><span>{{ $firstDay->format('Y/m') }}</span></div>
        <a href="{{ route('admin.staffs.attendance', [
            'user_id' => $user->id,
            'year' => $currentDate->copy()->addMonth()->year,
            'month' => $currentDate->copy()->addMonth()->month
        ]) }}" class="next-month">翌月 &rarr;</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @for ($date = $firstDay->copy(); $date->lte($lastDay); $date->addDay())
            @php
            $att = $attendances->get($date->format('Y-m-d'));
            @endphp
            <tr>
                <td>{{ $date->format('m/d') }} ({{ $date->isoFormat('ddd') }})</td>
                <td>{{ $att ? $att->clock_in_formatted : '' }}</td>
                <td>{{ $att ? $att->clock_out_formatted : '' }}</td>
                <td>{{ $att ? $att->break_formatted : '' }}</td>
                <td>{{ $att ? $att->total_formatted : '' }}</td>
                <td>
                    @if ($att)
                    <a href="{{ route('admin.attendance.detail', ['id' => $att->id]) }}" class="detail-link">詳細</a>
                    @else
                    <a href=" {{ route('admin.attendance.detail', ['user_id' => $user->id,'date' => $date->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endfor
        </tbody>
    </table>
    <div class="csv-button-area">
        <a href="{{ route('admin.staffs.attendance.csv', [
        'user_id' => $user->id,
        'year' => $currentDate->year,
        'month' => $currentDate->month
    ]) }}" class="csv-button">CSV出力</a>
    </div>
</div>

@endsection