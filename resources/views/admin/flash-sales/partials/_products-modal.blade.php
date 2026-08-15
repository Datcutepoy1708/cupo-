<!-- Modal Quan ly San pham trong Phiên Flash Sale -->
<div class="modal fade" id="flashSaleProductsModal" tabindex="-1" aria-labelledby="flashSaleProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <div>
                    <h5 class="modal-header-title mb-1" id="flashSaleProductsModalLabel">Quản lý Sản phẩm Flash Sale</h5>
                    <span class="text-muted small" id="currentSessionTitle"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Selector & Search combo --}}
                <div class="card mb-3 border-0 bg-light">
                    <div class="card-body p-3">
                        <label class="form-label font-weight-bold mb-2">Thêm sản phẩm vào phiên</label>
                        <div class="input-group">
                            <select class="form-select" id="selectProductToAdd">
                                <option value="">-- Chọn sản phẩm đã duyệt --</option>
                                @foreach($availableProducts as $prod)
                                    <option value="{{ $prod->id }}" 
                                            data-name="{{ $prod->name }}" 
                                            data-price="{{ $prod->price }}" 
                                            data-stock="{{ $prod->stock }}"
                                            data-thumbnail="{{ asset('storage/' . $prod->thumbnail) }}">
                                        {{ $prod->name }} - GIÁ: {{ number_format($prod->price, 0, ',', '.') }}đ (Tồn: {{ $prod->stock }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="btnAddProductToTable">
                                <i class="fa-solid fa-plus me-1"></i> Thêm vào danh sách
                            </button>
                        </div>
                        <div class="form-text mt-1 text-muted">Lưu ý: Giá Flash Sale tối đa $\le 90\%$ giá gốc sản phẩm.</div>
                    </div>
                </div>

                {{-- Table for selected products --}}
                <form id="flashSaleProductsForm">
                    @csrf
                    <input type="hidden" id="productsFormAction" value="">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="flashSaleProductsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%">Sản phẩm</th>
                                    <th style="width: 25%">Giá gốc / Tồn kho</th>
                                    <th style="width: 20%">Giá Flash Sale (VNĐ)</th>
                                    <th style="width: 15%">Số lượng bán</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="flashSaleProductsTableBody">
                                <tr id="emptyProductsRow">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                        Chưa có sản phẩm nào được chọn trong phiên này.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btnSaveSyncProducts">
                    <i class="fa-solid fa-check me-1"></i> Đồng bộ danh sách sản phẩm
                </button>
            </div>
        </div>
    </div>
</div>
