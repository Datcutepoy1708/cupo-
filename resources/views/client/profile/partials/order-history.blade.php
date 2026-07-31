<div class="tab-pane fade" id="historyOrder" role="tabpanel">
    <div class="content-card">
        <h2 class="content-title">Đơn hàng của tôi</h2>

        @php
            $demoOrders = [
                [
                    'id' => 'DH0231',
                    'name' => 'Tai nghe Bluetooth XZ200 (+2 SP khác)',
                    'date' => '28/07/2026',
                    'total' => '1.250.000',
                    'status' => 'confirmed',
                ],
                [
                    'id' => 'DH0228',
                    'name' => 'Đồng hồ thông minh Fit3',
                    'date' => '25/07/2026',
                    'total' => '1.590.000',
                    'status' => 'unpaid',
                ],
                [
                    'id' => 'DH0225',
                    'name' => 'Áo thun nam form rộng',
                    'date' => '20/07/2026',
                    'total' => '259.000',
                    'status' => 'completed',
                ],
                [
                    'id' => 'DH0219',
                    'name' => 'Nồi chiên không dầu 5L',
                    'date' => '10/07/2026',
                    'total' => '890.000',
                    'status' => 'processing',
                ],
                [
                    'id' => 'DH0210',
                    'name' => 'Giày sneaker unisex',
                    'date' => '05/07/2026',
                    'total' => '650.000',
                    'status' => 'returned',
                ],
                [
                    'id' => 'DH0204',
                    'name' => 'Balo laptop chống nước',
                    'date' => '02/07/2026',
                    'total' => '420.000',
                    'status' => 'cancelled',
                ],
            ];

            $statusMeta = [
                'unpaid' => ['label' => 'Chờ thanh toán', 'badge' => 'bg-warning text-dark'],
                'processing' => ['label' => 'Đang xử lý', 'badge' => 'bg-info text-dark'],
                'confirmed' => ['label' => 'Đang giao', 'badge' => 'bg-success'],
                'completed' => ['label' => 'Hoàn thành', 'badge' => 'bg-primary'],
                'cancelled' => ['label' => 'Đã hủy', 'badge' => 'bg-danger'],
                'returned' => ['label' => 'Trả hàng/Hoàn tiền', 'badge' => 'bg-secondary'],
            ];

            $orderFilters = [
                'all' => 'Tất cả',
                'unpaid' => 'Chờ thanh toán',
                'processing' => 'Đang xử lý',
                'confirmed' => 'Đang giao',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy',
                'returned' => 'Trả hàng/Hoàn tiền',
            ];
        @endphp

        {{-- Tabs trạng thái kiểu Shopee --}}
        <ul class="nav order-status-tabs mb-4" role="tablist">
            @foreach ($orderFilters as $key => $label)
                @php
                    $count =
                        $key === 'all'
                            ? count($demoOrders)
                            : count(array_filter($demoOrders, fn($o) => $o['status'] === $key));
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ $key === 'all' ? 'active' : '' }}" data-bs-toggle="pill"
                        href="#orderStatus-{{ $key }}" role="tab">
                        {{ $label }}
                        @if ($count > 0)
                            <span class="status-count">{{ $count }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach ($orderFilters as $key => $label)
                @php
                    $filteredOrders =
                        $key === 'all' ? $demoOrders : array_filter($demoOrders, fn($o) => $o['status'] === $key);
                @endphp
                <div class="tab-pane fade {{ $key === 'all' ? 'show active' : '' }}"
                    id="orderStatus-{{ $key }}" role="tabpanel">

                    @if (count($filteredOrders) > 0)
                        @foreach ($filteredOrders as $order)
                            <div class="order-row">
                                <div class="order-row-top">
                                    <span class="order-id">Đơn hàng #{{ $order['id'] }}</span>
                                    <span class="badge {{ $statusMeta[$order['status']]['badge'] }}">
                                        {{ $statusMeta[$order['status']]['label'] }}
                                    </span>
                                </div>
                                <div class="order-row-body">
                                    <div class="order-info">
                                        <p class="mb-1">{{ $order['name'] }}</p>
                                        <p class="text-muted small mb-0">Ngày đặt:
                                            {{ $order['date'] }}</p>
                                    </div>
                                    <div class="order-total text-end">
                                        <p class="text-muted small mb-1">Tổng tiền</p>
                                        <p class="fw-bold mb-0" style="color: var(--primary-red);">
                                            {{ $order['total'] }}₫</p>
                                    </div>
                                </div>
                                <div class="order-row-actions">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#orderDetailModal">
                                        Xem chi tiết
                                    </button>
                                    @if ($order['status'] === 'unpaid')
                                        <button class="btn btn-sm btn-save">Thanh toán
                                            ngay</button>
                                    @elseif($order['status'] === 'confirmed')
                                        <button class="btn btn-sm btn-outline-danger">Đã nhận được
                                            hàng</button>
                                    @elseif($order['status'] === 'completed')
                                        <button class="btn btn-sm btn-outline-secondary">Mua
                                            lại</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="fa-solid fa-inbox"></i>
                            <p class="mb-0">Không có đơn hàng nào ở trạng thái này</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
