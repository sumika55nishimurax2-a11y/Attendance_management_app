@extends('layouts.default')

@section('title','修正申請詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/detail.css')  }}">
@endsection

@section('content')
@include('components.header')
<div class="attendance-detail">
    <div class="detail-title">
        <h1 class="title">勤怠詳細</h1>
    </div>

    <form method="POST" action="{{ route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $correctionRequest->id]) }}">
        @csrf

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    @php
                    $displayDate = $attendance->work_date instanceof \Carbon\Carbon
                    ? $attendance->work_date
                    : \Carbon\Carbon::parse($attendance->work_date);
                    @endphp
                    {{ $displayDate->format('Y年n月j日') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ optional($attendance->clock_in)->format('H:i') ?? '-' }}
                    〜
                    {{ optional($attendance->clock_out)->format('H:i') ?? '-' }}
                </td>
            </tr>

            @foreach ($attendance->breaks as $i => $break)
            <tr>
                <th>@if ($i === 0)
                    休憩
                    @else
                    休憩{{ $i + 1 }}
                    @endif
                </th>
                <td>
                    {{ $break->break_start?->format('H:i') ?? '-' }}
                    〜
                    {{ $break->break_end?->format('H:i') ?? '-' }}
                </td>
            </tr>
            @endforeach

            <tr>
                <th>
                    @if($attendance->breaks->count() === 0)
                    休憩
                    @elseif($attendance->breaks->count() === 1)
                    休憩2
                    @else
                    休憩{{ $attendance->breaks->count() + 1 }}
                    @endif
                </th>
                <td></td>
            </tr>

            <tr>
                <th>備考</th>
                <td>{{ $attendance->note ?? 'なし' }}</td>
            </tr>
        </table>
    </form>
    @if($correctionRequest->status === 'approved')
    <div class="button-wrapper">
        <button type="button" class="fixed-button" disabled>承認済み</button>
    </div>
    @else
    <form method="POST" action="{{ route('admin.stamp_correction_request.approve', ['attendance_correction_request' => $correctionRequest->id]) }}" class="button-wrapper">
        @csrf
        <button type="submit" class="btn-primary">承認</button>
    </form>
    @endif

</div>
@endsection