<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache"); 

define('LOG_DIR', __DIR__ . '/logs'); 

function log_message($level, $message, $context = []) {
    if (!is_dir(LOG_DIR)) {
        if (!@mkdir(LOG_DIR, 0755, true)) {
            error_log("FATAL: Could not create log directory: " . LOG_DIR);
            return; 
        }
    }
    $log_file = LOG_DIR . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message";
    if (!empty($context)) {
        $log_entry .= " Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    if (@file_put_contents($log_file, $log_entry . "\n", FILE_APPEND | LOCK_EX) === false) {
         error_log("FATAL: Could not write to log file: " . $log_file);
    }
}

function fetch_with_curl($url, $options = []) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_USERAGENT, $options['user_agent'] ?? 'PHP-App/1.0');

    if (!empty($options['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
    }

    if (($options['method'] ?? 'GET') === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        if (!empty($options['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
        }
    }
    
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error_num = curl_errno($ch);
    $curl_error_msg = curl_error($ch);
    curl_close($ch);

    if ($curl_error_num) {
        log_message('ERROR', "cURL Error ({$curl_error_num}) fetching {$url}: {$curl_error_msg}");
        return ['error' => "API bağlantı hatası ({$url})", 'http_code' => $http_code];
    }

    $decoded_response = json_decode($response_body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
         log_message('ERROR', "JSON Decode Error fetching {$url}. HTTP Code: {$http_code}. Response: " . substr($response_body, 0, 1000)); 
         return ['error' => "API yanıtı çözümlenemedi ({$url})", 'http_code' => $http_code];
    }

    if ($http_code >= 400) {
        log_message('WARNING', "HTTP Error {$http_code} fetching {$url}", $decoded_response);
        $api_message = $decoded_response['error_description'] ?? $decoded_response['error'] ?? $decoded_response['message'] ?? 'Bilinmeyen API hatası';
        return ['error' => "API hatası ({$http_code}): " . $api_message, 'http_code' => $http_code, 'response' => $decoded_response];
    }

    return $decoded_response;
}


function getDiscordStatus() {
    $user_id = filter_input(INPUT_ENV, 'DISCORD_USER_ID', FILTER_SANITIZE_STRING) ?: '1244181502795976775'; 
    $api_url = "https://api.lanyard.rest/v1/users/{$user_id}";

    $default_status = [
        'status' => 'offline', 
        'game' => '',
        'has_game' => false,
        'username' => 'kynux.dev',
        'discriminator' => '0',
        'last_updated' => date('H:i:s')
    ];

    $response = fetch_with_curl($api_url, ['user_agent' => 'PHP-StatusFetcher/1.0']);

    if (isset($response['error'])) {
        log_message('ERROR', "Lanyard API Hatası: " . $response['error']);
        return $default_status;
    }

    if (!isset($response['success']) || $response['success'] !== true || !isset($response['data'])) {
        log_message('ERROR', "Lanyard API'den geçersiz yanıt", $response);
        return $default_status;
    }

    $discord_data = $response['data'];
    $game = '';
    $has_game = false;

    if (!empty($discord_data['activities'])) {
        foreach ($discord_data['activities'] as $activity) {
            if (($activity['type'] ?? -1) === 0 && !empty($activity['name'])) {
                $game = $activity['name'];
                $has_game = true;
                break;
            }
        }
    }

    return [
        'status' => $discord_data['discord_status'] ?? 'offline',
        'game' => $game,
        'has_game' => $has_game,
        'username' => $discord_data['discord_user']['username'] ?? 'kynux.dev',
        'discriminator' => $discord_data['discord_user']['discriminator'] ?? '0',
        'last_updated' => date('H:i:s')
    ];
}

function getSpotifyStatus() {
    $spotify_config_file = 'spotify_config.json';

     if (!file_exists($spotify_config_file)) {
         log_message('WARNING', "Spotify config file not found: " . $spotify_config_file);
         return ['error' => 'Spotify yapılandırılmamış.', 'is_playing' => false];
     }
     $config_json = @file_get_contents($spotify_config_file);
     if ($config_json === false) {
         log_message('ERROR', "Could not read Spotify config file: " . $spotify_config_file);
         return ['error' => 'Spotify yapılandırma okunamadı.', 'is_playing' => false];
     }
     $config_data = json_decode($config_json, true);
     if (json_last_error() !== JSON_ERROR_NONE) {
         log_message('ERROR', "Could not decode Spotify config JSON: " . $spotify_config_file . " - " . json_last_error_msg());
         return ['error' => 'Spotify yapılandırma çözümlenemedi.', 'is_playing' => false];
     }

    $client_id = $config_data['client_id'] ?? '';
    $client_secret = $config_data['client_secret'] ?? '';
    $refresh_token = $config_data['refresh_token'] ?? '';
    $access_token = $config_data['access_token'] ?? '';
    $token_expiry = $config_data['token_expiry'] ?? 0;

    $default_status = [
        'is_playing' => false, 'song' => '', 'artist' => '', 'album_art' => '',
        'progress_ms' => 0, 'duration_ms' => 0, 'progress_percent' => 0,
        'last_updated' => date('H:i:s')
    ];

    if (empty($client_id) || empty($client_secret)) {
        log_message('WARNING', "Spotify client_id or client_secret is missing in config.");
        return array_merge($default_status, ['error' => 'Spotify yapılandırma eksik.']);
    }
    if (empty($refresh_token)) {
        log_message('INFO', "Spotify refresh_token is missing. Need authorization.");
        return array_merge($default_status, ['error' => 'Spotify yetkilendirmesi gerekli.', 'auth_required' => true]);
    }

    $now = time();

    if ($now >= $token_expiry || empty($access_token)) {
        log_message('INFO', "Refreshing Spotify access token.");
        $token_url = "https://accounts.spotify.com/api/token";
        $auth_header = "Authorization: Basic " . base64_encode("{$client_id}:{$client_secret}");
        $post_body = http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ]);

        $token_response = fetch_with_curl($token_url, [
            'method' => 'POST',
            'headers' => [$auth_header, 'Content-Type: application/x-www-form-urlencoded'],
            'body' => $post_body
        ]);

         log_message('DEBUG', "Spotify Token Refresh Response", $token_response);

        if (isset($token_response['error'])) {
            log_message('ERROR', "Spotify token refresh failed: " . $token_response['error']);
             if (strpos($token_response['error'], 'invalid_grant') !== false) {
                 unset($config_data['access_token'], $config_data['refresh_token'], $config_data['token_expiry']);
                 if(!@file_put_contents($spotify_config_file, json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                    log_message('ERROR', "Could not clear invalid tokens from config file: " . $spotify_config_file);
                 }
                 return array_merge($default_status, ['error' => 'Spotify yetkilendirmesi geçersiz, tekrar bağlanın.', 'auth_required' => true]);
             }
            return array_merge($default_status, ['error' => 'Spotify token yenileme hatası.']);
        }

        if (isset($token_response['access_token'])) {
            $access_token = $token_response['access_token'];
            $expires_in = $token_response['expires_in'] ?? 3600; 

            $config_data['access_token'] = $access_token;
            $config_data['token_expiry'] = time() + $expires_in - 60; 
            $config_data['updated_at'] = date('Y-m-d H:i:s');
            if (isset($token_response['refresh_token'])) {
                $config_data['refresh_token'] = $token_response['refresh_token'];
                log_message('INFO', "Spotify refresh token was also updated.");
            }

            if (!@file_put_contents($spotify_config_file, json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                 log_message('ERROR', "Could not write updated Spotify token to config file: " . $spotify_config_file);
            }
        } else {
            log_message('ERROR', "Spotify token refresh response did not contain access_token.", $token_response);
            return array_merge($default_status, ['error' => 'Spotify token alınamadı.']);
        }
    }

    $player_url = "https://api.spotify.com/v1/me/player/currently-playing";
    $auth_header = "Authorization: Bearer {$access_token}";

    $player_response = fetch_with_curl($player_url, ['headers' => [$auth_header]]);

    log_message('DEBUG', "Spotify Player Response", $player_response);

    if (isset($player_response['error'])) {
        log_message('ERROR', "Spotify player API error: " . $player_response['error']);
        if (($player_response['http_code'] ?? 0) == 401) {
             unset($config_data['access_token'], $config_data['token_expiry']);
             @file_put_contents($spotify_config_file, json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
             log_message('WARNING', "Received 401 from Spotify Player API, cleared access token for next refresh.");
        }
        return array_merge($default_status, ['error' => 'Spotify player bilgisi alınamadı.']);
    }

    if (($player_response['http_code'] ?? 200) == 204 || empty($player_response) || !isset($player_response['is_playing']) || $player_response['is_playing'] !== true || !isset($player_response['item'])) {
        log_message('INFO', "Spotify is not currently playing or response is empty/invalid.");
        return $default_status; 
    }

    $item = $player_response['item'];
    $progress_ms = $player_response['progress_ms'] ?? 0;
    $duration_ms = $item['duration_ms'] ?? 1; 
    $progress_percent = $duration_ms > 0 ? ($progress_ms / $duration_ms) * 100 : 0;

    return [
        'is_playing' => true,
        'song' => $item['name'] ?? 'Bilinmeyen Şarkı',
        'artist' => $item['artists'][0]['name'] ?? 'Bilinmeyen Sanatçı', 
        'album_art' => $item['album']['images'][1]['url'] ?? ($item['album']['images'][0]['url'] ?? ''), 
        'progress_ms' => (int)$progress_ms,
        'duration_ms' => (int)$duration_ms,
        'progress_percent' => round($progress_percent, 2), 
        'last_updated' => date('H:i:s')
    ];
}

$final_response = [];
try {
    $discord_status = getDiscordStatus();
    $spotify_status = getSpotifyStatus();

    $final_response = [
        'discord' => [
            'status' => $discord_status['status'] ?? 'offline',
            'game' => $discord_status['game'] ?? '',
            'has_game' => $discord_status['has_game'] ?? false,
            'username' => $discord_status['username'] ?? 'kynux.dev',
            'discriminator' => $discord_status['discriminator'] ?? '0',
            'last_updated' => $discord_status['last_updated'] ?? date('H:i:s')
        ],
        'spotify' => [
            'is_playing' => $spotify_status['is_playing'] ?? false,
            'song' => $spotify_status['song'] ?? '',
            'artist' => $spotify_status['artist'] ?? '',
            'album_art' => $spotify_status['album_art'] ?? '',
            'progress_ms' => (int)($spotify_status['progress_ms'] ?? 0),
            'duration_ms' => (int)($spotify_status['duration_ms'] ?? 0),
            'progress_percent' => (float)($spotify_status['progress_percent'] ?? 0),
            'auth_required' => $spotify_status['auth_required'] ?? false, 
            'error' => $spotify_status['error'] ?? null, 
            'last_updated' => $spotify_status['last_updated'] ?? date('H:i:s')
        ],
        'server_time' => date('H:i:s')
    ];

    if (isset($discord_status['error'])) {
        $final_response['discord']['error'] = $discord_status['error'];
    }

} catch (Throwable $e) { 
    log_message('CRITICAL', "Genel API hatası: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(), ['trace' => $e->getTraceAsString()]);
    http_response_code(500); 
    $final_response = [
        'error' => 'Sunucu tarafında beklenmedik bir hata oluştu.',
        'detail' => htmlspecialchars($e->getMessage()) 
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($final_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit; 

?>
