<?php

$env_file = __DIR__ . '/.env';
$discord_bot_token = '';
$user_id_to_track = '';

if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    $discord_bot_token = $env_vars['DISCORD_BOT_TOKEN'] ?? '';
    $user_id_to_track = $env_vars['DISCORD_USER_ID'] ?? '1244181502795976775';
} else {
    logDebug("HATA: .env dosyası bulunamadı veya okunamadı!");
    exit("Yapılandırma hatası: Gerekli çevre değişkenleri bulunamadı!");
}

if (empty($discord_bot_token)) {
    logDebug("HATA: Discord Bot Token bulunamadı!");
    exit("Discord yapılandırma hatası: Token eksik!");
}

$logs_dir = __DIR__ . '/logs';
$status_file = $logs_dir . '/discord_status.json';
$debug_file = $logs_dir . '/discord_debug.log';

if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

function logDebug($message) {
    global $debug_file;
    file_put_contents($debug_file, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

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

function getUserPresence($user_id) {
    global $discord_bot_token;
    
    
    global $status_file;
    $current_status = [];
    
    if (file_exists($status_file)) {
        $current_status = json_decode(file_get_contents($status_file), true);
    }
    
    $user_info = getUserInfo($user_id);
    
    if (!$user_info) {
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

function updateDiscordStatus() {
    global $user_id_to_track;
    
    try {
        logDebug("Durum güncellemesi başlatılıyor...");
        
        $status_data = getUserPresence($user_id_to_track);
        
        if (saveStatus($status_data)) {
            logDebug("Durum başarıyla güncellendi.");
        } else {
            logDebug("Durum güncellenirken bir hata oluştu.");
        }
    } catch (Exception $e) {
        logDebug("Hata: " . $e->getMessage());
    }
}

if (php_sapi_name() === 'cli') {
    echo "Discord durum güncellemesi başlatılıyor...\n";
    updateDiscordStatus();
    echo "İşlem tamamlandı.\n";
} else {
    updateDiscordStatus();
    
    header('Content-Type: application/json; charset=utf-8');
    
    $status_data = json_decode(file_get_contents($status_file), true);
    echo json_encode($status_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
