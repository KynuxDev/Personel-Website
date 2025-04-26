<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

function logError($message) {
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    file_put_contents('logs/api_error.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

function getDiscordStatus() {
    $status_paths = [
        __DIR__ . '/discord-bot/logs/discord_status.json',
        __DIR__ . '/logs/discord_status.json',
    ];
    
    $status_file = null;
    $api_data = null;
    
    foreach ($status_paths as $path) {
        if (file_exists($path)) {
            logError("Discord durum dosyası bulundu: " . $path);
            $status_file = $path;
            
            try {
                $status_content = file_get_contents($status_file);
                
                if ($status_content === false) {
                    logError("Discord durum dosyası okunamadı: " . $path);
                    continue; // Diğer dosyayı deneyelim
                }
                
                $api_data = json_decode($status_content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    logError("Discord JSON ayrıştırma hatası: " . json_last_error_msg() . " - Dosya: " . $path);
                    continue; // Diğer dosyayı deneyelim
                }
                
                if ($api_data && isset($api_data['status'])) {
                    break;
                }
            } catch (Exception $e) {
                logError("Discord dosya okuma hatası: " . $e->getMessage() . " - Dosya: " . $path);
                continue; // Diğer dosyayı deneyelim
            }
        }
    }
    
    if ($api_data && isset($api_data['status'])) {
        return [
            'status' => $api_data['status'],
            'game' => $api_data['game'] ?? '',
            'has_game' => $api_data['has_game'] ?? false,
            'username' => $api_data['username'] ?? 'kynux.dev',
            'discriminator' => $api_data['discriminator'] ?? '0000',
            'last_updated' => isset($api_data['last_updated']) ? date('H:i:s', strtotime($api_data['last_updated'])) : date('H:i:s')
        ];
    }
    
    file_put_contents('logs/discord_debug.log', "Last check: " . date('Y-m-d H:i:s') . "\nStatus file missing or invalid\n", FILE_APPEND);
    
    return [
        'status' => 'dnd',
        'game' => '',
        'has_game' => false,
        'username' => 'kynux.dev',
        'discriminator' => '0',
        'last_updated' => date('H:i:s')
    ];
}

function getSpotifyStatus() {
    $env_file = __DIR__ . '/.env';
    $client_id = '';
    $client_secret = '';
    
    if (file_exists($env_file)) {
        $env_vars = parse_ini_file($env_file);
        $client_id = $env_vars['SPOTIFY_CLIENT_ID'] ?? '';
        $client_secret = $env_vars['SPOTIFY_CLIENT_SECRET'] ?? '';
    } else {
        file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - .env dosyası bulunamadı.\n", FILE_APPEND);
    }
    
    if (empty($client_id) || empty($client_secret)) {
        return [
            'is_playing' => false,
            'song' => '',
            'artist' => '',
            'album_art' => '',
            'progress_ms' => 0,
            'duration_ms' => 0,
            'progress_percent' => 0,
            'error' => 'Spotify yapılandırma hatası',
            'last_updated' => date('H:i:s')
        ];
    }
    
    $spotify_config_file = 'spotify_config.json';
    $refresh_token = '';
    $config_data = [];
    $access_token = '';
    
    if (file_exists($spotify_config_file)) {
        $config_content = file_get_contents($spotify_config_file);
        $config_data = json_decode($config_content, true);
        $refresh_token = $config_data['refresh_token'] ?? '';
        $access_token = $config_data['access_token'] ?? '';
        $token_expiry = $config_data['token_expiry'] ?? 0;
    } else {
        return [
            'is_playing' => false,
            'song' => '',
            'artist' => '',
            'album_art' => '',
            'progress_ms' => 0,
            'duration_ms' => 0,
            'progress_percent' => 0,
            'auth_required' => true,
            'auth_url' => $auth_url ?? '#',
            'last_updated' => date('H:i:s')
        ];
    }
    
    $token_expiry = $config_data['token_expiry'] ?? 0;
    $now = time();
    
    if ($now >= $token_expiry || empty($access_token)) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=refresh_token&refresh_token={$refresh_token}");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Basic " . base64_encode("{$client_id}:{$client_secret}")
            ]);
            
            $token_response = curl_exec($ch);
            $token_data = json_decode($token_response, true);
            
            if (!file_exists('logs')) {
                mkdir('logs', 0755, true);
            }
            file_put_contents('logs/spotify_token.log', date('Y-m-d H:i:s') . " - Token Refresh Response: " . print_r($token_data, true) . "\n", FILE_APPEND);
            
            if (isset($token_data['access_token'])) {
                $access_token = $token_data['access_token'];
                $expires_in = $token_data['expires_in'] ?? 3600;
                
                $config_data['access_token'] = $access_token;
                $config_data['token_expiry'] = time() + $expires_in;
                $config_data['updated_at'] = date('Y-m-d H:i:s');
                
                if (isset($token_data['refresh_token'])) {
                    $config_data['refresh_token'] = $token_data['refresh_token'];
                }
                
                file_put_contents($spotify_config_file, json_encode($config_data));
            } else {
                return [
                    'is_playing' => false,
                    'song' => '',
                    'artist' => '',
                    'album_art' => '',
                    'progress_ms' => 0,
                    'duration_ms' => 0,
                    'progress_percent' => 0,
                    'auth_required' => true,
                    'auth_url' => '#',
                    'last_updated' => date('H:i:s')
                ];
            }
        } catch (Exception $e) {
            file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - Token refresh error: " . $e->getMessage() . "\n", FILE_APPEND);
            return [
                'is_playing' => false,
                'song' => '',
                'artist' => '',
                'album_art' => '',
                'progress_ms' => 0,
                'duration_ms' => 0,
                'progress_percent' => 0,
                'error' => 'Token refresh failed',
                'last_updated' => date('H:i:s')
            ];
        }
    }
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me/player/currently-playing");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$access_token}"
        ]);
        
        $player_response = curl_exec($ch);
        $player_data = json_decode($player_response, true);
        
        curl_close($ch);
        
        file_put_contents('logs/spotify_player.log', date('Y-m-d H:i:s') . " - Player Response: " . print_r($player_data, true) . "\n", FILE_APPEND);
        
        if (isset($player_data['is_playing']) && $player_data['is_playing'] && isset($player_data['item'])) {
            $progress_ms = $player_data['progress_ms'] ?? 0;
            $duration_ms = $player_data['item']['duration_ms'] ?? 1;
            $progress_percent = ($progress_ms / $duration_ms) * 100;
            
            return [
                'is_playing' => true,
                'song' => $player_data['item']['name'],
                'artist' => $player_data['item']['artists'][0]['name'],
                'album_art' => $player_data['item']['album']['images'][1]['url'],
                'progress_ms' => $progress_ms,
                'duration_ms' => $duration_ms,
                'progress_percent' => $progress_percent,
                'last_updated' => date('H:i:s')
            ];
        } else {
            return [
                'is_playing' => false,
                'song' => '',
                'artist' => '',
                'album_art' => '',
                'progress_ms' => 0,
                'duration_ms' => 0,
                'progress_percent' => 0,
                'last_updated' => date('H:i:s')
            ];
        }
    } catch (Exception $e) {
        file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - API error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [
            'is_playing' => false,
            'song' => '',
            'artist' => '',
            'album_art' => '',
            'progress_ms' => 0,
            'duration_ms' => 0,
            'progress_percent' => 0,
            'error' => $e->getMessage(),
            'last_updated' => date('H:i:s')
        ];
    }
}

ob_start();

try {
    $discord_status = getDiscordStatus();
    $spotify_status = getSpotifyStatus();
    
    $response = [];
    
    $discord = [];
    $discord['status'] = $discord_status['status'] ?? 'dnd';
    $discord['game'] = $discord_status['game'] ?? '';
    $discord['has_game'] = $discord_status['has_game'] ?? false;
    $discord['username'] = $discord_status['username'] ?? 'kynux.dev';
    $discord['discriminator'] = $discord_status['discriminator'] ?? '0';
    $discord['last_updated'] = date('H:i:s');
    $response['discord'] = $discord;
    
    $spotify = [];
    $spotify['is_playing'] = ($spotify_status['is_playing'] ?? false) ? true : false;
    $spotify['song'] = $spotify_status['song'] ?? '';
    $spotify['artist'] = $spotify_status['artist'] ?? '';
    $spotify['album_art'] = $spotify_status['album_art'] ?? '';
    $spotify['progress_ms'] = (int)($spotify_status['progress_ms'] ?? 0);
    $spotify['duration_ms'] = (int)($spotify_status['duration_ms'] ?? 0);
    $spotify['progress_percent'] = (float)($spotify_status['progress_percent'] ?? 0);
    $spotify['last_updated'] = date('H:i:s');
    $response['spotify'] = $spotify;
    
    $response['server_time'] = date('H:i:s');
    
    ob_end_clean();
    
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_end_clean();
    
    logError("Genel API hatası: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo '{"error":"API işleme hatası: ' . $e->getMessage() . '"}';
}
