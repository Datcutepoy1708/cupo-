#  Hướng dẫn Git & Quy tắc làm việc nhóm — Dự án Cupo

 Đọc kỹ trước khi bắt đầu code. Tuân thủ đúng các quy tắc này để tránh conflict và mất code.

---

##  Cấu trúc nhánh (Branch Strategy)

```
main                    ← Nhánh production (KHÔNG được push thẳng)
  └── develop           ← Nhánh tích hợp chung (merge vào đây hàng ngày)
        ├── feature/be-*    ← Nhánh backend (Đạt phụ trách)
        └── feature/fe-*    ← Nhánh frontend (Thành viên FE phụ trách)
```

### Quy tắc đặt tên nhánh:
| Loại | Tên nhánh | Ví dụ |
| :--- | :--- | :--- |
| Tính năng mới | `feature/[be|fe]-tên-tính-năng` | `feature/fe-home-page` |
| Sửa lỗi | `fix/mô-tả-lỗi` | `fix/login-redirect` |
| Hotfix khẩn | `hotfix/mô-tả` | `hotfix/500-error` |

---

##  Quy trình làm việc hàng ngày (BẮT BUỘC)

### Bước 1: Sáng khi bắt đầu làm — PULL code mới nhất về
```bash
git checkout develop
git pull origin develop
git checkout feature/fe-tên-nhánh-của-bạn
git merge develop
```
 **Không bao giờ bắt đầu code mà không pull trước!**

### Bước 2: Tạo nhánh mới cho từng tính năng
```bash
# Ví dụ: làm trang chủ Storefront
git checkout develop
git pull origin develop
git checkout -b feature/fe-home-page
```

### Bước 3: Commit thường xuyên (mỗi khi xong 1 việc nhỏ)
```bash
git add .
git commit -m "feat: hoàn thiện layout header trang chủ"
git push origin feature/fe-home-page
```

### Bước 4: Khi xong tính năng — Tạo Pull Request vào develop
1. Lên GitHub → Nhấn **"New Pull Request"**
2. Base: `develop` ← Compare: `feature/fe-home-page`
3. Mô tả rõ bạn đã làm gì trong Pull Request
4. **Ping bạn BE** để review và approve trước khi merge

---

##  Quy tắc viết Commit Message

Theo chuẩn **Conventional Commits**:

```
<type>: <mô tả ngắn gọn>
```

| Type | Ý nghĩa | Ví dụ |
| :--- | :--- | :--- |
| `feat` | Thêm tính năng mới | `feat: thêm banner flash sale trang chủ` |
| `fix` | Sửa lỗi | `fix: sửa lỗi hiển thị giá sản phẩm` |
| `style` | Chỉnh CSS/UI (không ảnh hưởng logic) | `style: căn giữa footer mobile` |
| `refactor` | Tái cấu trúc code | `refactor: tách component ProductCard` |
| `chore` | Việc lặt vặt (update package...) | `chore: npm install tailwind` |

---

##  Phân chia File — Ai làm gì để tránh Conflict

###  Backend (BE) phụ trách
```
app/Http/Controllers/     ← Logic xử lý
app/Models/               ← Model Eloquent
routes/storefront.php     ← Route trang mua sắm
routes/seller.php         ← Route khu vực Seller
routes/admin.php          ← Route khu vực Admin
routes/customer.php       ← Route khu vực Customer
database/migrations/      ← Migration database
```

###  Frontend (FE) phụ trách
```
resources/views/          ← Toàn bộ giao diện Blade
resources/css/            ← File CSS tùy chỉnh
public/                   ← Ảnh, icon, assets tĩnh
```

###  File dùng chung — CẦN THÔNG BÁO TRƯỚC KHI SỬA
```
resources/views/layouts/app.blade.php        ← Layout chính
resources/views/layouts/navigation.blade.php ← Thanh điều hướng
resources/views/components/                  ← Components dùng chung
```
 **Ping nhau trên Zalo/nhóm chat trước khi sửa các file này!**

---

##  Những điều TUYỆT ĐỐI KHÔNG làm

```
 Push thẳng vào nhánh main hoặc develop (không qua PR)
 Force push (git push --force) lên nhánh chung
 Commit file .env (chứa thông tin database, secret key)
 Commit thư mục node_modules/ hoặc vendor/
 Để nhánh feature sống quá 3 ngày mà không merge
 Bắt đầu code mà không pull code mới nhất về trước
```

---

##  Xử lý khi bị Conflict

Nếu sau khi `git merge develop` bị báo conflict:

```bash
# Bước 1: Xem file nào đang conflict
git status

# Bước 2: Mở file đó trong VS Code
# Tìm các dấu hiệu conflict:
# <<<<<<< HEAD       ← Code của bạn
# =======
# >>>>>>> develop    ← Code của người kia
# Xóa các dấu <<< === >>> và giữ lại code đúng

# Bước 3: Sau khi sửa xong
git add .
git commit -m "fix: resolve merge conflict tại navigation.blade.php"
```

>  **Mẹo:** Dùng VS Code để giải quyết conflict dễ hơn — nó sẽ hiển thị nút **"Accept Current"**, **"Accept Incoming"**, **"Accept Both"** trực quan.

---

##  Khi có vấn đề

Trước khi làm bất cứ điều gì không chắc → **Hỏi trước, code sau!**
Thà mất 5 phút hỏi còn hơn mất 2 tiếng fix conflict. 
