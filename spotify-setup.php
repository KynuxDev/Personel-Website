<?php
session_start();
$title = "Spotify Hesabı Kurulumu";
$description = "Spotify hesabınızı web sitenize bağlayın";
$config_file = 'spotify_config.json';
$connected = false;
$refresh_token = '';

if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    if (isset($config_data['refresh_token']) && !empty($config_data['refresh_token'])) {
        $connected = true;
        $refresh_token = $config_data['refresh_token'];
    }
}
$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_credentials') {
        $client_id = trim($_POST['client_id'] ?? '');
        $client_secret = trim($_POST['client_secret'] ?? '');
        
        if (empty($client_id) || empty($client_secret)) {
            $message = 'Client ID ve Client Secret zorunludur.';
            $status = 'error';
        } else {
            $config_data = [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if ($connected && $refresh_token) {
                $config_data['refresh_token'] = $refresh_token;
            }
            file_put_contents($config_file, json_encode($config_data, JSON_PRETTY_PRINT));
            
            $message = 'Spotify API bilgileri başarıyla kaydedildi!';
            $status = 'success';
            header('Location: ' . $_SERVER['PHP_SELF'] . '?saved=1');
            exit;
        }
    } elseif ($action === 'disconnect') {
        if (file_exists($config_file)) {
            $config_data = json_decode(file_get_contents($config_file), true);
            if (isset($config_data['refresh_token'])) {
                unset($config_data['refresh_token']);
                file_put_contents($config_file, json_encode($config_data, JSON_PRETTY_PRINT));
            }
        }
        
        $message = 'Spotify hesabınız başarıyla bağlantısı kesildi!';
        $status = 'success';
        $connected = false;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?disconnected=1');
        exit;
    }
}

if (isset($_GET['saved'])) {
    $message = 'Spotify API bilgileri başarıyla kaydedildi!';
    $status = 'success';
} elseif (isset($_GET['disconnected'])) {
    $message = 'Spotify hesabınız başarıyla bağlantısı kesildi!';
    $status = 'success';
} elseif (isset($_GET['connected'])) {
    $message = 'Spotify hesabınız başarıyla bağlandı!';
    $status = 'success';
    $connected = true;
}

$client_id = '';
$client_secret = '';

if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    $client_id = $config_data['client_id'] ?? '';
    $client_secret = $config_data['client_secret'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo $description; ?>">
    <link rel="stylesheet" href="style.css">
    <style>
        .setup-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(30, 32, 44, 0.8);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .setup-title {
            color: #1DB954;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #1DB954;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 1rem;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        
        .button {
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.2s;
        }
        
        .primary-button {
            background-color: #1DB954;
            color: white;
        }
        
        .primary-button:hover {
            background-color: #1ED760;
        }
        
        .secondary-button {
            background-color: #4f545c;
            color: white;
        }
        
        .secondary-button:hover {
            background-color: #5d636e;
        }
        
        .danger-button {
            background-color: #f04747;
            color: white;
        }
        
        .danger-button:hover {
            background-color: #f25d5d;
        }
        
        .status-message {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .status-success {
            background-color: rgba(29, 185, 84, 0.1);
            border-left: 4px solid #1DB954;
            color: #1DB954;
        }
        
        .status-error {
            background-color: rgba(240, 71, 71, 0.1);
            border-left: 4px solid #f04747;
            color: #f04747;
        }
        
        .step-container {
            margin-bottom: 2rem;
        }
        
        .step-title {
            font-size: 1.2rem;
            color: #1DB954;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #1DB954;
            color: white;
        }
        
        .badge-waiting {
            background-color: #f0ad4e;
            color: white;
        }
        
        .info-text {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
        
        .code-block {
            background: #2a2d3e;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Spotify Hesabı Kurulumu</h1>
            <p>Web sitenizde Spotify çalma durumunuzu göstermek için hesabınızı bağlayın</p>
        </header>
        
        <div class="setup-container">
            <h2 class="setup-title">Spotify API Entegrasyonu</h2>
            
            <?php if (!empty($message)): ?>
                <div class="status-message status-<?php echo $status; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="step-container">
                <h3 class="step-title">1. Spotify Developer Hesabı 
                    <span class="status-badge badge-success">Gerekli</span>
                </h3>
                <p>Spotify API'sini kullanabilmek için Spotify Developer hesabınız olmalıdır.</p>
                <ol>
                    <li><a href="https://developer.spotify.com/dashboard/" target="_blank">Spotify Developer Dashboard</a>'a gidin ve giriş yapın</li>
                    <li>"Create An App" butonuna tıklayın</li>
                    <li>Uygulamanıza bir isim ve açıklama verin (örn. "Kişisel Web Sitesi")</li>
                    <li>Redirect URI olarak: <code><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php"; ?></code> ekleyin</li>
                    <li>Oluşturduğunuz uygulamanın sayfasında "Settings" kısmında Client ID ve Client Secret değerlerini bulacaksınız</li>
                </ol>
            </div>
            
            <div class="step-container">
                <h3 class="step-title">2. API Bilgilerini Kaydet 
                    <span class="status-badge <?php echo (!empty($client_id) && !empty($client_secret)) ? 'badge-success' : 'badge-waiting'; ?>">
                        <?php echo (!empty($client_id) && !empty($client_secret)) ? 'Tamamlandı' : 'Bekleniyor'; ?>
                    </span>
                </h3>
                
                <form method="post" action="">
                    <input type="hidden" name="action" value="save_credentials">
                    
                    <div class="form-group">
                        <label for="client_id">Client ID:</label>
                        <input type="text" id="client_id" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>" placeholder="Spotify Developer Dashboard'dan alın">
                    </div>
                    
                    <div class="form-group">
                        <label for="client_secret">Client Secret:</label>
                        <input type="password" id="client_secret" name="client_secret" value="<?php echo htmlspecialchars($client_secret); ?>" placeholder="Spotify Developer Dashboard'dan alın">
                    </div>
                    
                    <button type="submit" class="button primary-button">API Bilgilerini Kaydet</button>
                </form>
            </div>
            
            <div class="step-container">
                <h3 class="step-title">3. Spotify Hesabını Bağla 
                    <span class="status-badge <?php echo $connected ? 'badge-success' : 'badge-waiting'; ?>">
                        <?php echo $connected ? 'Bağlandı' : 'Bekleniyor'; ?>
                    </span>
                </h3>
                
                <?php if (!empty($client_id) && !empty($client_secret)): ?>
                    <?php if ($connected): ?>
                        <p>Spotify hesabınız başarıyla bağlandı! Artık web sitenizde dinlediğiniz müzikleri görüntüleyebilirsiniz.</p>
                        
                        <form method="post" action="">
                            <input type="hidden" name="action" value="disconnect">
                            <button type="submit" class="button danger-button">Spotify Hesabını Ayır</button>
                        </form>
                    <?php else: ?>
                        <p>API bilgileriniz kaydedildi. Şimdi Spotify hesabınızı bağlamak için aşağıdaki butona tıklayın:</p>
                        <a href="spotify-auth.php" class="button primary-button">Spotify Hesabını Bağla</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Önce Adım 2'yi tamamlayın ve API bilgilerinizi kaydedin.</p>
                <?php endif; ?>
            </div>
            
            <div class="button-group">
                <a href="index.php" class="button secondary-button">Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
</body>
</html>
