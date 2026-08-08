<div class="tab-pane fade show active" id="dashOverview" role="tabpanel">
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="dash-stat-card">
                <i class="fa-solid fa-sack-dollar"></i>
                <h4>{{ number_format($shop->revenue_month) }}₫</h4>
                <p>Doanh thu tháng này</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-stat-card">
                <i class="fa-solid fa-cart-shopping"></i>
                <h4>{{ $shop->pending_orders }}</h4>
                <p>Đơn cần xử lý</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-stat-card">
                <i class="fa-solid fa-box"></i>
                <h4>{{ $shop->product_count }}</h4>
                <p>Sản phẩm đang bán</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-stat-card">
                <i class="fa-solid fa-star"></i>
                <h4>{{ number_format($shop->rating, 1) }}/5</h4>
                <p>Đánh giá trung bình</p>
            </div>
        </div>
    </div>

    <div class="dash-section-title">Đơn hàng gần đây</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->recentOrders ?? [] as $sellerOrder)
                    <tr>
                        <td>#{{ $sellerOrder->order->order_number ?? $sellerOrder->order_id }}</td>
                        <td>{{ $sellerOrder->order->user->name ?? 'Khách vãng lai' }}</td>
                        <td>{{ number_format($sellerOrder->grand_total) }}₫</td>
                        <td>
                            @switch($sellerOrder->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                @break

                                @case('shipping')
                                    <span class="badge bg-success">Đang giao</span>
                                @break

                                @case('completed')
                                    <span class="badge bg-primary">Hoàn thành</span>
                                @break

                                @default
                                    <span class="badge bg-secondary">{{ $sellerOrder->status }}</span>
                            @endswitch
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Chưa có đơn hàng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
