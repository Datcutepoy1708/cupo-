<div class="tab-pane fade {{ $activeTab === 'historyOrder' ? 'show active' : '' }}" id="historyOrder" role="tabpanel">
    <div class="content-card">
        <h2 class="content-title">Đơn hàng của tôi</h2>

        @php
            $statusMeta = [
                'unpaid' => ['label' => 'Chờ thanh toán', 'badge' => 'bg-warning text-dark'],
                'pending' => ['label' => 'Chờ xác nhận', 'badge' => 'bg-info text-dark'],
                'confirmed' => ['label' => 'Đang chuẩn bị', 'badge' => 'bg-info text-dark'],
                'shipping' => ['label' => 'Đang giao hàng', 'badge' => 'bg-primary'],
                'completed' => ['label' => 'Hoàn thành', 'badge' => 'bg-success'],
                'cancelled' => ['label' => 'Đã hủy', 'badge' => 'bg-danger'],
            ];

            $orderFilters = [
                'all' => 'Tất cả',
                'unpaid' => 'Chờ thanh toán',
                'pending' => 'Chờ xác nhận',
                'shipping' => 'Đang giao',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy',
            ];
        @endphp

        <ul class="nav order-status-tabs mb-4" role="tablist">
            @foreach ($orderFilters as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $key === 'all' ? 'active' : '' }}" data-bs-toggle="pill"
                        href="#orderStatus-{{ $key }}" role="tab">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach ($orderFilters as $key => $label)
                <div class="tab-pane fade {{ $key === 'all' ? 'show active' : '' }}" id="orderStatus-{{ $key }}"
                    role="tabpanel">

                    @php
                        $filteredOrders = ($key === 'all')
                            ? $orders
                            : $orders->filter(function ($o) use ($key) {
                                if ($key === 'unpaid')
                                    return $o->payment_status === 'unpaid';
                                return $o->sellerOrders->contains('status', $key);
                            });
                    @endphp

                    @if ($filteredOrders->isNotEmpty())
                        @foreach ($filteredOrders as $order)
                            @foreach ($order->sellerOrders as $sellerOrder)
                                @if ($key !== 'all' && $key !== 'unpaid' && $sellerOrder->status !== $key)
                                    @continue
                                @endif

                                <div class="order-row mb-3 border rounded p-3">
                                    <div class="order-row-top d-flex justify-content-between border-bottom pb-2 mb-2">
                                        <span class="order-id fw-bold">
                                            Mã đơn: #{{ $order->order_number }}
                                            <small
                                                class="text-muted ms-2">({{ $sellerOrder->seller->sellerProfile->shop_name ?? $sellerOrder->seller->name }})</small>
                                        </span>
                                        <span class="badge {{ $statusMeta[$sellerOrder->status]['badge'] ?? 'bg-secondary' }}">
                                            {{ $statusMeta[$sellerOrder->status]['label'] ?? $sellerOrder->status }}
                                        </span>
                                    </div>

                                    <div class="order-items">
                                        @foreach ($sellerOrder->items as $item)
                                            <div class="order-item-row d-flex align-items-center py-2 border-bottom">
                                                <img src="{{ asset($item->product_image ?? 'https://via.placeholder.com/80') }}"
                                                    alt="{{ $item->product_name }}" class="order-thumb me-3"
                                                    style="width: 60px; height: 60px; object-fit: cover;">

                                                <div class="order-info flex-grow-1">
                                                    <p class="mb-1 fw-bold">{{ $item->product_name }}</p>
                                                    <p class="text-muted small mb-0">Số lượng: x{{ $item->quantity }}</p>
                                                </div>

                                                <div class="order-item-price text-end font-monospace">
                                                    {{ number_format($item->price) }}₫
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div
                                        class="order-row-footer d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                        <span class="text-muted small">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</span>
                                        <span class="order-total-label">
                                            Tổng tiền đơn shop:
                                            <span class="fw-bold text-danger">{{ number_format($sellerOrder->grand_total) }}₫</span>
                                        </span>
                                    </div>

                                    <div class="order-row-actions text-end mt-2 d-flex justify-content-end gap-2">
                                        <x-chat-button :seller-id="$sellerOrder->seller_id" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-regular fa-comment-dots me-1"></i>Chat với Shop
                                        </x-chat-button>
                                        @if ($order->payment_status === 'unpaid')
                                            <a href="#" class="btn btn-sm btn-danger">Thanh toán ngay</a>
                                        @elseif ($sellerOrder->status === 'completed')
                                            <a href="{{ route('products.reviews.index', $sellerOrder->items->first()->product_id) }}"
                                                class="btn btn-sm btn-outline-secondary">Đánh giá sản phẩm</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    @else
                        <div class="empty-state text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox display-4 d-block mb-2"></i>
                            <p class="mb-0">Không có đơn hàng nào ở trạng thái này</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
