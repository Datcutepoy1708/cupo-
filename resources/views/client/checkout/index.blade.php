@extends('layouts.client.app')
@push('styles')
    <link href="{{ asset('client/css/checkout.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="container py-4">
        <div class="checkout-shell">
            @include('client.checkout.partials.main-card')
            @include('client.checkout.partials.summary')
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('client/js/checkout.js') }}"></script>
@endpush
