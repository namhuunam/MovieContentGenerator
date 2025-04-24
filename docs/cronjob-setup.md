# Hướng dẫn thiết lập Cron Job cho Movie Content Generator

Để chạy package Movie Content Generator tự động theo lịch trình định kỳ (ví dụ: mỗi 15 phút), bạn có thể sử dụng cron job trên hệ thống Ubuntu hoặc các hệ điều hành Linux khác.

## Cài đặt Cron Job

### Bước 1: Mở trình soạn thảo crontab

Đăng nhập vào terminal của Ubuntu và chạy lệnh sau:

```bash
crontab -e
```

Nếu đây là lần đầu tiên bạn sử dụng crontab, hệ thống có thể yêu cầu bạn chọn trình soạn thảo (nano, vim, v.v.).

### Bước 2: Thêm lệnh cron

Thêm dòng sau vào file crontab để chạy lệnh mỗi 15 phút:

```
*/15 * * * * cd /đường/dẫn/đến/dự/án/laravel && php artisan movie:generate-content >> /đường/dẫn/đến/dự/án/laravel/storage/logs/cron.log 2>&1
```

Thay `/đường/dẫn/đến/dự/án/laravel` bằng đường dẫn thực tế đến thư mục gốc của dự án Laravel của bạn.

### Bước 3: Lưu và thoát

Sau khi thêm dòng lệnh, lưu file và thoát khỏi trình soạn thảo:
- Nếu sử dụng nano: Nhấn `Ctrl + X`, sau đó nhấn `Y` và `Enter` để xác nhận lưu.
- Nếu sử dụng vim: Nhấn `Esc`, sau đó gõ `:wq` và nhấn `Enter`.

### Bước 4: Xác minh cron job đã được thiết lập

Để xem danh sách cron job đã được cài đặt, chạy lệnh:

```bash
crontab -l
```

## Giải thích cú pháp cron

Cú pháp `*/15 * * * *` có ý nghĩa như sau:
- `*/15`: Chạy mỗi 15 phút
- Năm dấu sao đại diện cho: phút (0-59), giờ (0-23), ngày trong tháng (1-31), tháng (1-12), ngày trong tuần (0-7)

## Tùy chỉnh thời gian chạy

- Chạy mỗi 5 phút: `*/5 * * * *`
- Chạy mỗi 30 phút: `*/30 * * * *`
- Chạy mỗi giờ: `0 * * * *`
- Chạy mỗi ngày lúc 2 giờ sáng: `0 2 * * *`

## Xem log

Log của MovieContentGenerator sẽ được lưu tại:

```
/đường/dẫn/đến/dự/án/laravel/storage/logs/MovieContentGenerator.log
```

Log của cron job sẽ được lưu tại:

```
/đường/dẫn/đến/dự/án/laravel/storage/logs/cron.log
```

## Gỡ lỗi

Nếu cron job không hoạt động như mong đợi:

1. Đảm bảo người dùng chạy cron có đủ quyền để thực thi các lệnh
2. Sử dụng đường dẫn tuyệt đối cho tất cả các file và lệnh
3. Kiểm tra log tại file được chỉ định trong cron job

## Chú ý

- Đảm bảo server của bạn có đủ tài nguyên để xử lý các yêu cầu mỗi 15 phút
- Nếu API Gemini có giới hạn số lần gọi, hãy điều chỉnh thời gian chạy cron job phù hợp
- Đặt giá trị `--limit` phù hợp để đảm bảo quá trình xử lý hoàn tất trong khoảng thời gian giữa các lần chạy cron