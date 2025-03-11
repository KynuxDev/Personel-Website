<?php
// Sayfa başlığı ve meta verileri
$title = "Discord PHP Entegrasyonu Yardım";
$description = "Discord durumunuzu PHP ile takip etme rehberi";
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
        .help-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(30, 32, 44, 0.8);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .help-title {
            color: #7289DA;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #5865F2;
        }
        
        .step-container {
            margin-bottom: 2rem;
        }
        
        .step-title {
            font-size: 1.3rem;
            color: #5865F2;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .step-title i {
            margin-right: 0.5rem;
        }
        
        .code-block {
            background: #2a2d3e;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
        }
        
        .terminal-command {
            color: #5be78a;
        }
        
        .file-path {
            color: #f1c40f;
        }
        
        .api-key {
            color: #e74c3c;
        }
        
        .status-test {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(40, 43, 58, 0.5);
            border-radius: 8px;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .status-indicator.online { background-color: #43b581; }
        .status-indicator.idle { background-color: #faa61a; }
        .status-indicator.dnd { background-color: #f04747; }
        .status-indicator.offline { background-color: #747f8d; }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .button {
            padding: 0.7rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.2s;
        }
        
        .primary-button {
            background-color: #5865F2;
            color: white;
        }
        
        .secondary-button {
            background-color: #4f545c;
            color: white;
        }
        
        .button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .test-result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            background: rgba(40, 43, 58, 0.5);
            display: none;
        }
        
        .test-success {
            border-left: 4px solid #43b581;
        }
        
        .test-error {
            border-left: 4px solid #f04747;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Discord PHP Entegrasyonu</h1>
            <p>Discord bot yerine PHP ile Discord durumunuzu takip edin</p>
        </header>
        
        <div class="help-container">
            <h2 class="help-title">Discord Durumu Takip Sistemi</h2>
            
            <div class="step-container">
                <h3 class="step-title"><i class="fas fa-info-circle"></i>Genel Bakış</h3>
                <p>
                    Bu sistem, Node.js tabanlı Discord bot yerine doğrudan PHP kullanarak Discord kullanıcı 
                    durumunuzu web sitenizde görüntülemenizi sağlar. Böylece:
                </p>
                <ul>
                    <li>Node.js ve npm paketleri kurmanıza gerek kalmaz</li>
                    <li>Sürekli çalışan bir bot yerine, zamanlanmış görevlerle çalışan daha hafif bir sistem kullanılır</li>
                    <li>Web sitenizle aynı teknoloji yığınını kullanarak bakımı kolaylaşır</li>
                </ul>
            </div>
            
            <div class="step-container">
                <h3 class="step-title"><i class="fas fa-cogs"></i>Sistem Bileşenleri</h3>
                <p>Sistem üç ana bileşenden oluşur:</p>
                
                <div class="code-block">
                    <span class="file-path">discord-api.php</span> - Discord API ile iletişim kuran ve durum bilgilerini kaydeden ana PHP dosyası<br>
                    <span class="file-path">get-status.php</span> - Web sayfanız için Discord ve Spotify durum bilgilerini döndüren API<br>
                    <span class="file-path">discord-update-cron.php</span> - Zamanlanmış görev olarak çalışacak düzenli güncelleme yapan script
                </div>
                
                <p>Bu dosyalar, <span class="file-path">logs/discord_status.json</span> dosyasını kullanarak durum bilgilerini saklar ve paylaşır.</p>
            </div>
            
            <div class="step-container">
                <h3 class="step-title"><i class="fas fa-check-circle"></i>Sistemi Test Et</h3>
                <p>PHP entegrasyonunun doğru çalıştığını kontrol etmek için aşağıdaki testi yapabilirsiniz:</p>
                
                <button id="test-discord-api" class="button primary-button">Discord API Bağlantısını Test Et</button>
                
                <div id="test-result" class="test-result">
                    <h4>Test Sonucu</h4>
                    <div id="test-content"></div>
                </div>
            </div>
            
            <div class="step-container">
                <h3 class="step-title"><i class="fas fa-sync-alt"></i>Node.js Bot'undan Geçiş</h3>
                <p>Discord bot'undan PHP sistemine geçiş için aşağıdaki adımları izleyin:</p>
                
                <ol>
                    <li>
                        <strong>Discord bot'unu kapatın:</strong><br>
                        <div class="code-block">
                            <span class="terminal-command">$ pm2 stop discord-bot</span>
                            veya
                            <span class="terminal-command">$ forever stop discord-bot.js</span>
                            veya botu çalıştıran terminal penceresini kapatın
                        </div>
                    </li>
                    <li>
                        <strong>Cron görevi oluşturun:</strong><br>
                        <p>Windows Task Scheduler veya Linux Cron ile her dakika çalışacak bir görev ayarlayın:</p>
                        <div class="code-block">
                            <strong>Windows:</strong><br>
                            Başlat: <span class="file-path">C:\xampp\php\php.exe</span><br>
                            Parametreler: <span class="terminal-command">-f C:\xampp\htdocs\discord-update-cron.php</span><br>
                            Zamanlama: Her 1 dakikada bir<br><br>
                            
                            <strong>Linux:</strong><br>
                            Komutu çalıştırın: <span class="terminal-command">crontab -e</span><br>
                            Aşağıdaki satırı ekleyin:<br>
                            <span class="terminal-command">* * * * * php /path/to/discord-update-cron.php</span>
                        </div>
                    </li>
                    <li>
                        <strong>Durum takibini test edin:</strong><br>
                        <p>Tarayıcınızda <a href="get-status.php" target="_blank">get-status.php</a> dosyasını açarak JSON yanıtını kontrol edin.</p>
                    </li>
                </ol>
            </div>
            
            <div class="step-container">
                <h3 class="step-title"><i class="fas fa-exclamation-triangle"></i>Sorun Giderme</h3>
                
                <p><strong>Durum güncellenmiyor:</strong></p>
                <div class="code-block">
                    Log dosyalarını kontrol edin:<br>
                    <span class="file-path">logs/discord_debug.log</span><br>
                    <span class="file-path">logs/cron_log.txt</span>
                </div>
                
                <p><strong>API hataları:</strong></p>
                <div class="code-block">
                    Discord token'in doğru ve geçerli olduğundan emin olun<br>
                    <span class="file-path">discord-api.php</span> dosyasında <span class="api-key">$discord_bot_token</span> değişkenini kontrol edin
                </div>
                
                <p><strong>Yetkilendirme hatası:</strong></p>
                <div class="code-block">
                    Bot'unuzun yeterli izinlere sahip olduğundan emin olun:<br>
                    - Bot tokeninizin doğru olduğunu kontrol edin<br>
                    - Discord Developer Portal'da botunuzun "Privileged Gateway Intents" izinlerine sahip olduğunu kontrol edin
                </div>
            </div>
            
            <div class="button-group">
                <a href="index.php" class="button secondary-button">Ana Sayfaya Dön</a>
                <a href="README-discord-php.md" class="button secondary-button">Teknik Dokümantasyon</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testButton = document.getElementById('test-discord-api');
            const testResult = document.getElementById('test-result');
            const testContent = document.getElementById('test-content');
            
            testButton.addEventListener('click', function() {
                testContent.innerHTML = '<p>Discord API bağlantısı test ediliyor...</p>';
                testResult.style.display = 'block';
                testResult.className = 'test-result';
                
                fetch('discord-api.php?' + new Date().getTime())
                    .then(response => response.json())
                    .then(data => {
                        let statusClass = '';
                        let statusName = '';
                        
                        switch(data.status) {
                            case 'online': 
                                statusClass = 'online'; 
                                statusName = 'Çevrimiçi';
                                break;
                            case 'idle': 
                                statusClass = 'idle'; 
                                statusName = 'Boşta';
                                break;
                            case 'dnd': 
                                statusClass = 'dnd'; 
                                statusName = 'Rahatsız Etmeyin';
                                break;
                            default: 
                                statusClass = 'offline'; 
                                statusName = 'Çevrimdışı';
                        }
                        
                        testResult.classList.add('test-success');
                        testContent.innerHTML = `
                            <p>✅ Discord API bağlantısı başarılı!</p>
                            <div class="status-test">
                                <div class="status-indicator ${statusClass}"></div>
                                <div>
                                    <strong>${data.username}</strong> şu anda <strong>${statusName}</strong> durumunda.
                                    <div>Son güncelleme: ${data.last_updated}</div>
                                </div>
                            </div>
                            <p>API yanıtı:</p>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    })
                    .catch(error => {
                        testResult.classList.add('test-error');
                        testContent.innerHTML = `
                            <p>❌ Discord API bağlantısı başarısız!</p>
                            <p>Hata: ${error}</p>
                            <p>Olası çözümler:</p>
                            <ul>
                                <li>discord-api.php dosyasındaki token'i kontrol edin</li>
                                <li>logs/discord_debug.log dosyasını inceleyerek hata detaylarını görün</li>
                                <li>PHP'nin curl uzantısının etkin olduğundan emin olun</li>
                            </ul>
                        `;
                    });
            });
        });
    </script>
    
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>
