<?php
session_start();

function safe_redirect($url) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: ' . $url);
    exit;
}

function validateCSRFToken($submitted_token, $session_token) {
    if (empty($submitted_token) || empty($session_token)) {
        return false;
    }
    return hash_equals($session_token, $submitted_token);
}

function get_index_url($params = []) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) ?? 'localhost';
    
    
    $base_url = $protocol . $host . '/index.php'; 
    if (!empty($params)) {
        return $base_url . '#' . http_build_query($params); 
    }
    return $base_url;
}


if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'POST') {
    safe_redirect(get_index_url()); 
}

$post_data = filter_input_array(INPUT_POST, [
    'csrf_token' => FILTER_SANITIZE_STRING,
    'name' => FILTER_SANITIZE_STRING,
    'email' => FILTER_SANITIZE_EMAIL,
    'message' => FILTER_SANITIZE_STRING,
]);

$submitted_csrf_token = $post_data['csrf_token'] ?? '';
$session_csrf_token = $_SESSION['csrf_token'] ?? '';

if (!validateCSRFToken($submitted_csrf_token, $session_csrf_token)) {
    $_SESSION['form_error'] = "Güvenlik doğrulaması başarısız oldu. Lütfen formu tekrar gönderin.";
    $remote_addr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_URL) ?? 'UNKNOWN';
    error_log("CSRF doğrulama hatası: " . $remote_addr);
    safe_redirect(get_index_url(['error' => 'csrf_invalid'])); 
}

$name = trim($post_data['name'] ?? '');
$email = trim($post_data['email'] ?? '');
$message_body = trim($post_data['message'] ?? ''); 

if (empty($name) || empty($email) || empty($message_body)) {
    $_SESSION['form_error'] = "Lütfen tüm alanları doldurun.";
    safe_redirect(get_index_url(['error' => 'missing_fields']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['form_error'] = "Lütfen geçerli bir e-posta adresi girin.";
    safe_redirect(get_index_url(['error' => 'invalid_email']));
}

$to = "contact@kynux.cloud"; 
$subject = "Yeni İletişim Formu Mesajı";
$email_content = "İsim: " . $name . "\n";
$email_content .= "E-posta: " . $email . "\n\n";
$email_content .= "Mesaj:\n" . $message_body . "\n";

$headers = "From: =?UTF-8?B?" . base64_encode($name) . "?= <" . filter_var($email, FILTER_SANITIZE_EMAIL) . ">\r\n";
$headers .= "Reply-To: " . filter_var($email, FILTER_SANITIZE_EMAIL) . "\r\n"; 
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();


$_SESSION['form_success'] = "Mesajınız başarıyla gönderildi (simülasyon). Teşekkür ederiz!";
safe_redirect(get_index_url(['success' => 1]));

?>
