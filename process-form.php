<?php
// CSRF token kontrol fonksiyonu
function validateCSRFToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        return false;
    }
    
    return true;
}

// Oturum başlat
session_start();

// Form güvenlik kontrolü
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF koruması
    if (!validateCSRFToken()) {
        $error = "Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.";
        error_log("CSRF doğrulama hatası: " . $_SERVER['REMOTE_ADDR']);
        header("Location: index.php?error=" . urlencode($error));
        exit;
    }
    
    // Form verilerini alma
    $name = isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : "";
    $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : "";
    $message = isset($_POST["message"]) ? htmlspecialchars($_POST["message"]) : "";
    
    // Gerekli alanların doldurulduğunu kontrol etme
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Lütfen tüm alanları doldurun.";
        header("Location: index.php?error=" . urlencode($error));
        exit;
    }
    
    // E-posta adresinin geçerliliğini kontrol etme
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Lütfen geçerli bir e-posta adresi girin.";
        header("Location: index.php?error=" . urlencode($error));
        exit;
    }
    
    // Gerçek bir uygulamada, bu verileri veritabanına kaydedebilir veya e-posta gönderebilirsiniz
    // Örnek: E-posta gönderme
    $to = "contact@kynux.cloud"; // Kendi e-posta adresinizle değiştirin
    $subject = "Yeni İletişim Formu Mesajı";
    $email_content = "İsim: $name\n";
    $email_content .= "E-posta: $email\n\n";
    $email_content .= "Mesaj:\n$message\n";
    
    // E-posta başlıkları - Başlık enjeksiyonunu engelle
    $headers = "From: =?UTF-8?B?" . base64_encode($name) . "?= <" . filter_var($email, FILTER_SANITIZE_EMAIL) . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Örnek amaçlı - gerçek bir e-posta gönderimi için yorum satirlarını kaldırın
    // mail($to, $subject, $email_content, $headers);
    
    // Başarılı mesaj
    $success = "Mesajınız başarıyla gönderildi. Teşekkür ederiz!";
    header("Location: index.php?success=" . urlencode($success));
    exit;
} else {
    // POST olmayan istekleri reddet
    header("Location: index.php");
    exit;
}
?>
