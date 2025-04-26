<?php
session_start();

$config_file = 'spotify_config.json';
$client_id = '';

if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    $client_id = $config_data['client_id'] ?? '';
} else {
    header('Location: spotify-setup.php?error=no_config');
    exit;
}

if (empty($client_id)) {
    header('Location: spotify-setup.php?error=no_client_id');
    exit;
}

$redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";

$auth_url = "https://accounts.spotify.com/authorize" . 
            "?client_id=" . urlencode($client_id) .
            "&response_type=code" .
            "&redirect_uri=" . urlencode($redirect_uri) .
            "&scope=" . urlencode("user-read-currently-playing user-read-playback-state") .
            "&show_dialog=true";

header("Location: " . $auth_url);
exit;
?>
