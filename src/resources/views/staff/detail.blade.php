@extends('layouts.default')

@section('title','勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/detail.css')  }}">
@endsection

@section('content')
@include('components.header')
<div class="attendance-detail">
    <div class="detail-title">
        <h1 class="title">勤怠詳細</h1>
    </div>

    @if ($attendance->is_editable)
    <form method="POST" action="{{ route('attendance.detail.update', ['id' => $attendance->id ?? 0]) }}">
        @csrf
        @method('POST')

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="field-flex">
                        <div>{{ $attendance->work_date?->format('Y年') }}</div>
                        <div>{{ $attendance->work_date?->format('n月j日') }}</div>
                    </div>
                    <input type="hidden" name="work_date" value="{{ $attendance->work_date?->format('Y-m-d') }}">
                </td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-flex">
                        <input type="text" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}" class="input time-text" {{ $attendance->is_editable ? '' : 'disabled' }}>
                        <span>〜</span>
                        <input type="text" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}" class="input time-text" {{ $attendance->is_editable ? '' : 'disabled' }}>
                    </div>
                    @error('clock_in')
                    <div class="error">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @php
            $breaks = $attendance->breaks ?? collect();
            @endphp
            @foreach ($attendance->breaks as $i => $break)
            <tr>
                <th>@if ($i === 0)
                    休憩
                    @else
                    休憩{{ $i + 1 }}
                    @endif
                </th>
                <td>
                    <div class="time-flex">
                        <input type="text" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", $break->break_start?->format('H:i')) }}" class="input time-text" {{ $attendance->is_editable ? '' : 'disabled' }}>
                        <span>〜</span>
                        <input type="text" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", $break->break_end?->format('H:i')) }}" class="input time-text" {{ $attendance->is_editable ? '' : 'disabled' }}>
                    </div>
                    @error("breaks.$i.start")
                    <div class="error">{{ $message }}</div>
                    @enderror
                    @error("breaks.$i.end")
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @endforeach

            <tr>
                <th>
                    @if ($breaks->isEmpty())
                    休憩
                    @else
                    休憩{{ $breaks->count() + 1 }}
                    @endif
                </th>
                <td>
                    @php
                    $nextIndex = $breaks->count();
                    @endphp
                    <div class="time-flex">
                        <input type="text" name="breaks[{{ $nextIndex }}][start]"
                            value="{{ old("breaks.$nextIndex.start") }}"
                            class="input time-text" {{ $attendance->is_editable ? '' : 'disabled' }}>
                        <span>〜</span>
                        <input type="text" name="breaks[{{ $nextIndex }}][end]"
                            value="{{ old("breaks.$nextIndex.end") }}"
                            class="input time-text" {{ $attendance->is_editable ? '' : 'disabled' }}>
                    </div>
                    @error("breaks.$nextIndex.start")
                    <div class="error">{{ $message }}</div>
                    @enderror
                    @error("breaks.$nextIndex.end")
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" class="remarks" {{ $attendance->is_editable ? '' : 'disabled' }}>{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="button-wrapper">
            <button type="submit" class="btn-primary">修正</button>
        </div>
    </form>

    @else
    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>
                <div class="field-flex">
                    <div>{{ $attendance->work_date->format('Y年') }}</div>
                    <div>{{ $attendance->work_date->format('n月j日') }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                <div class="field-flex">
                    <div>{{ $attendance->clock_in_formatted }}</div>
                    <div>〜</div>
                    <div> {{ $attendance->clock_out_formatted }}</div>
                </div>
            </td>
        </tr>
        @foreach ($attendance->breaks as $i => $break)
        <tr>
            <th>@if ($i === 0) 休憩 @else 休憩{{ $i + 1 }} @endif</th>
            <td>
                <div class="field-flex">
                    <div>{{ $break->break_start?->format('H:i') }}</div>
                    <div>〜</div>
                    <div> {{ $break->break_end?->format('H:i') }}</div>
                </div>
            </td>
        </tr>
        @endforeach
        <tr>
            <th>備考</th>
            <td>{{ $attendance->note }}</td>
        </tr>
    </table>
    <div class="button-wrapper">
        <p class="message">＊承認待ちのため修正はできません</p>
    </div>
    @endif
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.time-text').forEach(function(input) {
            input.addEventListener('input', function(e) {
                let val = e.target.value.replace(/[^0-9]/g, '');
                if (val.length >= 3) {
                    e.target.value = val.slice(0, 2) + ':' + val.slice(2, 4);
                } else {
                    e.target.value = val;
                }
            });
        });
        document.querySelectorAll('.remarks').forEach(function(textarea) {
            textarea.addEventListener('focus', function(e) {
                setTimeout(() => {
                    this.setSelectionRange(0, 0);
                }, 0);
            });
        });
    });
</script>

@endsection