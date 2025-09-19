@extends('layouts.default')

@section('title','申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/request.css')  }}">
@endsection

@section('content')

@include('components.header')
<div class="request-wrapper">
    <div class="request-title">
        <h1 class="title">申請一覧</h1>
    </div>

    <div class="tabs">
        <button class="tab-button active" data-tab="pending">承認待ち</button>
        <button class="tab-button" data-tab="approved">承認済み</button>
    </div>
    <div id="pending" class="tab-panel active">
        <table class="request-table">
            <thead>
                <tr>
                    <th class="left-th">状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th class="right-th">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingRequests as $request)
                <tr>
                    <td class="left-td">{{ $request->status_label }}</td>
                    <td>{{ $request->attendance->user->name }}</td>
                    <td>{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                    <td class="reason">{{$request->reason}}</td>
                    <td>{{ $request->requested_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.stamp_correction_request.show', $request->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="approved" class="tab-panel">
        <table class="request-table">
            <thead>
                <tr>
                    <th class="left-th">状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th class="right-th">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvedRequests as $request)
                <tr>
                    <td class="left-td">{{ $request->status_label }}</td>
                    <td>{{ $request->attendance->user->name }}</td>
                    <td>{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                    <td class="reason">{{ $request->reason }}</td>
                    <td>{{ $request->requested_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.stamp_correction_request.show', $request->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".tab-button");
        const panels = document.querySelectorAll(".tab-panel");

        buttons.forEach(button => {
            button.addEventListener("click", () => {
                // 全部リセット
                buttons.forEach(btn => btn.classList.remove("active"));
                panels.forEach(panel => panel.classList.remove("active"));

                // クリックされたタブをアクティブ化
                button.classList.add("active");
                document.getElementById(button.dataset.tab).classList.add("active");
            });
        });
    });
</script>

@endsection