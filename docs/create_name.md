#  Quy tắc đặt tên — Dự án Cupo

> Áp dụng cho toàn bộ dự án. Cả BE và FE đều phải tuân theo để code dễ đọc, dễ maintain.

---

## 1. Tổng quan các kiểu đặt tên

| Kiểu | Ký hiệu | Ví dụ |
| :--- | :--- | :--- |
| **camelCase** | Chữ thường, chữ cái đầu từ tiếp theo viết hoa | `userName`, `totalPrice` |
| **PascalCase** | Chữ cái đầu mỗi từ viết hoa | `UserController`, `ProductImage` |
| **snake_case** | Chữ thường, ngăn cách bởi dấu gạch dưới | `user_id`, `created_at` |
| **kebab-case** | Chữ thường, ngăn cách bởi dấu gạch ngang | `home-page`, `product-card` |
| **SCREAMING_SNAKE** | Chữ HOA toàn bộ, dấu gạch dưới | `MAX_RETRY`, `APP_ENV` |

---

## 2. Backend (PHP / Laravel)

###  Class & Interface
- **Quy tắc:** PascalCase
- **Phải có hậu tố** mô tả rõ loại class

```php
//  Đúng
class ProductController {}
class SellerProfileRepository {}
class OrderCreatedEvent {}
interface PaymentGatewayInterface {}

//  Sai
class productcontroller {}
class Seller {}          // Không rõ là Model hay gì
```

| Loại | Hậu tố bắt buộc | Ví dụ |
| :--- | :--- | :--- |
| Controller | `Controller` | `ProductController` |
| Model | Không có hậu tố | `Product`, `SellerProfile` |
| Repository | `Repository` | `ProductRepository` |
| Service | `Service` | `PaymentService` |
| Event | `Event` | `OrderCreatedEvent` |
| Listener | `Listener` | `SendOrderEmailListener` |
| Job | `Job` | `ProcessPaymentJob` |
| Middleware | Middleware | `EnsureSellerApprovedMiddleware` |
| Request | `Request` | `StoreProductRequest` |
| Interface | `Interface` | `PaymentGatewayInterface` |

---

###  Phương thức (Method / Function)
- **Quy tắc:** camelCase
- Đặt tên theo dạng **động từ + danh từ**, mô tả rõ hành động

```php
//  Đúng
public function getProductsByCategory() {}
public function calculateTotalPrice() {}
public function markOrderAsShipped() {}
public function isSellerApproved() {}    // Hàm boolean bắt đầu bằng is/has/can

//  Sai
public function data() {}               // Quá mơ hồ
public function ProductList() {}        // Không dùng PascalCase cho method
public function get_products() {}       // Không dùng snake_case cho method
```

**Tiền tố thông dụng:**
| Tiền tố | Ý nghĩa | Ví dụ |
| :--- | :--- | :--- |
| `get` | Lấy dữ liệu | `getOrderTotal()` |
| `set` | Gán dữ liệu | `setDiscount()` |
| `create` / `store` | Tạo mới | `createSellerProfile()` |
| `update` | Cập nhật | `updateOrderStatus()` |
| `delete` / `destroy` | Xóa | `deleteProduct()` |
| `is` / `has` / `can` | Kiểm tra boolean | `isInStock()`, `canCheckout()` |
| `send` | Gửi thông báo | `sendOrderConfirmationEmail()` |
| `calculate` | Tính toán | `calculateShippingFee()` |

---

###  Biến (Variable)
- **Quy tắc:** camelCase
- Đặt tên có nghĩa, **tránh viết tắt** khó hiểu

```php
//  Đúng
 = Product::all();
 = 0;
 = auth()->check();
 = SellerProfile::find();

//  Sai
 = Product::all();      // Quá ngắn, không rõ nghĩa
 = 0;                  // Viết tắt khó hiểu
 = [];         // Không dùng PascalCase cho biến
```

**Biến đặc biệt:**
```php
// Biến số đếm/index dùng trong vòng lặp ngắn thì OK
for ( = 0;  < count(); ++) {}

// Collection (tập hợp nhiều bản ghi) — dùng số nhiều
 = Product::all();     //  (số nhiều)
 = Order::where(...);    // 

// Một bản ghi đơn — dùng số ít
 = Product::find();  //  (số ít)
 = Order::first();        // 
```

---

###  Database (Migration / Column)
- **Quy tắc:** snake_case cho tên bảng và tên cột
- Tên bảng: **số nhiều**

```php
//  Tên bảng — số nhiều, snake_case
users, products, seller_profiles, order_items, product_images

//  Tên cột — snake_case
user_id, shop_name, created_at, is_default, commission_rate

// Cột khóa ngoại: tên_bảng_số_ít + _id
user_id, product_id, seller_order_id

//  Sai
Users, Product, sellerProfile, orderItems
```

---

###  Hằng số (Constant)
- **Quy tắc:** SCREAMING_SNAKE_CASE

```php
//  Đúng
const MAX_PRODUCT_IMAGES = 10;
const ORDER_STATUS_PENDING = 'pending';
const COMMISSION_RATE_DEFAULT = 5.0;

// Trong file .env và config/ — cũng dùng SCREAMING_SNAKE
APP_URL=http://localhost
DB_CONNECTION=mysql
```

---

###  Route & URL
- **Quy tắc:** kebab-case, **tiếng Anh**, số nhiều

```php
//  Đúng
Route::get('/products', ...)
Route::get('/products/{id}', ...)
Route::get('/seller/flash-sales', ...)
Route::get('/admin/seller-accounts', ...)

//  Sai
Route::get('/sanPham', ...)         // Không dùng tiếng Việt
Route::get('/product_list', ...)    // Không dùng snake_case trong URL
```

**Tên Route (route name):** dùng dấu chấm phân cấp

```php
//  Đúng
Route::get('/products', ...)->name('storefront.products.index');
Route::get('/seller/orders', ...)->name('seller.orders.index');
Route::get('/admin/users', ...)->name('admin.users.index');
```

---

## 3. Frontend (Blade / CSS)

###  File Blade View
- **Quy tắc:** kebab-case, tiếng Anh

```
//  Đúng
home.blade.php
product-detail.blade.php
seller-dashboard.blade.php
flash-sale-list.blade.php

//  Sai
HomePage.blade.php
san_pham.blade.php
```

---

###  CSS Class & ID
- **Quy tắc:** kebab-case (theo chuẩn BEM nếu có thể)

```html
<!--  Đúng -->
<div class="product-card">
<div class="product-card__image">
<div class="product-card__price--discount">
<button id="btn-add-to-cart">

<!--  Sai -->
<div class="productCard">      <!-- Không dùng camelCase trong CSS -->
<div class="product_card">     <!-- Không dùng snake_case trong CSS -->
```

---

###  Biến trong Blade Template
- Biến truyền từ Controller vào View: **camelCase** (theo Laravel convention)

```php
// Controller
return view('storefront.products.index', [
    'products'       => ,       //  Số nhiều
    'featuredProduct' => ,      //  camelCase
    'totalCount'     => 
]);

// Blade
{{ ->count() }}
{{ ->name }}
```

---

## 4. Tóm tắt nhanh — Bảng tra cứu

| Đối tượng | Kiểu đặt tên | Ví dụ |
| :--- | :--- | :--- |
| Class / Model | PascalCase | `SellerProfile` |
| Controller | PascalCase + Controller | `ProductController` |
| Method / Function | camelCase + động từ | `getOrderTotal()` |
| Biến PHP | camelCase | `` |
| Hằng số | SCREAMING_SNAKE | `MAX_RETRY` |
| Tên bảng DB | snake_case số nhiều | `order_items` |
| Tên cột DB | snake_case | `commission_rate` |
| URL / Route | kebab-case | `/flash-sales` |
| Route name | dot.notation | `seller.orders.index` |
| File Blade | kebab-case | `product-detail.blade.php` |
| CSS class/ID | kebab-case | `product-card__price` |
| Biến Blade | camelCase | `\` |

---

>  **Nguyên tắc vàng:** Đặt tên sao cho người đọc lần đầu **không cần hỏi lại** bạn định nghĩa gì.
