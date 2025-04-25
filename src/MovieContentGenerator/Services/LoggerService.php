<?php

namespace NamHuuNam\MovieContentGenerator\Services;

class LoggerService
{
    /**
     * Log a message to the custom log file
     *
     * @param string $message The message to log
     * @param string $level The log level (INFO, ERROR, etc.)
     * @return void
     */
    public static function log($message, $level = 'INFO')
    {
        $logFile = config('MovieContentGenerator.log_file', storage_path('logs/moviecontentgenerator.log'));
        
        // Tạo thư mục lưu log nếu chưa tồn tại
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Ghi log với thời gian
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(
            $logFile, 
            "[{$timestamp}] {$level}: {$message}" . PHP_EOL, 
            FILE_APPEND
        );
    }

    /**
     * Log an info message
     *
     * @param string $message
     * @return void
     */
    public static function info($message)
    {
        self::log($message, 'INFO');
    }

    /**
     * Log a warning message
     *
     * @param string $message
     * @return void
     */
    public static function warning($message)
    {
        self::log($message, 'WARNING');
    }

    /**
     * Log an error message
     *
     * @param string $message
     * @return void
     */
    public static function error($message)
    {
        self::log($message, 'ERROR');
    }
}
