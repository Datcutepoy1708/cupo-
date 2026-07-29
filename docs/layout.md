# Cấu trúc thư mục view

resources/views/
├── layouts/                  # Chứa giao diện khung (Header, Footer, Sidebar)
│   ├── app.blade.php         # Khung chung cho trang khách hàng (Storefront)
│   └── seller.blade.php      # Khung chung cho trang Seller (nếu tự viết HTML)
│
├── storefront/               # Giao diện trang bán hàng (cho khách hàng)
│   ├── home.blade.php        # Trang chủ
│   ├── products/
│   │   ├── index.blade.php   # Trang danh sách sản phẩm (Search & Filter)
│   │   └── show.blade.php    # Trang chi tiết sản phẩm + Đánh giá (Review)
│   ├── cart.blade.php        # Giao diện giỏ hàng
│   ├── checkout.blade.php    # Trang thanh toán (chọn VNPay/Momo/COD)
│   └── chat.blade.php        # Giao diện khung chat real-time với Seller
│
└── auth/                     # Các trang Đăng nhập / Đăng ký
    ├── login.blade.php
    └── register.blade.php
