<?php
// Spotify Yetkilendirme Başlatıcı
session_start();

// Spotify API bilgilerini config dosyasından oku
$config_file = 'spotify_config.json';
$client_id = '';

if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    $client_id = $config_data['client_id'] ?? '';
} else {
    // Config dosyası yoksa kurulum sayfasına yönlendir
    header('Location: spotify-setup.php?error=no_config');
    exit;
}

if (empty($client_id)) {
    // Client ID yoksa kurulum sayfasına yönlendir
    header('Location: spotify-setup.php?error=no_client_id');
    exit;
}

// Callback URL
$redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";

// Yetkilendirme URL'si
$auth_url = "https://accounts.spotify.com/authorize" . 
            "?client_id=" . urlencode($client_id) .
            "&response_type=code" .
            "&redirect_uri=" . urlencode($redirect_uri) .
            "&scope=" . urlencode("user-read-currently-playing user-read-playback-state") .
            "&show_dialog=true";

// Kullanıcıyı Spotify'a yönlendir
header("Location: " . $auth_url);
exit;
?>
