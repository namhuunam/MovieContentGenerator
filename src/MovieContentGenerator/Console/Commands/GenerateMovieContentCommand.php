<?php

namespace NamHuuNam\MovieContentGenerator\Console\Commands;

use Illuminate\Console\Command;
use NamHuuNam\MovieContentGenerator\Services\MovieContentService;

class GenerateMovieContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movie:generate-content {--limit=10 : Số lượng phim xử lý trong một lần chạy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động tạo nội dung cho các phim sử dụng API Gemini';

    /**
     * The movie content service.
     *
     * @var MovieContentService
     */
    protected $movieContentService;

    /**
     * Create a new command instance.
     *
     * @param MovieContentService $movieContentService
     * @return void
     */
    public function __construct(MovieContentService $movieContentService)
    {
        parent::__construct();
        $this->movieContentService = $movieContentService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Bắt đầu quá trình tạo nội dung phim...');
        
        // Kiểm tra API key
        if (empty(config('MovieContentGenerator.api_key'))) {
            $this->error('Chưa cấu hình API key Gemini. Vui lòng thêm GEMINI_API_KEY vào file .env.');
            return 1;
        }

        // Kiểm tra và tạo cột complete nếu chưa tồn tại
        $this->info('Kiểm tra cấu trúc bảng dữ liệu...');
        if (!$this->movieContentService->ensureCompleteColumnExists()) {
            $this->error('Không thể kiểm tra hoặc tạo cột complete trong bảng movies. Xem log để biết thêm chi tiết.');
            return 1;
        }

        // Lấy số lượng phim cần xử lý từ tùy chọn
        $limit = (int) $this->option('limit');
        if ($limit <= 0) {
            $limit = 10;
        }

        $this->info("Xử lý tối đa {$limit} phim trong một lần chạy...");

        // Xử lý các phim
        $stats = $this->movieContentService->processMovies($limit);
        
        // Hiển thị kết quả
        $this->info('Kết quả xử lý:');
        $this->info("- Tổng số phim đã xử lý: {$stats['processed']}");
        $this->info("- Thành công: {$stats['success']}");
        $this->info("- Thất bại: {$stats['failed']}");
        $this->info("- Còn lại chưa xử lý: {$stats['remaining']}");

        if ($stats['processed'] === 0) {
            $this->info('Không còn phim nào cần xử lý.');
        }

        return 0;
    }
}