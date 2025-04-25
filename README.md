# MovieContentGenerator

Package tự động tạo nội dung cho phim sử dụng API Gemini cho Laravel 8.

## Tính năng

- Tự động kiểm tra và thêm cột `complete` vào bảng `movies` nếu chưa tồn tại
- Tự động xử lý các phim có `complete = 0` và tạo nội dung mới sử dụng API Gemini
- Cập nhật nội dung mới vào cột `content` và đánh dấu `complete = 1`
- Ghi log chi tiết quá trình xử lý
- Tùy chỉnh cấu hình API Gemini (temperature, topP, topK, maxOutputTokens)

## Cài đặt

### Bước 1: Cài đặt package thông qua Composer
```bash
composer config repositories.moviecontentgenerator vcs https://github.com/namhuunam/moviecontentgenerator.git
```
```bash
composer require namhuunam/moviecontentgenerator
```

> **Lưu ý quan trọng:** Dù thư mục dự án có tên là `MovieContentGenerator` (có chữ hoa), khi cài đặt qua Composer hoặc tham chiếu đến repository GitHub, bạn phải sử dụng tên gói viết thường `namhuunam/moviecontentgenerator` theo quy tắc đặt tên của Composer và GitHub.

### Cài đặt thay thế (nếu gặp lỗi giới hạn API GitHub hoặc exec() bị vô hiệu hóa)

Nếu bạn gặp lỗi về giới hạn API GitHub hoặc hàm `exec()` bị vô hiệu hóa trên máy chủ, hãy thử cách sau:

1. Tải package dưới dạng file ZIP từ GitHub:
   ```bash
   # Sử dụng curl hoặc wget để tải về
   curl -L https://github.com/namhuunam/moviecontentgenerator/archive/refs/heads/main.zip -o moviecontentgenerator.zip
   # hoặc
   wget https://github.com/namhuunam/moviecontentgenerator/archive/refs/heads/main.zip -O moviecontentgenerator.zip
   ```

2. Giải nén file ZIP vào thư mục `vendor/namhuunam/moviecontentgenerator` trong dự án Laravel của bạn:
   ```bash
   mkdir -p vendor/namhuunam/moviecontentgenerator
   unzip moviecontentgenerator.zip -d vendor/namhuunam/
   mv vendor/namhuunam/moviecontentgenerator-main/* vendor/namhuunam/moviecontentgenerator/
   rm -rf vendor/namhuunam/moviecontentgenerator-main
   ```

3. Thêm package vào file composer.json của dự án Laravel:
   ```json
   "require": {
       "namhuunam/moviecontentgenerator": "*"
   },
   "repositories": [
       {
           "type": "path",
           "url": "vendor/namhuunam/moviecontentgenerator"
       }
   ]
   ```

4. Cập nhật autoload của Composer:
   ```bash
   composer dumpautoload
   ```

### Bước 2: Đăng ký Service Provider (Laravel 5.5+ sẽ tự động đăng ký)

Trong file `config/app.php`, thêm service provider vào mảng `providers`:

```php
'providers' => [
    // Các providers khác...
    NamHuuNam\MovieContentGenerator\Providers\MovieContentGeneratorServiceProvider::class,
],
```

### Bước 3: Xuất file cấu hình và migration

```bash
php artisan vendor:publish --provider="NamHuuNam\MovieContentGenerator\Providers\MovieContentGeneratorServiceProvider" --force
```

### Bước 4: Chạy migration để thêm cột 'complete' vào bảng 'movies' (nếu cần)

```bash
php artisan migrate
```

### Bước 5: Cấu hình API Gemini trong file .env

```
# API key và model ID
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL_ID=gemini-1.5-flash-8b

# Prompt template
GEMINI_PROMPT_TEMPLATE="Dựa trên tiêu đề {name} và mô tả {content}, hãy viết một bài viết về phim chuẩn SEO với độ dài khoảng 150 đến 300 từ tránh trùng lặp nội dung với nội dung các website khác. Ngôn ngữ 100% tiếng việt, tuyệt đối không dùng Markdown, không chèn ảnh, không chèn bất kỳ link, và ký tự đặc biệt nào."

# Các tham số tùy chỉnh AI
GEMINI_TEMPERATURE=1
GEMINI_TOP_P=0.95
GEMINI_TOP_K=40
GEMINI_MAX_OUTPUT_TOKENS=8192
```

#### Giải thích các tham số AI:

- `GEMINI_TEMPERATURE`: Điều chỉnh độ ngẫu nhiên của văn bản được tạo (từ 0 đến 1). Giá trị cao hơn sẽ tạo ra kết quả sáng tạo hơn, ngẫu nhiên hơn.
- `GEMINI_TOP_P`: Điều chỉnh độ đa dạng của văn bản (từ 0 đến 1). Giá trị thấp hơn cho kết quả ít đa dạng, tập trung hơn.
- `GEMINI_TOP_K`: Giới hạn số lượng token có xác suất cao nhất được xem xét. Giá trị thấp hơn làm cho văn bản tập trung hơn.
- `GEMINI_MAX_OUTPUT_TOKENS`: Giới hạn độ dài tối đa của phản hồi.

## Sử dụng
```bash
# Xóa cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

Để tự động tạo nội dung cho các phim, chạy lệnh sau:

```bash
php artisan movie:generate-content
```

Mặc định, lệnh sẽ xử lý 10 phim trong một lần chạy. Bạn có thể thay đổi số lượng này bằng tham số `--limit`:

```bash
php artisan movie:generate-content --limit=5
```

## Thiết lập lịch tự động chạy

Package này có thể được cấu hình để chạy tự động theo lịch định kỳ bằng cách sử dụng cron job trên Ubuntu hoặc các hệ điều hành Linux khác.

### Chạy tự động mỗi 15 phút

```bash
*/15 * * * * cd /đường/dẫn/đến/dự/án/laravel && php artisan movie:generate-content >> /đường/dẫn/đến/dự/án/laravel/storage/logs/cron.log 2>&1
```

Thêm dòng trên vào crontab của bạn bằng cách chạy `crontab -e` và dán dòng lệnh vào.

Để biết chi tiết hơn về cách thiết lập cron job, vui lòng xem [hướng dẫn cài đặt cron job](docs/cronjob-setup.md).

## Cách hoạt động

1. Package sẽ kiểm tra và thêm cột `complete` vào bảng `movies` nếu chưa tồn tại
2. Tìm kiếm các phim có `complete = 0`
3. Lấy thông tin từ cột `name` và `content` của các phim
4. Sử dụng API Gemini để tạo nội dung mới dựa trên prompt đã cấu hình
5. Cập nhật nội dung mới vào cột `content` và đánh dấu `complete = 1`
6. Nếu xảy ra lỗi, package sẽ ghi log và bỏ qua phim đó để xử lý phim tiếp theo

## Log lỗi

Log được lưu tại `storage/logs/moviecontentgenerator.log`

## Liên hệ & Đóng góp

Vui lòng gửi issues hoặc pull requests tới [GitHub repository](https://github.com/namhuunam/moviecontentgenerator).

## License

MIT
