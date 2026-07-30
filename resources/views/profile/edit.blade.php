@extends('layouts.client.app')

@section('content')
<div class="auth-wrapper">
    <div class="container my-4" style="max-width: 800px;">
        <h2 class="mb-4 text-center font-weight-bold" style="color: #212529;">Quản lý tài khoản</h2>

        <div class="card auth-card mb-4" style="max-width: 100%;">
            <div class="card-body auth-card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card auth-card mb-4" style="max-width: 100%;">
            <div class="card-body auth-card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card auth-card mb-4" style="max-width: 100%;">
            <div class="card-body auth-card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
