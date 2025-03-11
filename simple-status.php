<?php
/**
 * Basit Discord Durum API'si
 * Discord bot tarafından güncellenen logs/discord_status.json dosyasını okuyarak 
 * kullanıcının Discord durumunu ve oyun bilgisini JSON formatında sunar
 */

// Tarayıcı önbelleğini engelle
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

// Hata kontrolü
try {
    // Discord durum dosyasının konumu
    $status_file = __DIR__ . '/logs/discord_status.json';
    
    // Durum dosyası var mı?
    if (file_exists($status_file)) {
        // Dosya içeriğini oku
        $discord_status = file_get_contents($status_file);
        
        // Geçerli JSON mu kontrol et
        $status_data = json_decode($discord_status, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Son güncelleme zamanını daha okunabilir formata çevir
            if (isset($status_data['last_updated'])) {
                $date = new DateTime($status_data['last_updated']);
                $status_data['last_updated_formatted'] = $date->format('H:i:s');
            }
            
            // Yanıtı hazırla
            $response = array(
                'discord' => $status_data,
                'server_time' => date('H:i:s')
            );
            
            // JSON olarak çıktı ver - tüm formatlama opsiyonlarıyla
            echo json_encode($response, 
                JSON_PRETTY_PRINT | 
                JSON_UNESCAPED_UNICODE | 
                JSON_UNESCAPED_SLASHES | 
                JSON_PRESERVE_ZERO_FRACTION);
        } else {
            // JSON hatası
            echo json_encode([
                'error' => 'Discord durum verisi geçersiz: ' . json_last_error_msg(),
                'server_time' => date('H:i:s')
            ]);
        }
    } else {
        // Dosya bulunamadı
        echo json_encode([
            'error' => 'Discord durum dosyası bulunamadı',
            'discord' => [
                'status' => 'dnd',
                'username' => 'kynux.dev',
                'game' => '',
                'has_game' => false
            ],
            'server_time' => date('H:i:s')
        ]);
    }
} catch (Exception $e) {
    // Genel hata durumu
    echo json_encode([
        'error' => 'API hatası: ' . $e->getMessage(),
        'server_time' => date('H:i:s')
    ]);
}
