@extends('layouts.client.app')

@section('page-title', 'Tất Cả Danh Mục Sản Phẩm — Cupo')

@push('styles')
    <link href="{{ asset('client/css/category-show.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="cat-page-bg">
        <div class="container py-4">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb cat-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tất cả danh mục</li>
                </ol>
            </nav>

            {{-- Header Tiêu Đề --}}
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-layer-group me-2 text-danger"></i>Tất Cả Danh Mục Sản Phẩm
                </h4>
                <span class="text-muted small">Hiển thị {{ $categories->count() }} danh mục chính</span>
            </div>

            @php
                // Chia danh mục thành 3 cột cố định độc lập
                $columns = [[], [], []];
                foreach ($categories as $index => $root) {
                    $columns[$index % 3][] = $root;
                }
            @endphp

            {{-- 3 Cột Flexbox Độc Lập --}}
            <div class="row g-4 align-items-start">
                @foreach ($columns as $colIndex => $colCategories)
                    <div class="col-lg-4 col-md-6 d-flex flex-column gap-4">
                        @foreach ($colCategories as $root)
                            <div class="card border-0 shadow-sm p-3 rounded-3 cat-card-item" style="background:#fff;">

                                {{-- Header danh mục gốc --}}
                                <div
                                    class="d-flex align-items-center justify-content-between pb-3 mb-2 border-bottom cat-root-header">
                                    <a href="{{ url('/categories/' . $root->slug) }}"
                                        class="fw-bold text-dark text-decoration-none h5 mb-0 hover-red">
                                        {{ $root->name }}
                                    </a>

                                    <span class="badge bg-danger rounded-pill px-3 py-2">{{ $root->products_count ?? 0 }}
                                        SP</span>
                                </div>

                                {{-- Dropdown danh mục con (Chỉ đẩy các phần tử BÊN DƯỚI thuộc cùng 1 cột) --}}
                                <div class="pt-2">
                                    <button
                                        class="btn btn-outline-danger btn-sm w-100 dropdown-toggle d-flex align-items-center justify-content-between px-3 py-2 rounded-2 cat-collapse-btn"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#catCollapse{{ $root->id }}" aria-expanded="false"
                                        aria-controls="catCollapse{{ $root->id }}">
                                        <span>
                                            <i class="fa-solid fa-list-ul me-2"></i>Danh mục con
                                            ({{ $root->children ? $root->children->count() : 0 }})
                                        </span>
                                    </button>

                                    <div class="collapse mt-2" id="catCollapse{{ $root->id }}">
                                        <div class="card card-body border-0 bg-light p-2 rounded-2">
                                            @if ($root->children && $root->children->isNotEmpty())
                                                <ul class="cat-dropdown-list mb-0">
                                                    @foreach ($root->children as $child)
                                                        <li>
                                                            <a class="cat-dropdown-link d-flex align-items-center justify-content-between py-2 px-3 text-decoration-none"
                                                                href="{{ url('/categories/' . $child->slug) }}">
                                                                <span>
                                                                    <i
                                                                        class="fa-solid fa-angle-right me-2 text-danger small"></i>
                                                                    {{ $child->name }}
                                                                </span>
                                                                <span
                                                                    class="badge bg-white text-secondary border rounded-pill small">
                                                                    {{ $child->products_count ?? 0 }} SP
                                                                </span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div class="text-center text-muted small py-2">
                                                    <i class="fa-solid fa-circle-info me-1"></i>Không có danh mục con
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection
