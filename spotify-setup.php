<?php
session_start();

$title = "Spotify Hesabı Kurulumu";
$description = "Spotify hesabınızı web sitenize bağlayın";
$config_file = 'spotify_config.json';
$connected = false;
$refresh_token = '';
$client_id = '';
$client_secret = '';
$message = '';
$status = '';

function safe_redirect($url) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: ' . $url);
    exit;
}

function get_current_url($params = []) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) ?? 'localhost';
    $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_URL) ?? '';
    $base_url = $protocol . $host . $script_name;
    if (!empty($params)) {
        return $base_url . '?' . http_build_query($params);
    }
    return $base_url;
}

function read_config($config_file) {
    if (!file_exists($config_file)) {
        return null;
    }
    $config_json = file_get_contents($config_file);
    if ($config_json === false) {
        error_log("Error reading config file: " . $config_file);
        return false;
    }
    $config_data = json_decode($config_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding config file JSON: " . $config_file . " - " . json_last_error_msg());
        return false;
    }
    return $config_data;
}

function write_config($config_file, $config_data) {
    $json_data = json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (json_last_error() !== JSON_ERROR_NONE) {
         error_log("Error encoding config data for file: " . $config_file . " - " . json_last_error_msg());
        return false;
    }
    // LOCK_EX bayrağı eklenerek atomik yazma işlemi sağlanır, race condition önlenir.
    if (file_put_contents($config_file, $json_data, LOCK_EX) === false) {
        error_log("Error writing to config file: " . $config_file);
        return false;
    }
    return true;
}

$config_data = read_config($config_file);
if ($config_data === false) {
    $message = 'Yapılandırma dosyası okunurken veya çözümlenirken bir hata oluştu.';
    $status = 'error';
} elseif ($config_data !== null) {
    $client_id = $config_data['client_id'] ?? '';
    $client_secret = $config_data['client_secret'] ?? '';
    $refresh_token = $config_data['refresh_token'] ?? '';
    if (!empty($refresh_token)) {
        $connected = true;
    }
}

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    // Kullanımdan kaldırılan FILTER_SANITIZE_STRING yerine FILTER_DEFAULT kullan.
    // Gelen veriler daha sonra trim edilecek veya doğrudan kullanılacak (örn. action, csrf_token).
    $post_data = filter_input_array(INPUT_POST, [
        'action' => FILTER_DEFAULT,
        'csrf_token' => FILTER_DEFAULT,
        'client_id' => FILTER_DEFAULT,
        'client_secret' => FILTER_DEFAULT, // Secret'ın özel karakter içerme olasılığına karşı FILTER_DEFAULT daha güvenli.
    ]);
    $action = $post_data['action'] ?? '';
    $submitted_csrf_token = $post_data['csrf_token'] ?? '';
    $session_csrf_token = $_SESSION['csrf_token'] ?? '';

    if (empty($session_csrf_token) || !hash_equals($session_csrf_token, $submitted_csrf_token)) {
        $message = 'Geçersiz veya eksik güvenlik anahtarı. Lütfen formu tekrar gönderin.';
        $status = 'error';
    } else {
        $allowed_actions = ['save_credentials', 'disconnect', 'reset'];

        if ($action && in_array($action, $allowed_actions)) {
            $current_config_data = read_config($config_file) ?? [];

            if ($action === 'save_credentials') {
                $client_id_input = trim($post_data['client_id'] ?? '');
                $client_secret_input = trim($post_data['client_secret'] ?? '');

                if (empty($client_id_input) || empty($client_secret_input)) {
                    $message = 'Client ID ve Client Secret alanları zorunludur.';
                    $status = 'error';
                } else {
                    $current_config_data['client_id'] = $client_id_input;
                    $current_config_data['client_secret'] = $client_secret_input;
                    $current_config_data['updated_at'] = date('Y-m-d H:i:s');
                    $current_config_data['refresh_token'] = $current_config_data['refresh_token'] ?? $refresh_token;

                    if (write_config($config_file, $current_config_data)) {
                        $_SESSION['message'] = 'Spotify API bilgileri başarıyla kaydedildi!';
                        $_SESSION['status'] = 'success';
                        safe_redirect(get_current_url(['saved' => 1]));
                    } else {
                         $message = 'Yapılandırma dosyası yazılamadı.';
                         $status = 'error';
                    }
                }
            } elseif ($action === 'disconnect') {
                unset($current_config_data['refresh_token']);
                unset($current_config_data['access_token']);
                unset($current_config_data['token_expiry']);

                if (write_config($config_file, $current_config_data)) {
                    $_SESSION['message'] = 'Spotify hesabınız başarıyla bağlantısı kesildi!';
                    $_SESSION['status'] = 'success';
                    safe_redirect(get_current_url(['disconnected' => 1]));
                } else {
                    $message = 'Yapılandırma dosyası güncellenirken hata oluştu.';
                    $status = 'error';
                }

            } elseif ($action === 'reset') {
                 $reset_error = false;
                 $config_to_save = $current_config_data;

                 unset($config_to_save['refresh_token']);
                 unset($config_to_save['access_token']);
                 unset($config_to_save['token_expiry']);

                 if (!write_config($config_file, $config_to_save)) {
                    error_log("Error writing config file during reset: " . $config_file);
                    $message = 'Yapılandırma dosyası sıfırlanırken hata oluştu.';
                    $status = 'error';
                    $reset_error = true;
                 }

                 $log_files = [
                     'logs/spotify_token_response.log',
                     'logs/spotify_token.log',
                     'logs/spotify_player.log',
                     'logs/spotify_error.log'
                 ];
                 $log_dir = 'logs';
                 if (!is_dir($log_dir)) {
                    if (!mkdir($log_dir, 0755, true)) {
                        error_log("Could not create log directory: " . $log_dir);
                         if (!$reset_error) {
                            $message = 'Log dizini oluşturulamadı.';
                            $status = 'error';
                            $reset_error = true;
                         }
                    }
                 }

                 foreach ($log_files as $log_file) {
                     if (file_exists($log_file)) {
                         if (!unlink($log_file)) {
                             error_log("Error deleting log file: " . $log_file);
                             if (!$reset_error) {
                                 $message = 'Log dosyaları silinirken hata oluştu: ' . basename($log_file);
                                 $status = 'error';
                                 $reset_error = true;
                             }
                         }
                     }
                 }

                 if (!$reset_error) {
                    $_SESSION['message'] = 'Spotify bağlantınız ve log dosyalarınız tamamen sıfırlandı. Lütfen hesabınızı yeniden bağlayın.';
                    $_SESSION['status'] = 'success';
                 }
                 safe_redirect(get_current_url(['reset' => 1]));
            }
        } else {
             $message = 'Geçersiz eylem belirtildi.';
             $status = 'error';
        }
    }
} else {
     if (isset($_SESSION['message'])) {
         $message = $_SESSION['message'];
         $status = $_SESSION['status'] ?? 'info';
         unset($_SESSION['message']);
         unset($_SESSION['status']);

         $get_params = filter_input_array(INPUT_GET, [
             'connected' => FILTER_VALIDATE_BOOLEAN,
             'disconnected' => FILTER_VALIDATE_BOOLEAN,
             'reset' => FILTER_VALIDATE_BOOLEAN,
             'saved' => FILTER_VALIDATE_BOOLEAN,
         ]);

         if ($get_params['connected']) {
             $connected = true;
         } elseif ($get_params['disconnected'] || $get_params['reset']) {
             $connected = false;
             $refresh_token = '';
         }
     }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title); ?></title>
    <meta name="description" content="<?= htmlspecialchars($description); ?>">
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
        
        .reset-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
         .status-info {
            background-color: rgba(240, 173, 78, 0.1);
            border-left: 4px solid #f0ad4e;
            color: #f0ad4e;
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
                <div class="status-message status-<?= htmlspecialchars($status); ?>">
                    <?= htmlspecialchars($message); ?>
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
                    <?php
                        $cb_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
                        $cb_host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) ?? 'localhost';
                        $cb_path = dirname(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_URL) ?? '');
                        $cb_callback_path = rtrim($cb_path, '/') . '/spotify-callback.php';
                        $callback_url = $cb_protocol . $cb_host . $cb_callback_path;
                    ?>
                    <li>Redirect URI olarak: <code><?= htmlspecialchars($callback_url); ?></code> ekleyin</li>
                    <li>Oluşturduğunuz uygulamanın sayfasında "Settings" kısmında Client ID ve Client Secret değerlerini bulacaksınız</li>
                </ol>
            </div>
            
            <div class="step-container">
                <h3 class="step-title">2. API Bilgilerini Kaydet 
                    <span class="status-badge <?= (!empty($client_id) && !empty($client_secret)) ? 'badge-success' : 'badge-waiting'; ?>">
                        <?= (!empty($client_id) && !empty($client_secret)) ? 'Tamamlandı' : 'Bekleniyor'; ?>
                    </span>
                </h3>
                
                <form method="post" action="<?= htmlspecialchars(get_current_url()); ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="save_credentials">

                    <div class="form-group">
                        <label for="client_id">Client ID:</label>
                        <input type="text" id="client_id" name="client_id" value="<?= htmlspecialchars($client_id); ?>" placeholder="Spotify Developer Dashboard'dan alın" required>
                    </div>

                    <div class="form-group">
                        <label for="client_secret">Client Secret:</label>
                        <input type="password" id="client_secret" name="client_secret" value="<?= htmlspecialchars($client_secret); ?>" placeholder="Spotify Developer Dashboard'dan alın" required>
                    </div>
                    
                    <button type="submit" class="button primary-button">API Bilgilerini Kaydet</button>
                </form>
            </div>
            
            <div class="step-container">
                <h3 class="step-title">3. Spotify Hesabını Bağla 
                    <span class="status-badge <?= $connected ? 'badge-success' : 'badge-waiting'; ?>">
                        <?= $connected ? 'Bağlandı' : 'Bekleniyor'; ?>
                    </span>
                </h3>
                
                <?php if (!empty($client_id) && !empty($client_secret)): ?>
                    <?php if ($connected): ?>
                        <p>Spotify hesabınız başarıyla bağlandı! Artık web sitenizde dinlediğiniz müzikleri görüntüleyebilirsiniz.</p>
                        
                        <form method="post" action="<?= htmlspecialchars(get_current_url()); ?>">
                             <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
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
            
            <?php if ($connected): ?>
            <div class="reset-section">
                <h3 class="step-title">4. Bağlantıyı Sıfırla</h3>
                <p>Spotify bağlantınızda sorun yaşıyorsanız, bağlantıyı tamamen sıfırlayabilirsiniz. Bu işlem:</p>
                <ul>
                    <li>Token bilgilerinizi siler (yeniden bağlanmanız gerekir)</li>
                    <li>Log dosyalarını temizler</li>
                    <li>Yeni ve temiz bir bağlantı kurmanızı sağlar</li>
                </ul>
                <form method="post" action="<?= htmlspecialchars(get_current_url()); ?>">
                     <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="reset">
                    <button type="submit" class="button danger-button">Bağlantıyı Sıfırla</button>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="button-group">
                <a href="index.php" class="button secondary-button">Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
</body>
</html>
