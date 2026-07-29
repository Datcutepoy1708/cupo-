# Cấu trúc thư mục view

resources/views/
│
├── layouts/                          ← Breeze tạo sẵn (sẽ tùy chỉnh)
│   ├── app.blade.php                 ← Khung layout chính (sau đăng nhập)
│   ├── guest.blade.php               ← Khung layout trang Login/Register
│   └── navigation.blade.php          ← Thanh nav mặc định của Breeze
│
├── auth/                             ← Breeze tạo sẵn (sẽ tùy chỉnh)
│   ├── login.blade.php
│   ├── register.blade.php            ← Cần sửa để thêm tùy chọn Seller
│   ├── forgot-password.blade.php
│   └── ...
│
├── components/                       ← Breeze tạo sẵn (component tái sử dụng)
│
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
│  PHẦN NHÓM BẠN TỰ TẠO THÊM ↓
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
│
├── storefront/                       ← Giao diện trang mua sắm (Khách hàng)
│   ├── home.blade.php                ← Trang chủ (Banner, Flash Sale, Sản phẩm nổi bật)
│   ├── products/
│   │   ├── index.blade.php           ← Danh sách + Tìm kiếm + Lọc sản phẩm
│   │   └── show.blade.php            ← Chi tiết sản phẩm + Đánh giá
│   ├── shops/
│   │   └── show.blade.php            ← Trang gian hàng của từng Seller
│   ├── cart.blade.php                ← Giỏ hàng
│   ├── checkout.blade.php            ← Trang thanh toán
│   └── chat.blade.php                ← Chat real-time Buyer-Seller
│
├── customer/                         ← Khu vực cá nhân của Khách hàng
│   ├── orders/
│   │   ├── index.blade.php           ← Lịch sử đơn hàng
│   │   └── show.blade.php            ← Chi tiết đơn hàng
│   └── profile.blade.php             ← Thông tin cá nhân + Địa chỉ
│
├── seller/                           ← Khu vực quản lý của Người bán
│   ├── dashboard.blade.php           ← Tổng quan (Doanh thu, Đơn chờ xử lý)
│   ├── products/
│   │   ├── index.blade.php           ← Danh sách sản phẩm của shop
│   │   ├── create.blade.php          ← Form đăng sản phẩm mới
│   │   └── edit.blade.php            ← Form chỉnh sửa sản phẩm
│   ├── orders/
│   │   ├── index.blade.php           ← Danh sách đơn hàng cần xử lý
│   │   └── show.blade.php            ← Chi tiết đơn hàng (cập nhật trạng thái)
│   ├── coupons/
│   │   └── index.blade.php           ← Quản lý mã giảm giá riêng của shop
│   └── wallet.blade.php              ← Xem số dư ví + Yêu cầu rút tiền
│
└── admin/                            ← Khu vực quản trị của Admin
    ├── dashboard.blade.php           ← Tổng quan hệ thống (Chart doanh thu)
    ├── users/
    │   └── index.blade.php           ← Danh sách Khách hàng + Seller
    ├── sellers/
    │   └── index.blade.php           ← Duyệt/Khóa tài khoản Shop
    ├── products/
    │   └── index.blade.php           ← Duyệt/Gỡ sản phẩm vi phạm
    ├── categories/
    │   └── index.blade.php           ← Quản lý cây danh mục
    ├── orders/
    │   └── index.blade.php           ← Giám sát tổng đơn hàng toàn sàn
    ├── disputes/
    │   └── index.blade.php           ← Xử lý khiếu nại
    ├── flash-sales/
    │   └── index.blade.php           ← Cấu hình Flash Sale
    └── withdrawals/
        └── index.blade.php           ← Duyệt yêu cầu rút tiền của Seller
