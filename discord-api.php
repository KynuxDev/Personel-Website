<?php
/**
 * Discord API PHP Entegrasyonu
 * Discord bot yerine doğrudan PHP ile Discord API'sine bağlanıp kullanıcı durumunu takip etme
 */

// Discord API ayarları - Güvenlik için .env dosyasından al
$env_file = __DIR__ . '/.env';
$discord_bot_token = '';
$user_id_to_track = '';

// .env dosyası varsa, değerleri al
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    $discord_bot_token = $env_vars['DISCORD_BOT_TOKEN'] ?? '';
    $user_id_to_track = $env_vars['DISCORD_USER_ID'] ?? '1244181502795976775';
} else {
    logDebug("HATA: .env dosyası bulunamadı veya okunamadı!");
    exit("Yapılandırma hatası: Gerekli çevre değişkenleri bulunamadı!");
}

// Token boşsa hataya düş
if (empty($discord_bot_token)) {
    logDebug("HATA: Discord Bot Token bulunamadı!");
    exit("Discord yapılandırma hatası: Token eksik!");
}

// Log dosyası yolu
$logs_dir = __DIR__ . '/logs';
$status_file = $logs_dir . '/discord_status.json';
$debug_file = $logs_dir . '/discord_debug.log';

// Loglar klasörünü oluştur
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

// Debug log fonksiyonu
function logDebug($message) {
    global $debug_file;
    file_put_contents($debug_file, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

// Durum bilgilerini kaydetme fonksiyonu
function saveStatus($statusData) {
    global $status_file;
    try {
        file_put_contents($status_file, json_encode($statusData, JSON_PRETTY_PRINT));
        logDebug('Durum bilgileri kaydedildi: ' . json_encode($statusData));
        return true;
    } catch (Exception $e) {
        logDebug('Durum bilgileri kaydedilemedi: ' . $e->getMessage());
        return false;
    }
}

// Discord API'den kullanıcı bilgilerini al
function getUserInfo($user_id) {
    global $discord_bot_token;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://discord.com/api/v10/users/{$user_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bot {$discord_bot_token}",
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200) {
        logDebug("Kullanıcı bilgileri alınamadı. HTTP kodu: {$http_code}, Yanıt: {$response}");
        return null;
    }
    
    return json_decode($response, true);
}

// Gateway bağlantısı kurarak kullanıcı durumunu takip etme (simülasyon)
function getUserPresence($user_id) {
    global $discord_bot_token;
    
    // Discord Gateway veya OAuth2 ile gerçek zamanlı presence verisi çekme
    // Bu örnekte, kullanıcı durumunu rastgele belirleyerek simüle ediyoruz
    // Gerçek implementasyonda, Discord Gateway veya özel çözüm kullanılmalıdır
    
    // Mevcut durum dosyasını oku (varsa)
    global $status_file;
    $current_status = [];
    
    if (file_exists($status_file)) {
        $current_status = json_decode(file_get_contents($status_file), true);
    }
    
    // Kullanıcı bilgilerini API'den al
    $user_info = getUserInfo($user_id);
    
    if (!$user_info) {
        // API'den kullanıcı bilgisi alınamadıysa, mevcut status'u koru veya varsayılana dön
        if (!empty($current_status)) {
            return $current_status;
        }
        
        return [
            'status' => 'offline',
            'game' => '',
            'has_game' => false,
            'username' => 'kynux.dev',
            'discriminator' => '0000',
            'last_updated' => date('c')
        ];
    }
    
    // Özel bir Discord Gateway implementasyonu olmadan presence bilgisini almak mümkün değil
    // Bu nedenle mevcut durum dosyasındaki status'u koruyoruz veya varsayılan olarak dnd kullanıyoruz
    $status = $current_status['status'] ?? 'dnd';
    
    return [
        'status' => $status,
        'game' => $current_status['game'] ?? '',
        'has_game' => $current_status['has_game'] ?? false,
        'username' => $user_info['username'] ?? 'kynux.dev',
        'discriminator' => $user_info['discriminator'] ?? '0',
        'last_updated' => date('c')
    ];
}

/**
 * Discord gateway ile bağlantı
 * Not: Tam gateway bağlantısı, WebSocket gerektirdiği için burada basitleştirilmiştir.
 * Bu örnekte, periyodik HTTP istekleri ile durum güncellenecektir.
 */
function updateDiscordStatus() {
    global $user_id_to_track;
    
    try {
        logDebug("Durum güncellemesi başlatılıyor...");
        
        // Kullanıcının Discord bilgilerini al
        $status_data = getUserPresence($user_id_to_track);
        
        // Durum bilgilerini kaydet
        if (saveStatus($status_data)) {
            logDebug("Durum başarıyla güncellendi.");
        } else {
            logDebug("Durum güncellenirken bir hata oluştu.");
        }
    } catch (Exception $e) {
        logDebug("Hata: " . $e->getMessage());
    }
}

// Script komut satırından çalıştırıldığında durum güncellemesi yap
if (php_sapi_name() === 'cli') {
    echo "Discord durum güncellemesi başlatılıyor...\n";
    updateDiscordStatus();
    echo "İşlem tamamlandı.\n";
} else {
    // Web tarayıcısından çağrıldığında
    updateDiscordStatus();
    
    // API yanıtı olarak mevcut durumu döndür
    header('Content-Type: application/json; charset=utf-8');
    
    // JSON dosyasından veriyi oku ve tekrar kodla
    $status_data = json_decode(file_get_contents($status_file), true);
    echo json_encode($status_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
