@extends('layouts.default')

@section('title','スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/staff-list.css')  }}">
@endsection

@section('content')
@include('components.header')
<div class="attendance-wrapper">
    <div class="attendance-title">
        <h1 class="title">スタッフ一覧</h1>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="left">名前</th>
                <th>メールアドレス</th>
                <th class="right">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td class="left">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td class="right"><a class="detail-link" href="{{ route('admin.staffs.attendance', ['user_id' => $user->id]) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>


</div>
@endsection