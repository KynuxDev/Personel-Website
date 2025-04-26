<?php
session_start();

$config_file = 'spotify_config.json';
$client_id = '';
$client_secret = '';

if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    $client_id = $config_data['client_id'] ?? '';
    $client_secret = $config_data['client_secret'] ?? '';
} else {
    $message = 'Config dosyası bulunamadı. Lütfen önce Spotify ayarlarını yapılandırın.';
    $success = false;
}

if (empty($client_id) || empty($client_secret)) {
    $message = 'Client ID veya Client Secret bulunamadı. Lütfen Spotify ayarlarını kontrol edin.';
    $success = false;
}
$redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}
$message = "";
$success = false;

try {
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'client_id' => $client_id,
            'client_secret' => $client_secret
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        file_put_contents('logs/spotify_token_response.log', date('Y-m-d H:i:s') . " - Response: " . $response . "\n", FILE_APPEND);
        
        $data = json_decode($response, true);
        
        if (isset($data['refresh_token'])) {
            $refresh_token = $data['refresh_token'];
            
            $config_file = 'spotify_config.json';
            $config_data = [];
            
            if (file_exists($config_file)) {
                $config_content = file_get_contents($config_file);
                $config_data = json_decode($config_content, true);
            }
            
            $config_data['refresh_token'] = $refresh_token;
            $config_data['updated_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($config_file, json_encode($config_data));
            
            $message = "Spotify hesabınız başarıyla bağlandı! Artık dinlediğiniz müzikler profilinizde görüntülenecek.";
            $success = true;
        } else if (isset($data['error'])) {
            $message = "Spotify API Hatası: " . $data['error'];
        } else {
            $message = "Spotify yetkilendirme başarısız oldu. Bir hata oluştu.";
        }
    } else if (isset($_GET['error'])) {
        $message = "Spotify yetkilendirme reddedildi: " . $_GET['error'];
    } else {
        $message = "Geçersiz istek. Authorization code yok.";
    }
} catch (Exception $e) {
    $message = "Beklenmeyen bir hata oluştu: " . $e->getMessage();
    file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify Bağlantısı</title>
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
            color: <?php echo $success ? 'var(--spotify-color)' : 'var(--error-color)'; ?>;
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: <?php echo $success ? 'var(--success-color)' : 'var(--error-color)'; ?>;
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
        
        <h1><?php echo $success ? 'Bağlantı Başarılı!' : 'Bağlantı Hatası'; ?></h1>
        <p><?php echo $message; ?></p>
        
        <a href="index.php" class="btn">Ana Sayfaya Dön</a>
    </div>
</body>
</html>
