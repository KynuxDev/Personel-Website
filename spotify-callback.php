<?php
session_start();

define('LOG_DIR', __DIR__ . '/logs'); 

function log_message($level, $message, $context = []) {
    if (!is_dir(LOG_DIR)) {
        if (!@mkdir(LOG_DIR, 0755, true)) { error_log("FATAL: Could not create log directory: " . LOG_DIR); return; }
    }
    $log_file = LOG_DIR . '/app.log'; 
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message";
    if (!empty($context)) {
        $log_entry .= " Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    @file_put_contents($log_file, $log_entry . "\n", FILE_APPEND | LOCK_EX);
}

function fetch_with_curl($url, $options = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, $options['user_agent'] ?? 'PHP-App/1.0');
    if (!empty($options['headers'])) { curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']); }
    if (($options['method'] ?? 'GET') === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        if (!empty($options['body'])) { curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']); }
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
        log_message('ERROR', "JSON Decode Error fetching {$url}. HTTP Code: {$http_code}. Response: " . substr($response_body, 0, 500));
        return ['error' => "API yanıtı çözümlenemedi ({$url})", 'http_code' => $http_code];
    }
    if ($http_code >= 400) {
        log_message('WARNING', "HTTP Error {$http_code} fetching {$url}", $decoded_response);
        $api_message = $decoded_response['error_description'] ?? $decoded_response['error'] ?? $decoded_response['message'] ?? 'Bilinmeyen API hatası';
        return ['error' => "API hatası ({$http_code}): " . $api_message, 'http_code' => $http_code];
    }
    return $decoded_response;
}

function read_config($config_file) {
    if (!file_exists($config_file)) return null;
    $config_json = @file_get_contents($config_file);
    if ($config_json === false) { log_message('ERROR', "Could not read config file: " . $config_file); return false; }
    $config_data = json_decode($config_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) { log_message('ERROR', "Could not decode config JSON: " . $config_file . " - " . json_last_error_msg()); return false; }
    return $config_data;
}
function write_config($config_file, $config_data) {
    $json_data = json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (json_last_error() !== JSON_ERROR_NONE) { log_message('ERROR', "Error encoding config data for file: " . $config_file . " - " . json_last_error_msg()); return false; }
    if (@file_put_contents($config_file, $json_data, LOCK_EX) === false) { log_message('ERROR', "Error writing to config file: " . $config_file); return false; }
    return true;
}

$config_file = 'spotify_config.json';
$message = "Bilinmeyen bir hata oluştu."; 
$success = false;

$config_data = read_config($config_file);
if ($config_data === false) {
    $message = 'Yapılandırma dosyası okunamadı veya bozuk.';
    log_message('CRITICAL', $message);
} elseif ($config_data === null) {
    $message = 'Spotify yapılandırması bulunamadı. Lütfen önce kurulumu yapın.';
    log_message('WARNING', $message);
} else {
    $client_id = $config_data['client_id'] ?? '';
    $client_secret = $config_data['client_secret'] ?? '';

    if (empty($client_id) || empty($client_secret)) {
        $message = 'Client ID veya Client Secret eksik. Lütfen Spotify ayarlarını kontrol edin.';
        log_message('ERROR', $message);
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) ?? 'localhost';
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_URL) ?? '';
        $redirect_uri = $protocol . $host . $script_name;

        $get_params = filter_input_array(INPUT_GET, [
            'code' => FILTER_SANITIZE_STRING,
            'state' => FILTER_SANITIZE_STRING,
            'error' => FILTER_SANITIZE_STRING,
        ]);
        $code = $get_params['code'] ?? null;
        $state = $get_params['state'] ?? null;
        $error = $get_params['error'] ?? null;
        $session_state = $_SESSION['spotify_auth_state'] ?? null;

        try {
            if (empty($state) || empty($session_state) || !hash_equals($session_state, $state)) {
                $message = 'Geçersiz state parametresi. Yetkilendirme isteği zaman aşımına uğramış veya değiştirilmiş olabilir.';
                log_message('ERROR', $message, ['received_state' => $state, 'session_state' => $session_state]);
                $success = false;
            } elseif ($error) {
                $message = "Spotify yetkilendirme hatası: " . $error;
                log_message('WARNING', $message);
                $success = false;
            } elseif ($code) {
                log_message('INFO', "Received authorization code, requesting token.");
                $token_url = "https://accounts.spotify.com/api/token";
                $auth_header = "Authorization: Basic " . base64_encode("{$client_id}:{$client_secret}");
                $post_body = http_build_query([
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirect_uri,
                ]);

                $token_response = fetch_with_curl($token_url, [
                    'method' => 'POST',
                    'headers' => [$auth_header, 'Content-Type: application/x-www-form-urlencoded'],
                    'body' => $post_body
                ]);

                log_message('DEBUG', "Spotify Token Request Response", $token_response);

                if (isset($token_response['error'])) {
                    $message = "Spotify token alınamadı: " . $token_response['error'];
                    log_message('ERROR', $message, $token_response);
                    $success = false;
                } elseif (isset($token_response['refresh_token'])) {
                    $config_data['refresh_token'] = $token_response['refresh_token'];
                    $config_data['access_token'] = $token_response['access_token'] ?? null; 
                    $config_data['token_expiry'] = isset($token_response['expires_in']) ? (time() + $token_response['expires_in'] - 60) : null;
                    $config_data['updated_at'] = date('Y-m-d H:i:s');

                    if (write_config($config_file, $config_data)) {
                        $message = "Spotify hesabınız başarıyla bağlandı!";
                        log_message('INFO', $message);
                        $success = true;
                         header('Location: spotify-setup.php?connected=1'); 
                         exit;
                    } else {
                        $message = "Refresh token alındı ancak yapılandırma dosyasına yazılamadı.";
                        log_message('CRITICAL', $message);
                        $success = false;
                    }
                } else {
                    $message = "Spotify'dan geçerli bir refresh token alınamadı.";
                    log_message('ERROR', $message, $token_response);
                    $success = false;
                }
            } else {
                $message = "Geçersiz istek. Spotify'dan 'code' veya 'error' parametresi gelmedi.";
                log_message('WARNING', $message, $get_params);
                $success = false;
            }
        } catch (Throwable $e) { 
            $message = "Beklenmeyen bir sunucu hatası oluştu.";
            log_message('CRITICAL', "Exception during Spotify callback: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $success = false;
        } finally {
            unset($_SESSION['spotify_auth_state']);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify Bağlantısı - <?= $success ? 'Başarılı' : 'Hata'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #2563eb;
            --spotify-color: #1DB954;
            --background-dark: #091124;
            --background-darker: #050a18;
            --background-card: #152238;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --success-color: #10b981;
            --error-color: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-dark);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
            background-color: var(--background-card);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
            text-align: center;
        }
        
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: <?= $success ? 'var(--spotify-color)' : 'var(--error-color)'; ?>;
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: <?= $success ? 'var(--success-color)' : 'var(--error-color)'; ?>;
        }
        
        p {
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
        }
        
        .btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <?php if ($success): ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z"/>
            </svg>
            <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z"/>
            </svg>
            <?php endif; ?>
        </div>
        
        <h1><?= $success ? 'Bağlantı Başarılı!' : 'Bağlantı Hatası'; ?></h1>
        <p><?= htmlspecialchars($message); ?></p>

        <a href="spotify-setup.php" class="btn">Kurulum Sayfasına Dön</a>
    </div>
</body>
</html>
