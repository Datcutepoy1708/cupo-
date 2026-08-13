@extends('layouts.client.app')

@section('page-title', 'Tất Cả Danh Mục Sản Phẩm — Cupo')

@push('styles')
    <link href="{{ asset('client/css/category-show.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="cat-page-bg py-4">
    <div class="container">

        {{-- Top Category Banner --}}
        @if (isset($categoryBanner) && $categoryBanner)
            <div class="cat-top-banner mb-4">
                <a href="{{ $categoryBanner->link_url ?: '#' }}">
                    <img src="{{ Str::contains($categoryBanner->image_path, 'http') ? $categoryBanner->image_path : asset('storage/' . $categoryBanner->image_path) }}"
                         alt="{{ $categoryBanner->title }}" class="img-fluid rounded-3 w-100 shadow-sm" style="max-height: 200px; object-fit: cover;">
                </a>
            </div>
        @endif

        <h1 class="h4 fw-bold text-dark mb-4">
            <i class="fa-solid fa-layer-group text-danger me-2"></i>Tất Cả Danh Mục Hàng Hóa
        </h1>

        <div class="row g-4">
            @foreach ($categories as $root)
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 p-3 rounded-3" style="background:#fff;">

                        {{-- Header danh mục gốc --}}
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <a href="{{ url('/categories/' . $root->slug) }}" class="fw-bold text-dark text-decoration-none h5 mb-0 hover-red">
                                {{ $root->name }}
                            </a>
                            <span class="badge bg-danger rounded-pill">{{ $root->products_count ?? 0 }} SP</span>
                        </div>

                        {{-- Danh sách con --}}
                        @if ($root->children && $root->children->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($root->children as $child)
                                    <a href="{{ url('/categories/' . $child->slug) }}"
                                       class="btn btn-sm btn-light border text-dark text-decoration-none" style="font-size: 12px;">
                                        {{ $child->name }}
                                        <span class="text-muted ms-1">({{ $child->products_count ?? 0 }})</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-0">Chưa có danh mục con.</p>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
