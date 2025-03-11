<?php
function getDiscordStatus() {
    $status_file = 'logs/discord_status.json';
    
    // Status dosyası var mı kontrol et
    if (file_exists($status_file)) {
        try {
            // Status dosyasından veriyi oku
            $status_content = file_get_contents($status_file);
            $status_data = json_decode($status_content, true);
            
            // Dosya başarıyla okundu ve içeriği geçerli JSON ise
            if ($status_data && isset($status_data['status'])) {
                // Dosyadan gelen verileri döndür
                return [
                    'status' => $status_data['status'],
                    'game' => $status_data['game'] ?? '',
                    'has_game' => $status_data['has_game'] ?? false,
                    'username' => $status_data['username'] ?? 'kynux.dev',
                    'discriminator' => $status_data['discriminator'] ?? '0000'
                ];
            }
        } catch (Exception $e) {
            // JSON ayrıştırma hatası
            file_put_contents('logs/discord_error.log', date('Y-m-d H:i:s') . " - JSON Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    // Varsayılan değerler (dosya yoksa veya okunamazsa)
    return [
        'status' => 'online',
        'game' => '',  // Varsayılan olarak boş bırak
        'has_game' => false,
        'username' => 'kynux.dev',
        'discriminator' => '0000'
    ];
}

// Spotify API Bağlantısı - Tam Otomatik Token Yenileme/Alma
function getSpotifyStatus() {
    // Spotify API bilgileri - Güvenli şekilde .env dosyasından al
    $env_file = __DIR__ . '/.env';
    $client_id = '';
    $client_secret = '';
    
    // .env dosyası varsa, değerleri al
    if (file_exists($env_file)) {
        $env_vars = parse_ini_file($env_file);
        $client_id = $env_vars['SPOTIFY_CLIENT_ID'] ?? '';
        $client_secret = $env_vars['SPOTIFY_CLIENT_SECRET'] ?? '';
    }
    
    // API kimlik bilgileri yoksa, erken çık
    if (empty($client_id) || empty($client_secret)) {
        error_log("Spotify yapılandırma hatası: API kimlik bilgileri bulunamadı.");
        return [
            'is_playing' => false,
            'song' => '',
            'artist' => '',
            'album_art' => '',
            'error' => 'Yapılandırma hatası'
        ];
    }
    
    // Logs klasörü kontrolü
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    // Config dosyası için kontrol
    $spotify_config_file = 'spotify_config.json';
    $refresh_token = '';
    $config_data = [];
    $access_token = '';
    
    // Eğer dosya varsa, okuyalım
    if (file_exists($spotify_config_file)) {
        $config_content = file_get_contents($spotify_config_file);
        $config_data = json_decode($config_content, true);
        $refresh_token = $config_data['refresh_token'] ?? '';
        $access_token = $config_data['access_token'] ?? '';
        $token_expiry = $config_data['token_expiry'] ?? 0;
    }
    
    // Spotify code geldi mi kontrol et (OAuth callback'ten)
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";
        
        // Token almak için API isteği yap
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'client_id' => $client_id,
            'client_secret' => $client_secret
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Token yanıtını logla ve işle
        file_put_contents('logs/spotify_token_response.log', date('Y-m-d H:i:s') . " - Response from auth code: " . $response . "\n", FILE_APPEND);
        
        $data = json_decode($response, true);
        
        if (isset($data['refresh_token']) && isset($data['access_token'])) {
            // Tokenleri kaydet
            $refresh_token = $data['refresh_token'];
            $access_token = $data['access_token'];
            $expires_in = $data['expires_in'] ?? 3600;
            
            $config_data['refresh_token'] = $refresh_token;
            $config_data['access_token'] = $access_token;
            $config_data['token_expiry'] = time() + $expires_in;
            $config_data['updated_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($spotify_config_file, json_encode($config_data));
            
            // Sayfayı temiz URL'e yönlendir (code parametresini kaldır)
            header("Location: index.php");
            exit;
        }
    }
    
        // Eğer refresh token yoksa veya kod gelmemişse, yetkilendirme URL'sine yönlendir
    if (empty($refresh_token) && !isset($_GET['code'])) {
        // Auto-redirect yerine sadece varsayılan değerler gösterelim
        // Ve bir sonraki yüklemede kullanıcı bağlantıya tıklayabilir
        $redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";
        $auth_url = "https://accounts.spotify.com/authorize?client_id={$client_id}&response_type=code&redirect_uri=" . urlencode($redirect_uri) . "&scope=user-read-currently-playing%20user-read-playback-state&show_dialog=true";
        
        file_put_contents('logs/spotify_auth.log', date('Y-m-d H:i:s') . " - Spotify yetkilendirmesi gerekiyor. URL: {$auth_url}\n", FILE_APPEND);
        
        return [
            'is_playing' => false, // Gerçek durumu yansıtacak şekilde değiştirildi
            'song' => '',
            'artist' => '',
            'album_art' => '',
            'auth_required' => true,
            'auth_url' => $auth_url
        ];
    }
    
    // Token geçerlilik süresini kontrol et
    $token_expiry = $config_data['token_expiry'] ?? 0;
    $now = time();
    
    // Eğer access token süresi dolmuşsa veya yoksa, refresh token ile yenile
    if ($now >= $token_expiry || empty($access_token)) {
    
        try {
            // Refresh token ile yeni access token al
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=refresh_token&refresh_token={$refresh_token}");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Basic " . base64_encode("{$client_id}:{$client_secret}")
            ]);
            
            $token_response = curl_exec($ch);
            $token_data = json_decode($token_response, true);
            
            // Debug için token yanıtını logla
            file_put_contents('logs/spotify_token.log', date('Y-m-d H:i:s') . " - Token Refresh Response: " . print_r($token_data, true) . "\n", FILE_APPEND);
            
            if (isset($token_data['access_token'])) {
                $access_token = $token_data['access_token'];
                $expires_in = $token_data['expires_in'] ?? 3600;
                
                // Config dosyasını güncelle
                $config_data['access_token'] = $access_token;
                $config_data['token_expiry'] = time() + $expires_in;
                $config_data['updated_at'] = date('Y-m-d H:i:s');
                
                // Eğer yeni bir refresh token geldiyse, onu da kaydet
                if (isset($token_data['refresh_token'])) {
                    $config_data['refresh_token'] = $token_data['refresh_token'];
                }
                
                file_put_contents($spotify_config_file, json_encode($config_data));
            } else {
                // Token alınamadıysa hata logla
                file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - Failed to refresh token: " . print_r($token_data, true) . "\n", FILE_APPEND);
                
                // Yeniden yetkilendirme süreci başlat
                $redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";
                $auth_url = "https://accounts.spotify.com/authorize?client_id={$client_id}&response_type=code&redirect_uri=" . urlencode($redirect_uri) . "&scope=user-read-currently-playing%20user-read-playback-state&show_dialog=true";
                
                return [
                    'is_playing' => false, // Gerçek durumu yansıtacak şekilde değiştirildi
                    'song' => '',
                    'artist' => '',
                    'album_art' => '',
                    'auth_required' => true,
                    'auth_url' => $auth_url
                ];
            }
        } catch (Exception $e) {
            file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - Token refresh error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    try {
        // Access token ile çalan şarkı bilgisini al
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me/player/currently-playing");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$access_token}"
        ]);
        
        $player_response = curl_exec($ch);
        $player_data = json_decode($player_response, true);
        
        curl_close($ch);
        
        // Debug için player yanıtını logla
        file_put_contents('logs/spotify_player.log', date('Y-m-d H:i:s') . " - Player Response: " . print_r($player_data, true) . "\n", FILE_APPEND);
        
    // Eğer şu anda bir şarkı çalıyorsa ve veri doğru geldiyse
        if (isset($player_data['is_playing']) && $player_data['is_playing'] && isset($player_data['item'])) {
            return [
                'is_playing' => true,
                'song' => $player_data['item']['name'],
                'artist' => $player_data['item']['artists'][0]['name'],
                'album_art' => $player_data['item']['album']['images'][1]['url']
            ];
        } else {
            // Şarkı çalmıyorsa veya veri gelmiyorsa
            return [
                'is_playing' => false,
                'song' => '',
                'artist' => '',
                'album_art' => ''
            ];
        }
    } catch (Exception $e) {
        file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - API error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    // API hatası durumunda
    return [
        'is_playing' => false,
        'song' => '',
        'artist' => '',
        'album_art' => ''
    ];
}

// API'lerden veri çekme
$discord = getDiscordStatus();
$spotify = getSpotifyStatus();

// Discord ve Spotify API kurulumu konusunda bilgilendirme
$api_setup_info = "
<!--
API KURULUM BİLGİSİ:

1. Discord API için:
   - Discord Developer Portal'a git: https://discord.com/developers/applications
   - Yeni bir uygulama oluştur
   - Bot sekmesinde bot oluştur ve token'ı kopyala
   - Bu token'ı yukarıda YOUR_DISCORD_BOT_TOKEN yerine yapıştır
   - Kullanıcı ID'nizi Discord'da geliştirici modunu açarak bulabilirsiniz

2. Spotify API için:
   - Spotify Developer Dashboard'a git: https://developer.spotify.com/dashboard
   - Yeni bir uygulama oluştur
   - Client ID ve Client Secret'ı kopyala
   - Bir refresh token almak için OAuth akışını kullanmalısınız:
     https://developer.spotify.com/documentation/general/guides/authorization-guide/
   - Alınan bilgileri yukarıdaki ilgili değişkenlere yapıştır
-->
";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kynux.dev - Kişisel Tanıtım</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <span>Kynux<span class="highlight">Dev</span></span>
        </div>
        <ul class="nav-links">
            <li><a href="#home" class="active">Ana Sayfa</a></li>
            <li><a href="#platforms">Platformlar</a></li>
            <li><a href="#skills">Yetenekler</a></li>
            <li><a href="#projects">Projeler</a></li>
            <li><a href="#contact">İletişim</a></li>
        </ul>
        <div class="social-links">
            <a href="https://github.com/kynux.dev" target="_blank"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
                <h1 class="title">Full Stack <span class="highlight">Developer</span> & <br>Yazılım <span class="highlight">Mimarı</span></h1>
                <p class="subtitle">Modern web teknolojileri ve yazılım mimarisi konusunda uzmanlaşmış, yenilikçi çözümler üreten bir geliştirici.</p>
                <div class="cta-buttons">
                    <a href="#contact" class="btn btn-primary">İletişime Geç</a>
                    <a href="#projects" class="btn btn-outline">Projelerimi Gör</a>
                </div>
            </div>
        </div>
        <div class="scroll-indicator" data-aos="fade-up" data-aos-delay="400">
            <div class="mouse">
                <div class="wheel"></div>
            </div>
            <div class="arrow">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </section>

    <section id="platforms" class="platforms-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div id="platformDurumumBaslik">
                    <h2>Platform <span class="highlight">Durumum</span></h2>
                </div>
            </div>
            
            <div class="platforms-grid">
                <div class="platform-card discord-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="platform-icon">
                        <i class="fab fa-discord"></i>
                    </div>
                    <div class="platform-info">
                        <h3>Discord</h3>
                        <div class="status-indicator <?php echo $discord['status']; ?>">
                            <span class="status-dot"></span>
                            <span class="status-text">
                                <?php 
                                    switch($discord['status']) {
                                        case 'online': echo 'Çevrimiçi'; break;
                                        case 'idle': echo 'Boşta'; break;
                                        case 'dnd': echo 'Rahatsız Etmeyin'; break;
                                        default: echo 'Çevrimdışı';
                                    }
                                ?>
                            </span>
                        </div>
                        <?php if (!empty($discord['game'])): ?>
                        <div class="activity">
                            <p><i class="fas fa-gamepad"></i> Oynanıyor: <span><?php echo $discord['game']; ?></span></p>
                        </div>
                        <?php else: ?>
                        <div class="no-activity">
                            <p><i class="fas fa-gamepad"></i> Oyun oynamıyor</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="last-update-time">Son güncelleme: <?php echo date('H:i:s'); ?></div>
                    </div>
                </div>
                
                <div class="platform-card spotify-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="platform-icon">
                        <i class="fab fa-spotify"></i>
                    </div>
                    <div class="platform-info">
                        <h3>Spotify</h3>
                        <?php if ($spotify['is_playing']): ?>
                        <div class="now-playing">
                            <div class="album-art">
                                <img src="<?php echo $spotify['album_art']; ?>" alt="Album Art">
                                <div class="playing-animation">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                            <div class="song-info">
                                <p class="song-title"><?php echo $spotify['song']; ?></p>
                                <p class="artist"><?php echo $spotify['artist']; ?></p>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo isset($spotify['progress_percent']) ? $spotify['progress_percent'] : 0; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="not-playing">
                            <p>Şu anda müzik dinlenmiyor</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="last-update-time">Son güncelleme: <?php echo date('H:i:s'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="skills" class="skills-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div id="beceriSetimBaslik">
                    <h2>Beceri <span class="highlight">Setim</span></h2>
                </div>
            </div>
            
            <div class="skills-content">
                <div class="skills-card" data-aos="fade-up" data-aos-delay="200">
                    <h3>Programlama Dilleri</h3>
                    <div class="skills-list">
                        <div class="skill-item" data-percentage="90">
                            <div class="skill-info">
                                <span class="skill-name">JavaScript</span>
                                <span class="skill-percentage">90%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item" data-percentage="85">
                            <div class="skill-info">
                                <span class="skill-name">Python</span>
                                <span class="skill-percentage">85%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item" data-percentage="95">
                            <div class="skill-info">
                                <span class="skill-name">HTML/CSS</span>
                                <span class="skill-percentage">95%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item" data-percentage="75">
                            <div class="skill-info">
                                <span class="skill-name">PHP</span>
                                <span class="skill-percentage">75%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="skills-card" data-aos="fade-up" data-aos-delay="400">
                    <h3>Yabancı Diller</h3>
                    <div class="skills-list">
                        <div class="skill-item" data-percentage="95">
                            <div class="skill-info">
                                <span class="skill-name">İngilizce</span>
                                <span class="skill-percentage">95%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item" data-percentage="65">
                            <div class="skill-info">
                                <span class="skill-name">Almanca</span>
                                <span class="skill-percentage">65%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                        
                        <div class="skill-item" data-percentage="40">
                            <div class="skill-info">
                                <span class="skill-name">Japonca</span>
                                <span class="skill-percentage">40%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="projects" class="projects-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div id="githubPortfoyumBaslik">
                    <h2>GitHub <span class="highlight">Portföyüm</span></h2>
                </div>
            </div>
            
            <div class="projects-grid">
                <div class="project-card" data-aos="zoom-in-up" data-aos-delay="200">
                    <div class="project-content">
                        <div class="project-header">
                            <h3 class="project-title">E-Ticaret Platformu</h3>
                            <div class="project-links">
                                <a href="https://github.com/kynux.dev/ecommerce" target="_blank" class="project-link"><i class="fab fa-github"></i></a>
                                <a href="https://github.com/kynux.dev" target="_blank" class="project-link"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </div>
                        <p class="project-desc">Tam kapsamlı bir e-ticaret çözümü. Ürün yönetimi, sepet işlemleri ve ödeme entegrasyonu içerir.</p>
                        <div class="project-tech">
                            <span class="tech-tag">React</span>
                            <span class="tech-tag">Node.js</span>
                            <span class="tech-tag">MongoDB</span>
                            <span class="tech-tag">Stripe API</span>
                        </div>
                    </div>
                </div>
                
                <div class="project-card" data-aos="zoom-in-up" data-aos-delay="400">
                    <div class="project-content">
                        <div class="project-header">
                            <h3 class="project-title">Akıllı Ev Yönetim Sistemi</h3>
                            <div class="project-links">
                                <a href="https://github.com/kynux.dev/smart-home" target="_blank" class="project-link"><i class="fab fa-github"></i></a>
                                <a href="https://github.com/kynux.dev" target="_blank" class="project-link"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </div>
                        <p class="project-desc">IoT cihazlarını kontrol etmek için mobil uygulama. Aydınlatma, sıcaklık ve güvenlik sistemlerini yönetir.</p>
                        <div class="project-tech">
                            <span class="tech-tag">Flutter</span>
                            <span class="tech-tag">Firebase</span>
                            <span class="tech-tag">MQTT</span>
                            <span class="tech-tag">Raspberry Pi</span>
                        </div>
                    </div>
                </div>
                
                <div class="project-card" data-aos="zoom-in-up" data-aos-delay="600">
                    <div class="project-content">
                        <div class="project-header">
                            <h3 class="project-title">Yapay Zeka Sohbet Botu</h3>
                            <div class="project-links">
                                <a href="https://github.com/kynux.dev/ai-chatbot" target="_blank" class="project-link"><i class="fab fa-github"></i></a>
                                <a href="https://github.com/kynux.dev" target="_blank" class="project-link"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </div>
                        <p class="project-desc">Doğal dil işleme kullanan interaktif chatbot. Müşteri hizmetleri ve bilgi edinme amaçlı kullanılabilir.</p>
                        <div class="project-tech">
                            <span class="tech-tag">Python</span>
                            <span class="tech-tag">TensorFlow</span>
                            <span class="tech-tag">NLP</span>
                            <span class="tech-tag">AWS Lambda</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div id="iletisimeGecBaslik">
                    <h2>İletişime <span class="highlight">Geç</span></h2>
                </div>
            </div>
            
            <div class="contact-content">
                <div class="contact-info" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:iletisim@kynux.dev.com">iletisim@kynux.dev.com</a>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-github"></i>
                        <a href="https://github.com/kynux.dev" target="_blank">github.com/kynux.dev</a>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-linkedin"></i>
                        <a href="https://linkedin.com/in/kynux.dev" target="_blank">linkedin.com/in/kynux.dev</a>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-twitter"></i>
                        <a href="https://twitter.com/kynux.dev" target="_blank">@kynux.dev</a>
                    </div>
                </div>
                
                <?php 
                // CSRF token oluştur
                session_start();
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <div class="contact-form" data-aos="fade-up" data-aos-delay="400">
                    <form action="process-form.php" method="POST">
                        <!-- CSRF koruması -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <input type="text" id="name" name="name" placeholder="Adınız" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="E-posta Adresiniz" required>
                        </div>
                        
                        <div class="form-group">
                            <textarea id="message" name="message" placeholder="Mesajınız" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Gönder</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content" data-aos="fade-up">
                <div class="footer-logo">
                    <span>Kynux<span class="highlight">Dev</span></span>
                </div>
                
                <div class="footer-nav">
                    <a href="#home">Ana Sayfa</a>
                    <a href="#platforms">Platformlar</a>
                    <a href="#skills">Yetenekler</a>
                    <a href="#projects">Projeler</a>
                    <a href="#contact">İletişim</a>
                </div>
                
                <div class="footer-social">
                    <a href="https://github.com/kynux.dev" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-bottom" data-aos="fade-up">
                <p>&copy; 2025 kynux.dev. Tüm hakları saklıdır.</p>
                <p class="credit">Tasarım ile <i class="fas fa-heart" style="color: #ef4444; font-size: 0.8rem;"></i> ve <i class="fas fa-code" style="color: var(--primary-color); font-size: 0.8rem;"></i> arasında...</p>
            </div>
        </div>
    </footer>

    <div class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- AOS Kütüphanesi - Sayfa kaydırma animasyonları için -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="script.js"></script>
    
    <!-- Sayfa geçiş efekti için arka plan elementi -->
    <div class="page-transition-overlay"></div>
</body>
</html>
