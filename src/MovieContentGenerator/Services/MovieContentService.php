<?php

namespace NamHuuNam\MovieContentGenerator\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MovieContentService
{
    /**
     * @var GeminiService
     */
    protected $geminiService;

    /**
     * MovieContentService constructor.
     * 
     * @param GeminiService $geminiService
     */
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Check and add 'complete' column to movies table if not exists
     * 
     * @return bool
     */
    public function ensureCompleteColumnExists()
    {
        try {
            if (!Schema::hasTable('movies')) {
                LoggerService::error("Không tìm thấy bảng 'movies'.");
                return false;
            }

            if (!Schema::hasColumn('movies', 'complete')) {
                Schema::table('movies', function ($table) {
                    $table->tinyInteger('complete')->default(0)->after('content');
                });
                LoggerService::info("Đã thêm cột 'complete' vào bảng 'movies'.");
            } else {
                LoggerService::info("Cột 'complete' đã tồn tại trong bảng 'movies'.");
            }

            return true;
        } catch (\Exception $e) {
            LoggerService::error("Lỗi khi kiểm tra/tạo cột 'complete': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process all movies with complete = 0
     * 
     * @param int $limit Number of movies to process in one run
     * @return array Results statistics
     */
    public function processMovies($limit = 10)
    {
        $stats = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'remaining' => 0
        ];

        try {
            // Lấy danh sách phim cần xử lý
            $movies = DB::table('movies')
                ->where('complete', 0)
                ->limit($limit)
                ->get();

            $stats['processed'] = count($movies);
            
            if ($stats['processed'] === 0) {
                LoggerService::info("Không còn phim nào cần xử lý.");
                return $stats;
            }

            foreach ($movies as $movie) {
                try {
                    // Kiểm tra dữ liệu đầu vào
                    if (empty($movie->name)) {
                        LoggerService::error("Phim ID {$movie->id} thiếu thông tin name. Bỏ qua phim này.");
                        $stats['failed']++;
                        continue;
                    }

                    $movieContent = $movie->content ?? '';
                    $generatedContent = null;

                    // Nếu content bị thiếu, chỉ sử dụng name
                    if (empty($movieContent)) {
                        LoggerService::info("Phim ID {$movie->id}: Thiếu thông tin content, chỉ sử dụng name để tạo nội dung");
                        $generatedContent = $this->geminiService->generateContent($movie->name, '');
                    } else {
                        // Sử dụng cả name và content để tạo nội dung
                        LoggerService::info("Đang xử lý phim: {$movie->name} (ID: {$movie->id})");
                        $generatedContent = $this->geminiService->generateContent($movie->name, $movieContent);
                    }

                    // Nếu không nhận được nội dung, bỏ qua phim này và tiếp tục phim tiếp theo
                    if ($generatedContent === null) {
                        LoggerService::error("Không thể tạo nội dung cho phim ID {$movie->id}: {$movie->name}");
                        $stats['failed']++;
                        continue;
                    }

                    // Xử lý nội dung để loại bỏ các dòng trống
                    $processedContent = $this->processContent($generatedContent);

                    // Cập nhật nội dung và đánh dấu đã hoàn thành
                    DB::table('movies')
                        ->where('id', $movie->id)
                        ->update([
                            'content' => '<p>' . $processedContent . '</p>',
                            'complete' => 1
                        ]);

                    LoggerService::info("Đã cập nhật nội dung cho phim ID {$movie->id}: {$movie->name}");
                    $stats['success']++;

                } catch (\Exception $e) {
                    LoggerService::error("Lỗi khi xử lý phim ID {$movie->id}: " . $e->getMessage());
                    $stats['failed']++;
                }
            }

            // Đếm số lượng phim còn lại cần xử lý
            $stats['remaining'] = DB::table('movies')->where('complete', 0)->count();

            return $stats;

        } catch (\Exception $e) {
            LoggerService::error("Lỗi trong quá trình xử lý phim: " . $e->getMessage());
            return $stats;
        }
    }

    /**
     * Process the content to remove empty lines
     * 
     * @param string $content
     * @return string
     */
    protected function processContent($content)
    {
        // Chuyển đổi tất cả dấu xuống dòng thành \n để đảm bảo xử lý nhất quán
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Tách nội dung thành các dòng
        $lines = explode("\n", $content);
        
        // Lọc các dòng trống
        $filteredLines = [];
        foreach ($lines as $line) {
            // Chỉ giữ lại các dòng không trống
            if (trim($line) !== '') {
                $filteredLines[] = $line;
            }
        }
        
        // Nối lại các dòng với dấu xuống dòng
        $processedContent = implode("\n", $filteredLines);
        
        return $processedContent;
    }
}
