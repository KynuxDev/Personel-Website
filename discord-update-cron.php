<?php
/**
 * Discord Durum Güncelleme Cron İşi
 * 
 * Bu script discord-api.php'yi düzenli aralıklarla çağırarak, 
 * Discord durum bilgilerini güncel tutar.
 * 
 * Çalıştırma: 
 * - Windows Task Scheduler veya Linux Cron ile her dakika çalıştırın
 * - Windows: php.exe -f C:/xampp/htdocs/discord-update-cron.php
 * - Linux: * * * * * php /path/to/discord-update-cron.php
 */

// Başlangıç zamanını kaydet
$start_time = microtime(true);

// Kök dizini ayarla
$root_dir = __DIR__;
$log_file = $root_dir . '/logs/cron_log.txt';

// Log sınıfı
class Logger {
    private $log_file;
    
    public function __construct($log_file) {
        $this->log_file = $log_file;
    }
    
    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        
        // Loglar dizini yoksa oluştur
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Log dosyasına yaz
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
}

// Log örneği oluştur
$logger = new Logger($log_file);
$logger->log("Cron işi başlatıldı");

try {
    // Discord API'yi çağır
    $logger->log("discord-api.php çağrılıyor...");
    
    // API'yi çağır ve sonucu al (http yerine güvenli şekilde dosya yolunu kullan)
    $api_result = file_get_contents(__DIR__ . "/discord-api.php");
    
    if ($api_result === false) {
        throw new Exception("API çağrısı başarısız oldu");
    }
    
    // API sonucunu JSON olarak ayrıştır
    $api_data = json_decode($api_result, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON ayrıştırma hatası: " . json_last_error_msg());
    }
    
    // Sonuç bilgilerini logla
    $status = $api_data['status'] ?? 'bilinmiyor';
    $username = $api_data['username'] ?? 'bilinmiyor';
    $logger->log("Durum güncellendi: $username - $status");
    
    // İşlem süresini hesapla
    $execution_time = round(microtime(true) - $start_time, 2);
    $logger->log("İşlem tamamlandı (süre: {$execution_time}s)");
    
} catch (Exception $e) {
    $logger->log("HATA: " . $e->getMessage());
    exit(1);
}

// Başarılı çıkış
exit(0);
