@extends('layouts.client.app')

@section('content')
    <div class="shop-page">
        <div class="container">
            <div class="shop-status-card status-blocked">
                <i class="fa-solid fa-ban"></i>
                <h2>Gian hàng đã bị khóa</h2>
                <p>
                    Gian hàng <strong>"{{ $shop->name }}"</strong> đã bị tạm khóa do vi phạm chính sách bán hàng của Cupo.
                </p>
                @if ($shop->block_reason)
                    <div class="reject-reason">{{ $shop->block_reason }}</div>
                @endif
                <p class="text-muted small">
                    Nếu bạn cho rằng đây là nhầm lẫn, vui lòng liên hệ bộ phận hỗ trợ người bán để được xem xét lại.
                </p>
                <a href="{{ route('help') }}" class="btn btn-outline-secondary me-2">Liên hệ hỗ trợ</a>
                <a href="{{ route('home') }}" class="btn btn-save">Quay về trang chủ</a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shop-blocked.css') }}">
@endpush
