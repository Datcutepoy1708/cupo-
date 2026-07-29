# 1 Khái niệm về migration 
Migration = viết cấu trúc database bằng code, để nó có thể được chia sẻ, theo dõi lịch sử, và tái tạo lại tự động trên bất kỳ máy nào — thay vì phải tạo bảng bằng tay từng người từng máy một.

# Cách phân biệt
Cascade = "con chết theo cha" (như tế bào con phụ thuộc hoàn toàn vào tế bào mẹ)
Restrict = "cấm giết cha khi còn con" (như luật không cho ly hôn khi con còn nhỏ — phải giải quyết ổn thoả trước)
Null on delete = "con mồ côi vẫn sống, chỉ mất tên cha trong giấy khai sinh" (con vẫn tồn tại độc lập, chỉ thiếu 1 thông tin tham chiếu)

# RBAC phân quyền
composer require spatie/laravel-permission 
Đây là 1 package (thư viện) có sẵn, chuyên dùng để làm RBAC (phân quyền theo vai trò) cho Laravel — đúng như đã ghi trong schema Word của bạn ở mục "Phân quyền Admin (Spatie Laravel-Permission Style)". Thay vì bạn tự viết tay các bảng roles, permissions , package này viết sẵn toàn bộ, chỉ cần cài vào là dùng được

