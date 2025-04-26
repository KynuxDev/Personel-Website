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
    $log_dir = 'logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    

    $user_id = getenv('DISCORD_USER_ID') ?: '1244181502795976775';
    
    $api_url = "https://api.lanyard.rest/v1/users/{$user_id}";
    
    try {
        $response = @file_get_contents($api_url);
        
        if ($response === false) {
            logError("API Error: Could not connect to Lanyard API");
            return [
                'status' => 'online',
                'game' => '',
                'has_game' => false,
                'username' => 'kynux.dev',
                'discriminator' => '0',
                'last_updated' => date('H:i:s')
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['success']) || $data['success'] !== true) {
            logError("API Error: Invalid response from Lanyard API: " . print_r($data, true));
            return [
                'status' => 'online',
                'game' => '',
                'has_game' => false,
                'username' => 'kynux.dev',
                'discriminator' => '0',
                'last_updated' => date('H:i:s')
            ];
        }
        
        $discord_data = $data['data'];
        
        $game = '';
        $has_game = false;
        
        if (!empty($discord_data['activities'])) {
            foreach ($discord_data['activities'] as $activity) {
                if (isset($activity['type']) && $activity['type'] === 0) {
                    $game = $activity['name'] ?? '';
                    $has_game = true;
                    break;
                }
            }
        }
        
        return [
            'status' => $discord_data['discord_status'] ?? 'online',
            'game' => $game,
            'has_game' => $has_game,
            'username' => $discord_data['discord_user']['username'] ?? 'kynux.dev',
            'discriminator' => $discord_data['discord_user']['discriminator'] ?? '0',
            'last_updated' => date('H:i:s')
        ];
        
    } catch (Exception $e) {
        logError("Discord API Error: " . $e->getMessage());
        
        return [
            'status' => 'online',
            'game' => '',
            'has_game' => false,
            'username' => 'kynux.dev',
            'discriminator' => '0',
            'last_updated' => date('H:i:s')
        ];
    }
}

function getSpotifyStatus() {
    $spotify_config_file = 'spotify_config.json';
    $client_id = '';
    $client_secret = '';
    
    if (file_exists($spotify_config_file)) {
        $config_data = json_decode(file_get_contents($spotify_config_file), true);
        $client_id = $config_data['client_id'] ?? '';
        $client_secret = $config_data['client_secret'] ?? '';
    } else {
        file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - spotify_config.json dosyası bulunamadı.\n", FILE_APPEND);
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
    
    $refresh_token = $config_data['refresh_token'] ?? '';
    $access_token = $config_data['access_token'] ?? '';
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
        
        if (isset($player_data['is_playing']) && $player_data['is_playing'] === true && isset($player_data['item'])) {
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

try {
    $discord_status = getDiscordStatus();
    $spotify_status = getSpotifyStatus();
    
    $response = [
        'discord' => [
            'status' => $discord_status['status'] ?? 'dnd',
            'game' => $discord_status['game'] ?? '',
            'has_game' => $discord_status['has_game'] ?? false,
            'username' => $discord_status['username'] ?? 'kynux.dev',
            'discriminator' => $discord_status['discriminator'] ?? '0',
            'last_updated' => date('H:i:s')
        ],
        'spotify' => [
            'is_playing' => ($spotify_status['is_playing'] ?? false) ? true : false,
            'song' => $spotify_status['song'] ?? '',
            'artist' => $spotify_status['artist'] ?? '',
            'album_art' => $spotify_status['album_art'] ?? '',
            'progress_ms' => (int)($spotify_status['progress_ms'] ?? 0),
            'duration_ms' => (int)($spotify_status['duration_ms'] ?? 0),
            'progress_percent' => (float)($spotify_status['progress_percent'] ?? 0),
            'last_updated' => date('H:i:s')
        ],
        'server_time' => date('H:i:s')
    ];
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    
} catch (Exception $e) {
    logError("Genel API hatası: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo '{"error":"API işleme hatası: ' . $e->getMessage() . '"}';
}
