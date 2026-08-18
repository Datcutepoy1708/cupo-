@extends('layouts.client.app')
@push('styles')
    <link href="{{ asset('client/css/home.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('client.home.partials.hero-slider')
    @include('client.home.partials.featured-categories')
    @include('client.home.partials.flash-sale')
    @include('client.home.partials.top-search')
    @include('client.home.partials.suggest-products')
@endsection
