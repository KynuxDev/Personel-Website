<?php
session_start();

function getGithubRepositories($username) {
    $url = "https://api.github.com/users/{$username}/repos";
    
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP GitHub Portfolio'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['error' => 'GitHub API\'sine bağlanılamadı.'];
    }
    
    $repositories = json_decode($response, true);
    
    if (isset($repositories['message'])) {
        return ['error' => $repositories['message']];
    }
    
    return $repositories;
}

function sortRepositories($repositories, $sortBy = 'updated') {
    usort($repositories, function($a, $b) use ($sortBy) {
        if ($sortBy === 'stars') {
            return $b['stargazers_count'] - $a['stargazers_count'];
        } else if ($sortBy === 'updated') {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        } else if ($sortBy === 'created') {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        }
        return 0;
    });
    
    return $repositories;
}

function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d.m.Y');
}

function getGithubUserInfo($username) {
    $url = "https://api.github.com/users/{$username}";
    
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP GitHub Portfolio'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['error' => 'GitHub API\'sine bağlanılamadı.'];
    }
    
    $userInfo = json_decode($response, true);
    
    if (isset($userInfo['message'])) {
        return ['error' => $userInfo['message']];
    }
    
    return $userInfo;
}

function getDiscordStatus() {
    // logs dizininin varlığını kontrol et ve yoksa oluştur
    $log_dir = 'logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Discord user ID
    $user_id = '1244181502795976775';
    
    // Lanyard API kullanarak Discord durumunu al
    $api_url = "https://api.lanyard.rest/v1/users/{$user_id}";
    
    try {
        $response = @file_get_contents($api_url);
        
        if ($response === false) {
            // API'ye bağlanılamadı, varsayılan durum döndür
            file_put_contents('logs/discord_error.log', date('Y-m-d H:i:s') . " - API Error: Could not connect to Lanyard API\n", FILE_APPEND);
            return [
                'status' => 'online',
                'game' => '',
                'has_game' => false,
                'username' => 'kynux.dev',
                'discriminator' => '0',
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['success']) || $data['success'] !== true) {
            // API geçersiz yanıt verdi, varsayılan durum döndür
            file_put_contents('logs/discord_error.log', date('Y-m-d H:i:s') . " - API Error: Invalid response from Lanyard API\n", FILE_APPEND);
            return [
                'status' => 'online',
                'game' => '',
                'has_game' => false,
                'username' => 'kynux.dev',
                'discriminator' => '0',
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
        
        // API'den başarılı yanıt
        $discord_data = $data['data'];
        
        // Oyun aktivitesini kontrol et
        $game = '';
        $has_game = false;
        
        if (!empty($discord_data['activities'])) {
            foreach ($discord_data['activities'] as $activity) {
                if (isset($activity['type']) && $activity['type'] === 0) { // Type 0 = Playing
                    $game = $activity['name'] ?? '';
                    $has_game = true;
                    break;
                }
            }
        }
        
        return [
            'status' => $discord_data['discord_status'] ?? 'online',
            'game' => $game,
            'has_game' => $has_game,
            'username' => $discord_data['discord_user']['username'] ?? 'kynux.dev',
            'discriminator' => $discord_data['discord_user']['discriminator'] ?? '0',
            'last_updated' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        file_put_contents('logs/discord_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
        
        // Hata durumunda varsayılan değerleri döndür
        return [
            'status' => 'online',
            'game' => '',
            'has_game' => false,
            'username' => 'kynux.dev',
            'discriminator' => '0',
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
}

function getSpotifyStatus() {
    $env_file = __DIR__ . '/.env';
    $client_id = '';
    $client_secret = '';
    
    if (file_exists($env_file)) {
        $env_vars = parse_ini_file($env_file);
        $client_id = $env_vars['SPOTIFY_CLIENT_ID'] ?? '';
        $client_secret = $env_vars['SPOTIFY_CLIENT_SECRET'] ?? '';
    }
    
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
    
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    $spotify_config_file = 'spotify_config.json';
    $refresh_token = '';
    $config_data = [];
    $access_token = '';
    
    if (file_exists($spotify_config_file)) {
        $config_content = file_get_contents($spotify_config_file);
        $config_data = json_decode($config_content, true);
        $refresh_token = $config_data['refresh_token'] ?? '';
        $access_token = $config_data['access_token'] ?? '';
        $token_expiry = $config_data['token_expiry'] ?? 0;
    }
    
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/spotify-callback.php";
        
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
        
        file_put_contents('logs/spotify_token_response.log', date('Y-m-d H:i:s') . " - Response from auth code: " . $response . "\n", FILE_APPEND);
        
        $data = json_decode($response, true);
        
        if (isset($data['refresh_token']) && isset($data['access_token'])) {
            $refresh_token = $data['refresh_token'];
            $access_token = $data['access_token'];
            $expires_in = $data['expires_in'] ?? 3600;
            
            $config_data['refresh_token'] = $refresh_token;
            $config_data['access_token'] = $access_token;
            $config_data['token_expiry'] = time() + $expires_in;
            $config_data['updated_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($spotify_config_file, json_encode($config_data));
            
            header("Location: index.php");
            exit;
        }
    }
    
    if (empty($refresh_token) && !isset($_GET['code'])) {
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
    
    $token_expiry = $config_data['token_expiry'] ?? 0;
    $now = time();
    
    if ($now >= $token_expiry || empty($access_token)) {
    
        try {
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
            
            file_put_contents('logs/spotify_token.log', date('Y-m-d H:i:s') . " - Token Refresh Response: " . print_r($token_data, true) . "\n", FILE_APPEND);
            
            if (isset($token_data['access_token'])) {
                $access_token = $token_data['access_token'];
                $expires_in = $token_data['expires_in'] ?? 3600;
                
                $config_data['access_token'] = $access_token;
                $config_data['token_expiry'] = time() + $expires_in;
                $config_data['updated_at'] = date('Y-m-d H:i:s');
                
                if (isset($token_data['refresh_token'])) {
                    $config_data['refresh_token'] = $token_data['refresh_token'];
                }
                
                file_put_contents($spotify_config_file, json_encode($config_data));
            } else {
                file_put_contents('logs/spotify_error.log', date('Y-m-d H:i:s') . " - Failed to refresh token: " . print_r($token_data, true) . "\n", FILE_APPEND);
                
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me/player/currently-playing");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$access_token}"
        ]);
        
        $player_response = curl_exec($ch);
        $player_data = json_decode($player_response, true);
        
        curl_close($ch);
        
        file_put_contents('logs/spotify_player.log', date('Y-m-d H:i:s') . " - Player Response: " . print_r($player_data, true) . "\n", FILE_APPEND);
        
        if (isset($player_data['is_playing']) && $player_data['is_playing'] && isset($player_data['item'])) {
            return [
                'is_playing' => true,
                'song' => $player_data['item']['name'],
                'artist' => $player_data['item']['artists'][0]['name'],
                'album_art' => $player_data['item']['album']['images'][1]['url']
            ];
        } else {
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
    
    return [
        'is_playing' => false,
        'song' => '',
        'artist' => '',
        'album_art' => ''
    ];
}

$discord = getDiscordStatus();
$spotify = getSpotifyStatus();

$api_setup_info = "";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kynux.dev - Kişisel Tanıtım</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
</head>
<body>
    <div class="bg-particles" id="particles-js"></div>
    <div class="bg-grid"></div>
    <div class="bg-gradient"></div>
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
            <a href="<?php echo getenv('GITHUB_URL') ?: 'https://github.com/KynuxDev'; ?>" target="_blank"><i class="fab fa-github"></i></a>
            <a href="<?php echo getenv('LINKEDIN_URL') ?: '#'; ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
            <a href="<?php echo getenv('TWITTER_URL') ?: '#'; ?>" target="_blank"><i class="fab fa-twitter"></i></a>
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
                    <h2>Platform <span class="highlight">Durumum</span></h2>
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
                    <h2>Beceri <span class="highlight">Setim</span></h2>
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
                    <h2>GitHub <span class="highlight">Portföyüm</span></h2>
            </div>
            
            <?php
            $github_username = 'KynuxDev';

            $sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'updated';

            $repositories = getGithubRepositories($github_username);
            $userInfo = getGithubUserInfo($github_username);

            $error = null;
            if (isset($repositories['error'])) {
                $error = $repositories['error'];
                $repositories = [];
            } else {
                $repositories = sortRepositories($repositories, $sort_by);
            }
            ?>
            
            <?php if ($error): ?>
                <div class="error-message" data-aos="fade-up">
                    <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php else: ?>
                <div class="github-profile" data-aos="fade-up">
                    <?php if (!isset($userInfo['error'])): ?>
                        <div class="profile-summary">
                            <img class="profile-image" src="<?php echo htmlspecialchars($userInfo['avatar_url']); ?>" alt="<?php echo htmlspecialchars($github_username); ?> profil resmi">
                            <div class="profile-details">
                                <h3 class="profile-name"><?php echo htmlspecialchars($userInfo['name'] ?? $github_username); ?></h3>
                                <p class="profile-username">@<?php echo htmlspecialchars($github_username); ?></p>
                                
                                <?php if (!empty($userInfo['bio'])): ?>
                                    <p class="profile-bio"><?php echo htmlspecialchars($userInfo['bio']); ?></p>
                                <?php endif; ?>
                                
                                <div class="profile-stats">
                                    <div class="stat">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo htmlspecialchars($userInfo['followers']); ?> Takipçi</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-user-plus"></i>
                                        <span><?php echo htmlspecialchars($userInfo['following']); ?> Takip Edilen</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-code-branch"></i>
                                        <span><?php echo count($repositories); ?> Repo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sort-controls" data-aos="fade-up">
                    <div class="sort-options">
                        <a href="?sort=updated" class="<?php echo $sort_by === 'updated' ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Son Güncellenen
                        </a>
                        <a href="?sort=stars" class="<?php echo $sort_by === 'stars' ? 'active' : ''; ?>">
                            <i class="fas fa-star"></i> Yıldızlar
                        </a>
                        <a href="?sort=created" class="<?php echo $sort_by === 'created' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-plus"></i> Yeni Eklenen
                        </a>
                    </div>
                    <div class="search-box">
                        <input type="text" id="repoSearch" placeholder="Repo ara...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <?php if (empty($repositories)): ?>
                    <p class="no-repos" data-aos="fade-up">Hiç repository bulunamadı.</p>
                <?php else: ?>
                    <div class="repos-grid" id="repoGrid" data-aos="fade-up">
                        <?php 
                        $display_repos = array_slice($repositories, 0, 6);
                        foreach ($display_repos as $repo): 
                            if ($repo['fork']) continue;
                            
                            $languageColors = [
                                'JavaScript' => '#f1e05a',
                                'TypeScript' => '#2b7489',
                                'HTML' => '#e34c26',
                                'CSS' => '#563d7c',
                                'PHP' => '#4F5D95',
                                'Python' => '#3572A5',
                                'Java' => '#b07219',
                                'C#' => '#178600',
                                'C++' => '#f34b7d',
                                'Ruby' => '#701516',
                                'Go' => '#00ADD8',
                                'Swift' => '#ffac45',
                                'Kotlin' => '#F18E33',
                                'Rust' => '#dea584',
                            ];
                            
                            $languageColor = isset($languageColors[$repo['language']]) 
                                            ? $languageColors[$repo['language']] 
                                            : '#bbbbbb';
                        ?>
                        <div class="repo-card" data-aos="zoom-in-up">
                            <div class="repo-name">
                                <a href="<?php echo htmlspecialchars($repo['html_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($repo['name']); ?>
                                </a>
                            </div>
                            
                            <?php if (!empty($repo['description'])): ?>
                                <div class="repo-description">
                                    <?php echo htmlspecialchars($repo['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="repo-stats">
                                <span>
                                    <i class="fas fa-star"></i>
                                    <?php echo $repo['stargazers_count']; ?>
                                </span>
                                <span>
                                    <i class="fas fa-code-branch"></i>
                                    <?php echo $repo['forks_count']; ?>
                                </span>
                                <span>
                                    <i class="fas fa-eye"></i>
                                    <?php echo $repo['watchers_count']; ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($repo['language'])): ?>
                                <div class="repo-language">
                                    <span class="language-color" style="background-color: <?php echo $languageColor; ?>"></span>
                                    <?php echo htmlspecialchars($repo['language']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="repo-dates">
                                <span>
                                    <i class="fas fa-calendar-plus"></i> 
                                    <?php echo formatDate($repo['created_at']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-sync-alt"></i> 
                                    <?php echo formatDate($repo['updated_at']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($repositories) > 6): ?>
                    <div class="more-repos-link" data-aos="fade-up">
                        <a href="https://github.com/<?php echo htmlspecialchars($github_username); ?>?tab=repositories" class="btn btn-outline" target="_blank">
                            <i class="fab fa-github"></i> Tüm Projeleri GitHub'da Görüntüle
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
            
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                    <h2>İletişime <span class="highlight">Geç</span></h2>
            </div>
            
            <div class="contact-content">
                <div class="contact-info" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo getenv('CONTACT_EMAIL') ?: 'iletisim@kynux.dev.com'; ?>"><?php echo getenv('CONTACT_EMAIL') ?: 'iletisim@kynux.dev.com'; ?></a>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-github"></i>
                        <a href="<?php echo getenv('GITHUB_URL') ?: 'https://github.com/KynuxDev'; ?>" target="_blank"><?php echo str_replace('https://', '', getenv('GITHUB_URL')) ?: 'github.com/KynuxDev'; ?></a>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-linkedin"></i>
                        <a href="<?php echo getenv('LINKEDIN_URL') ?: 'https://linkedin.com/in/KynuxDev'; ?>" target="_blank"><?php echo str_replace('https://', '', getenv('LINKEDIN_URL')) ?: 'linkedin.com/in/KynuxDev'; ?></a>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-twitter"></i>
                        <a href="<?php echo getenv('TWITTER_URL') ?: 'https://twitter.com/KynuxDev'; ?>" target="_blank"><?php echo getenv('TWITTER_URL') ? '@'.basename(getenv('TWITTER_URL')) : '@KynuxDev'; ?></a>
                    </div>
                </div>
                
                <?php
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <div class="contact-form" data-aos="fade-up" data-aos-delay="400">
                    <form action="process-form.php" method="POST">
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
                    <a href="#platforms">Platformlar</a>
                    <a href="#skills">Yetenekler</a>
                    <a href="#projects">Projeler</a>
                    <a href="#contact">İletişim</a>
                </div>
                
                <div class="footer-social">
                    <a href="<?php echo getenv('GITHUB_URL') ?: 'https://github.com/KynuxDev'; ?>" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="<?php echo getenv('LINKEDIN_URL') ?: '#'; ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <a href="<?php echo getenv('TWITTER_URL') ?: '#'; ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-bottom" data-aos="fade-up">
                <p>&copy; 2025 kynux.dev. Tüm hakları saklıdır.</p>
                <p class="license">BSD 3-Clause "New" or "Revised" License ile lisanslanmıştır</p>
                <p class="credit">Tasarım ile <i class="fas fa-heart" style="color: #ef4444; font-size: 0.8rem;"></i> ve <i class="fas fa-code" style="color: var(--primary-color); font-size: 0.8rem;"></i> arasında...</p>
            </div>
        </div>
    </footer>

    <div class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="particles.js"></script>
    <script src="script.js"></script>
    
    <div class="page-transition-overlay"></div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Neon efektleri için elementleri seç
        const neonElements = document.querySelectorAll('.repo-card, .profile-image, .btn-primary, .category-icon');
        
        // Her element için rastgele parıldama animasyonu ekle
        neonElements.forEach(element => {
            // Rastgele parıldama süresi (3-7 saniye arası)
            const randomDuration = Math.random() * 4 + 3;
            // Rastgele başlangıç gecikmesi (0-5 saniye arası)
            const randomDelay = Math.random() * 5;
            
            element.style.animation = `glow ${randomDuration}s ease-in-out ${randomDelay}s infinite alternate`;
        });
        
        // Interaktif profil resmi efekti
        const profileImage = document.querySelector('.profile-image');
        if (profileImage) {
            profileImage.addEventListener('mousemove', function(e) {
                const bounds = this.getBoundingClientRect();
                const mouseX = e.clientX - bounds.left;
                const mouseY = e.clientY - bounds.top;
                const centerX = bounds.width / 2;
                const centerY = bounds.height / 2;
                
                // İmlecin pozisyonuna göre 3D döndürme hesapla
                const rotateY = ((mouseX - centerX) / centerX) * 15;
                const rotateX = ((centerY - mouseY) / centerY) * 15;
                
                // Dönme efektini uygula
                this.style.transform = `rotateY(${rotateY}deg) rotateX(${rotateX}deg) scale(1.1)`;
                this.style.boxShadow = `
                    ${rotateY / 3}px ${rotateX / 3}px 15px rgba(59, 130, 246, 0.6),
                    0 0 30px rgba(59, 130, 246, 0.4)
                `;
            });
            
            profileImage.addEventListener('mouseleave', function() {
                // Mouse ayrıldığında normal görünüme geri dön
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        }
    });
    </script>
</body>
</html>
