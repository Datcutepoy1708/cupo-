<div class="tab-pane fade" id="historyWishlist" role="tabpanel">
    <div class="content-card">
        <h2 class="content-title">Sản phẩm yêu thích</h2>

        <div class="row g-3">
            @for ($i = 0; $i < 4; $i++)
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm wishlist-card">
                        <button class="btn-remove-wishlist"><i class="fa-solid fa-heart"></i></button>
                        <img src="{{ asset('https://picsum.photos/1600/700') }}" class="card-img-top" alt="Sản phẩm">
                        <div class="card-body">
                            <p class="small mb-1 text-truncate">Sản phẩm yêu thích
                                {{ $i + 1 }}</p>
                            <p class="fw-bold mb-0" style="color: var(--primary-red);">
                                {{ number_format(199000 + $i * 60000) }}₫</p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
