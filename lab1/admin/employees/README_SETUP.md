# Hướng dẫn thiết lập quản lý nhân viên

## Bước 1: Tạo các bảng database

Truy cập vào file `setup_tables.php` trong trình duyệt:
```
http://localhost/webcaphe/lab1/admin/employees/setup_tables.php
```

File này sẽ tự động:
- Tạo bảng `employees` (nếu chưa có)
- Thêm cột `employee_code` và `role` vào bảng employees (nếu chưa có)
- Tạo bảng `work_shifts` (quản lý ca làm việc)
- Tạo bảng `shift_assignments` (phân ca cho nhân viên)
- Tạo bảng `salary_calculations` (tính lương)

## Bước 2: Sử dụng các tính năng

Sau khi chạy setup, bạn có thể sử dụng:
- **Quản lý nhân viên**: Thêm, sửa, xóa nhân viên
- **Quản lý ca làm việc**: Tạo các ca làm việc với giờ và lương/giờ
- **Phân ca**: Gán ca cho nhân viên, check-in/check-out
- **Tính lương**: Tự động tính lương từ ca đã hoàn thành

## Lưu ý

- Nếu gặp lỗi "Table doesn't exist", hãy chạy lại `setup_tables.php`
- Nếu gặp lỗi "Unknown column 'employee_code'", file setup sẽ tự động thêm cột này




