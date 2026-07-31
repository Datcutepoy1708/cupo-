<div class="tab-pane fade" id="historyOrder" role="tabpanel">
    <div class="content-card">
        <h2 class="content-title">Đơn hàng của tôi</h2>

        @php
            $demoOrders = [
                [
                    'id' => 'DH0231',
                    'date' => '28/07/2026',
                    'total' => '1.250.000',
                    'status' => 'confirmed',
                    'items' => [
                        [
                            'name' => 'Tai nghe Bluetooth XZ200',
                            'image' => 'https://picsum.photos/id/1/200',
                            'qty' => 1,
                            'price' => '750.000',
                        ],
                        [
                            'name' => 'Ốp lưng chống sốc',
                            'image' => 'https://picsum.photos/id/2/200',
                            'qty' => 2,
                            'price' => '150.000',
                        ],
                        [
                            'name' => 'Cáp sạc nhanh Type-C',
                            'image' => 'https://picsum.photos/id/3/200',
                            'qty' => 1,
                            'price' => '200.000',
                        ],
                    ],
                ],
                [
                    'id' => 'DH0228',
                    'date' => '25/07/2026',
                    'total' => '1.590.000',
                    'status' => 'unpaid',
                    'items' => [
                        [
                            'name' => 'Đồng hồ thông minh Fit3',
                            'image' => 'https://picsum.photos/id/4/200',
                            'qty' => 1,
                            'price' => '1.590.000',
                        ],
                    ],
                ],
                [
                    'id' => 'DH0225',
                    'date' => '20/07/2026',
                    'total' => '259.000',
                    'status' => 'completed',
                    'items' => [
                        [
                            'name' => 'Áo thun nam form rộng',
                            'image' => 'https://picsum.photos/id/5/200',
                            'qty' => 1,
                            'price' => '259.000',
                        ],
                    ],
                ],
                [
                    'id' => 'DH0219',
                    'date' => '10/07/2026',
                    'total' => '890.000',
                    'status' => 'processing',
                    'items' => [
                        [
                            'name' => 'Nồi chiên không dầu 5L',
                            'image' => 'https://picsum.photos/id/6/200',
                            'qty' => 1,
                            'price' => '890.000',
                        ],
                    ],
                ],
                [
                    'id' => 'DH0210',
                    'date' => '05/07/2026',
                    'total' => '650.000',
                    'status' => 'returned',
                    'items' => [
                        [
                            'name' => 'Giày sneaker unisex',
                            'image' => 'https://picsum.photos/id/7/200',
                            'qty' => 1,
                            'price' => '650.000',
                        ],
                    ],
                ],
                [
                    'id' => 'DH0204',
                    'date' => '02/07/2026',
                    'total' => '420.000',
                    'status' => 'cancelled',
                    'items' => [
                        [
                            'name' => 'Balo laptop chống nước',
                            'image' => 'https://picsum.photos/id/8/200',
                            'qty' => 1,
                            'price' => '420.000',
                        ],
                    ],
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

                                <div class="order-items">
                                    @foreach ($order['items'] as $item)
                                        <div class="order-item-row">
                                            <img src="{{ $item['image'] ?? 'https://via.placeholder.com/80' }}"
                                                onerror="this.src='https://via.placeholder.com/80'"
                                                alt="{{ $item['name'] }}" class="order-thumb">

                                            <div class="order-info">
                                                <p class="mb-1">{{ $item['name'] }}</p>
                                                <p class="text-muted small mb-0">x{{ $item['qty'] }}</p>
                                            </div>

                                            <div class="order-item-price text-end">
                                                {{ $item['price'] }}₫
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="order-row-footer">
                                    <span class="text-muted small">Ngày đặt: {{ $order['date'] }}</span>
                                    <span class="order-total-label">
                                        Tổng tiền:
                                        <span class="fw-bold" style="color: var(--primary-red);">
                                            {{ $order['total'] }}₫</span>
                                    </span>
                                </div>

                                <div class="order-row-actions">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#orderDetailModal">
                                        Xem chi tiết
                                    </button>
                                    @if ($order['status'] === 'unpaid')
                                        <button class="btn btn-sm btn-save">Thanh toán ngay</button>
                                    @elseif($order['status'] === 'confirmed')
                                        <button class="btn btn-sm btn-outline-danger">Đã nhận được hàng</button>
                                    @elseif($order['status'] === 'completed')
                                        <button class="btn btn-sm btn-outline-secondary">Mua lại</button>
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
