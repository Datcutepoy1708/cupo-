<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb prod-breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Tất cả danh mục</a></li>
        @if ($product->category)
            @if ($product->category->parent)
                <li class="breadcrumb-item"><a
                        href="{{ url('/categories/' . $product->category->parent->slug) }}">{{ $product->category->parent->name }}</a>
                </li>
            @endif
            <li class="breadcrumb-item"><a
                    href="{{ url('/categories/' . $product->category->slug) }}">{{ $product->category->name }}</a>
            </li>
        @endif
        <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page">
            {{ $product->name }}</li>
    </ol>
</nav>
