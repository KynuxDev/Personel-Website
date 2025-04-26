<?php

$start_time = microtime(true);

$root_dir = __DIR__;
$log_file = $root_dir . '/logs/cron_log.txt';

class Logger {
    private $log_file;
    
    public function __construct($log_file) {
        $this->log_file = $log_file;
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
}

$logger = new Logger($log_file);
$logger->log("Cron işi başlatıldı");

try {
    $logger->log("discord-api.php çağrılıyor...");
    
    $api_result = file_get_contents(__DIR__ . "/discord-api.php");
    
    if ($api_result === false) {
        throw new Exception("API çağrısı başarısız oldu");
    }
    
    $api_data = json_decode($api_result, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON ayrıştırma hatası: " . json_last_error_msg());
    }
    
    $status = $api_data['status'] ?? 'bilinmiyor';
    $username = $api_data['username'] ?? 'bilinmiyor';
    $logger->log("Durum güncellendi: $username - $status");
    
    $execution_time = round(microtime(true) - $start_time, 2);
    $logger->log("İşlem tamamlandı (süre: {$execution_time}s)");
    
} catch (Exception $e) {
    $logger->log("HATA: " . $e->getMessage());
    exit(1);
}

exit(0);
