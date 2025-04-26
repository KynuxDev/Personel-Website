<?php

function printHeader($title) {
    echo "\n\033[1;36m======== $title ========\033[0m\n";
}

function printSuccess($message) {
    echo "\033[0;32m✓ $message\033[0m\n";
}

function printError($message) {
    echo "\033[0;31m✗ $message\033[0m\n";
}

function printInfo($message) {
    echo "\033[0;33m→ $message\033[0m\n";
}

echo "\033[1;35m\n";
echo "==================================================\n";
echo "    KYNUX.DEV SİSTEM ENTEGRASYON TESTİ\n";
echo "==================================================\n";
echo "\033[0m\n";

echo "Test başlangıç zamanı: " . date('Y-m-d H:i:s') . "\n";

printHeader("DOSYA YAPISI KONTROLÜ");

$requiredFiles = [
    'index.php' => 'Ana sayfa dosyası',
    'style.css' => 'Ana stil dosyası',
    'script.js' => 'JavaScript dosyası',
    'get-status.php' => 'Durum API dosyası',
    'discord-bot/discord-bot.js' => 'Discord Bot ana dosyası',
    'discord-bot/discord-api.php' => 'Discord API entegrasyonu',
    'discord-bot/README.md' => 'Discord Bot dokümantasyonu',
    'discord-bot/package.json' => 'Bot bağımlılıkları',
    'README.md' => 'Proje dokümantasyonu'
];

$missingFiles = [];
foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        printSuccess("$file: $description - MEVCUT");
    } else {
        printError("$file: $description - BULUNAMADI");
        $missingFiles[] = $file;
    }
}

if (count($missingFiles) > 0) {
    printError("Toplam " . count($missingFiles) . " dosya eksik!");
} else {
    printSuccess("Tüm gerekli dosyalar mevcut.");
}

printHeader("KLASÖR YAPISI KONTROLÜ");

$requiredDirs = [
    'discord-bot' => 'Discord Bot klasörü',
    'discord-bot/logs' => 'Bot log klasörü',
    'logs' => 'Ana log klasörü'
];

$missingDirs = [];
foreach ($requiredDirs as $dir => $description) {
    if (is_dir($dir)) {
        printSuccess("$dir: $description - MEVCUT");
    } else {
        printError("$dir: $description - BULUNAMADI");
        if (strpos($dir, 'logs') !== false) {
            printInfo("$dir klasörü oluşturuluyor...");
            mkdir($dir, 0755, true);
            if (is_dir($dir)) {
                printSuccess("$dir klasörü başarıyla oluşturuldu.");
            } else {
                printError("$dir klasörü oluşturulamadı!");
                $missingDirs[] = $dir;
            }
        } else {
            $missingDirs[] = $dir;
        }
    }
}

if (count($missingDirs) > 0) {
    printError("Toplam " . count($missingDirs) . " klasör eksik!");
} else {
    printSuccess("Tüm gerekli klasörler mevcut.");
}

printHeader("DISCORD DURUM KONTROLÜ");

$discordStatusPaths = [
    'discord-bot/logs/discord_status.json',
    'logs/discord_status.json'
];

$statusFileFound = false;
foreach ($discordStatusPaths as $path) {
    if (file_exists($path)) {
        $statusFileFound = true;
        printSuccess("Discord durum dosyası bulundu: $path");
        
        $content = file_get_contents($path);
        $statusData = json_decode($content, true);
        
        if ($statusData && isset($statusData['status'])) {
            printSuccess("Durum dosyası geçerli JSON içeriyor.");
            printInfo("Kullanıcı: " . ($statusData['username'] ?? 'bilinmiyor'));
            printInfo("Durum: " . ($statusData['status'] ?? 'bilinmiyor'));
            if (!empty($statusData['game'])) {
                printInfo("Oyun: " . $statusData['game']);
            }
            printInfo("Son güncelleme: " . ($statusData['last_updated'] ?? date('Y-m-d H:i:s')));
        } else {
            printError("Durum dosyası geçersiz veya boş!");
        }
        
        break;
    }
}

if (!$statusFileFound) {
    printError("Discord durum dosyası bulunamadı!");
    printInfo("Örnek durum dosyası oluşturuluyor...");
    
    $exampleStatus = [
        'status' => 'online',
        'game' => 'Test Game',
        'has_game' => true,
        'username' => 'kynux.dev',
        'discriminator' => '0',
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    $targetDir = 'discord-bot/logs';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    file_put_contents("$targetDir/discord_status.json", json_encode($exampleStatus, JSON_PRETTY_PRINT));
    
    if (file_exists("$targetDir/discord_status.json")) {
        printSuccess("Örnek durum dosyası oluşturuldu: $targetDir/discord_status.json");
    } else {
        printError("Örnek durum dosyası oluşturulamadı!");
    }
}

printHeader("API TESTİ");

try {
    printInfo("get-status.php API'si test ediliyor...");
    
    $apiUrl = "http://" . $_SERVER['HTTP_HOST'] . "/get-status.php";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response === false) {
        printError("API erişilemedi: $apiUrl");
    } else {
        $responseData = json_decode($response, true);
        
        if ($responseData && isset($responseData['discord'])) {
            printSuccess("API başarıyla yanıt verdi.");
            printInfo("Discord durumu: " . $responseData['discord']['status']);
            printInfo("Server zamanı: " . $responseData['server_time']);
        } else {
            printError("API geçersiz yanıt döndürdü!");
            printInfo("Yanıt: " . substr($response, 0, 100) . "...");
        }
    }
} catch (Exception $e) {
    printError("API testi sırasında hata: " . $e->getMessage());
}

printHeader("TEST SONUÇLARI");

echo "\nTüm kontroller tamamlandı.\n";
echo "Eksik dosyalar: " . (count($missingFiles) > 0 ? implode(", ", $missingFiles) : "Yok") . "\n";
echo "Eksik klasörler: " . (count($missingDirs) > 0 ? implode(", ", $missingDirs) : "Yok") . "\n\n";

printHeader("KONFİGÜRASYON İPUÇLARI");

echo "1. Discord bot'u çalıştırmak için:\n";
echo "   - Windows: start-discord-service.bat dosyasını çalıştırın\n";
echo "   - Manuel: cd discord-bot && npm start\n\n";

echo "2. Discord bot yapılandırması için:\n";
echo "   - discord-bot/.env.example dosyasını discord-bot/.env olarak kopyalayın\n";
echo "   - Discord Developer Portal'dan token oluşturun\n";
echo "   - Takip etmek istediğiniz kullanıcı ID'sini ekleyin\n\n";

echo "3. Spotify API yapılandırması için:\n";
echo "   - .env.example dosyasını .env olarak kopyalayın\n";
echo "   - Spotify Developer Dashboard'dan uygulama oluşturun\n";
echo "   - Client ID ve Secret değerlerini .env dosyasına ekleyin\n\n";

echo "Test tamamlandı. " . date('Y-m-d H:i:s') . "\n";
