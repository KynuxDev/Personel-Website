<?php
session_start();

$config_file = 'spotify_config.json';

function safe_redirect($url) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Location: ' . $url);
    exit;
}

function read_config($config_file) {
    if (!file_exists($config_file)) return null;
    $config_json = @file_get_contents($config_file);
    if ($config_json === false) { error_log("Error reading config file: " . $config_file); return false; }
    $config_data = json_decode($config_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) { error_log("Error decoding config file JSON: " . $config_file . " - " . json_last_error_msg()); return false; }
    return $config_data;
}

function get_spotify_callback_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) ?? 'localhost';
    $script_dir = dirname(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_URL) ?? '');
    $callback_path = rtrim($script_dir, '/') . '/spotify-callback.php';
    return $protocol . $host . $callback_path;
}

$config_data = read_config($config_file);

if ($config_data === false) {
    $_SESSION['message'] = 'Yapılandırma dosyası okunamadı veya bozuk.';
    $_SESSION['status'] = 'error';
    safe_redirect('spotify-setup.php');
}
if ($config_data === null) {
    $_SESSION['message'] = 'Spotify yapılandırması bulunamadı. Lütfen önce kurulumu yapın.';
    $_SESSION['status'] = 'error';
    safe_redirect('spotify-setup.php');
}

$client_id = $config_data['client_id'] ?? '';
if (empty($client_id)) {
    $_SESSION['message'] = 'Client ID eksik. Lütfen Spotify ayarlarını kontrol edin.';
    $_SESSION['status'] = 'error';
    safe_redirect('spotify-setup.php');
}

$state = bin2hex(random_bytes(16));
$_SESSION['spotify_auth_state'] = $state;

$redirect_uri = get_spotify_callback_url();

$scopes = "user-read-currently-playing user-read-playback-state"; 
$auth_params = [
    'response_type' => 'code',
    'client_id' => $client_id,
    'scope' => $scopes,
    'redirect_uri' => $redirect_uri,
    'state' => $state,
    'show_dialog' => 'true' 
];
$auth_url = "https://accounts.spotify.com/authorize?" . http_build_query($auth_params);

safe_redirect($auth_url);

?>
