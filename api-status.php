<?php
/**
 * Basitleştirilmiş API Durumu - Sadece JSON çıktısı için
 * Bu dosya get-status.php yerine kullanılacak
 */

// Headers - basit tutuyoruz 
header('Content-Type: application/json');

// Global değişkenler
$api_data = [];

try {
    // Discord durum verilerini al
    $status_file = __DIR__ . '/logs/discord_status.json';
    
    // Dosya varsa oku
    if (file_exists($status_file)) {
        $status_content = file_get_contents($status_file);
        $discord_data = json_decode($status_content, true);
        
        // Discord verilerini API çıktısına ekle
        $api_data['discord'] = [
            'status' => $discord_data['status'] ?? 'dnd',
            'game' => $discord_data['game'] ?? '',
            'has_game' => $discord_data['has_game'] ?? false,
            'username' => $discord_data['username'] ?? 'kynux.dev',
            'discriminator' => $discord_data['discriminator'] ?? '0',
            'last_updated' => date('H:i:s')
        ];
    } else {
        // Varsayılan Discord verileri
        $api_data['discord'] = [
            'status' => 'dnd',
            'game' => '',
            'has_game' => false,
            'username' => 'kynux.dev',
            'discriminator' => '0',
            'last_updated' => date('H:i:s')
        ];
    }
    
    // Spotify için temel veriler
    $api_data['spotify'] = [
        'is_playing' => false,
        'song' => '',
        'artist' => '',
        'album_art' => '',
        'progress_ms' => 0,
        'duration_ms' => 0,
        'progress_percent' => 0,
        'last_updated' => date('H:i:s')
    ];
    
    // Server zamanı ekle
    $api_data['server_time'] = date('H:i:s');
    
    // JSON olarak çıktı ver - opsiyonları ekleyerek
    echo json_encode($api_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    // Hata durumunda sadece temel hata mesajı
    echo json_encode(['error' => 'API Processing Error']);
}
