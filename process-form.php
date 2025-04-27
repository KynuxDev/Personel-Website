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
    'csrf_token' => FILTER_DEFAULT, // Basit string, özel temizlemeye gerek yok, zaten hash_equals ile karşılaştırılıyor.
    'name' => FILTER_DEFAULT,       // htmlspecialchars ile temizlenecek
    'email' => FILTER_SANITIZE_EMAIL, // E-posta formatına özel temizleme
    'message' => FILTER_DEFAULT,    // htmlspecialchars ile temizlenecek
]);

$submitted_csrf_token = $post_data['csrf_token'] ?? '';
$session_csrf_token = $_SESSION['csrf_token'] ?? '';

if (!validateCSRFToken($submitted_csrf_token, $session_csrf_token)) {
    $_SESSION['form_error'] = "Güvenlik doğrulaması başarısız oldu. Lütfen formu tekrar gönderin.";
    $remote_addr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_URL) ?? 'UNKNOWN';
    error_log("CSRF doğrulama hatası: " . $remote_addr);
    safe_redirect(get_index_url(['error' => 'csrf_invalid'])); 
}

// Girişleri al ve temel temizlik yap (trim)
$raw_name = trim($post_data['name'] ?? '');
$raw_email = trim($post_data['email'] ?? '');
$raw_message = trim($post_data['message'] ?? '');

// XSS ve diğer enjeksiyonlara karşı temizle
$name = htmlspecialchars($raw_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$email = $raw_email; // Zaten filter_var ile doğrulanacak ve sanitize edilecek
$message_body = htmlspecialchars($raw_message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

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
// E-posta içeriğini oluştururken temizlenmiş değişkenleri kullan
// Not: $name ve $message_body zaten yukarıda htmlspecialchars ile temizlendi.
// E-posta 'text/plain' olduğu için HTML'e gerek yok ama yine de güvenli.
$email_content = "İsim: " . $name . "\n";
$email_content .= "E-posta: " . $email . "\n\n"; // E-posta zaten doğrulanmış/temizlenmiş olacak
$email_content .= "Mesaj:\n" . $message_body . "\n";

// Güvenli e-posta başlıkları oluştur
$from_email = "noreply@kynux.cloud"; // Sabit, güvenli bir gönderici adresi
$from_name = "İletişim Formu"; // Sabit veya site adı
$reply_to_email = filter_var($email, FILTER_SANITIZE_EMAIL); // Kullanıcının GÜVENLİ hale getirilmiş e-postası
$reply_to_name = $name; // Kullanıcının temizlenmiş adı

$headers = "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <" . $from_email . ">\r\n";
// Reply-To başlığını kullanıcının doğrulanmış/temizlenmiş e-postası olarak ayarla
$headers .= "Reply-To: =?UTF-8?B?" . base64_encode($reply_to_name) . "?= <" . $reply_to_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();


$_SESSION['form_success'] = "Mesajınız başarıyla gönderildi (simülasyon). Teşekkür ederiz!";
safe_redirect(get_index_url(['success' => 1]));

?>
