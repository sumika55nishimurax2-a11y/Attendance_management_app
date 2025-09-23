@extends('layouts.default')

@section('title','メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/verify.css') }}">
@endsection

@section('content')

<div class="verification-container-wrapper">
    <div class="verification-container">
        <h1 class="verification-title">メールアドレス認証</h1>
        <p class="verification-text">
            登録していただいたメールアドレスの認証を行います。<br>
            下のボタンをクリックすると認証が完了します。
        </p>

        <form method="POST" action="{{ route('verification.perform') }}">
            @csrf
            <button type="submit" class="auth-button">メールアドレスを認証する</button>
        </form>
    </div>
</div>
@endsection