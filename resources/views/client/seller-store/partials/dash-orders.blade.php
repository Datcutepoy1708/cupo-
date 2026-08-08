<div class="tab-pane fade" id="dashOrders" role="tabpanel">
    <div class="dash-section-title">Đơn hàng cần xử lý</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->pendingOrdersList ?? [] as $sellerOrder)
                    <tr>
                        <td>#{{ $sellerOrder->order->order_number ?? $sellerOrder->order_id }}</td>
                        <td>{{ $sellerOrder->order->user->name ?? 'Khách vãng lai' }}</td>
                        <td>
                            @php $itemNames = $sellerOrder->items->pluck('product_name'); @endphp
                            @if ($itemNames->isNotEmpty())
                                {{ $itemNames->first() }}
                                @if ($itemNames->count() > 1)
                                    (+{{ $itemNames->count() - 1 }} SP)
                                @endif
                            @endif
                        </td>
                        <td>{{ number_format($sellerOrder->grand_total) }}₫</td>
                        <td><span class="badge bg-warning text-dark">Chờ xác nhận</span></td>
                        <td>
                            <form method="post" action="{{ route('seller.orders.update-status', $sellerOrder) }}"
                                class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-sm btn-save">Xác nhận</button>
                            </form>
                            <form method="post" action="{{ route('seller.orders.update-status', $sellerOrder) }}"
                                class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Từ
                                    chối</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Không có đơn hàng cần xử lý
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
