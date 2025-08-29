@extends('layouts.default')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/list.css')  }}">
@endsection

@section('content')

@include('components.header')
<div class="attendance-wrapper">
    <div class="attendance-title">
        <h1 class="title">勤怠一覧</h1>
    </div>

    <div class="month-nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="prev-month">&larr; 前月</a>
        <div class="current-month"><img src="{{ asset('img/calendar.png') }}" alt="" class="img"><span>{{ $firstDay->format('Y/m') }}</span></div>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="next-month">翌月 &rarr;</a>
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
                <td>{{ $att ? $att->break_formatted    : '' }}</td>
                <td>{{ $att ? $att->total_formatted    : '' }}</td>
                <td>
                    @if ($att)
                    <a href="{{ route('attendance.detail', ['id' => $att->id]) }}" class="detail-link">詳細</a>
                    @else
                    <a href="{{ route('attendance.detail', ['date' => $date->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endfor
        </tbody>
    </table>

</div>

@endsection