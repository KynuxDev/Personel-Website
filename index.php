<?php
session_start();

function fetch_url_with_curl($url, $user_agent = 'PHP App') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log("cURL Error fetching {$url}: {$curl_error}");
        return ['error' => 'Veri alınırken bir hata oluştu. URL: ' . $url];
    }

    if ($http_code >= 400) {
        error_log("HTTP Error {$http_code} fetching {$url}");
         $decoded_response = json_decode($response, true);
         $api_message = $decoded_response['message'] ?? 'API hatası';
        return ['error' => "API'den hata alındı ({$http_code}): " . $api_message . ". URL: " . $url];
    }
    
    return json_decode($response, true);
}

function getGithubRepositories($username) {
    $url = "https://api.github.com/users/{$username}/repos";
    $repositories = fetch_url_with_curl($url, 'PHP GitHub Portfolio');

    return $repositories;
}

function sortRepositories($repositories, $sortBy = 'updated') {
    $validSorts = ['updated', 'stars', 'created'];
    $sortBy = in_array($sortBy, $validSorts) ? $sortBy : 'updated';

    usort($repositories, function($a, $b) use ($sortBy) {
        switch ($sortBy) {
            case 'stars':
                return ($b['stargazers_count'] ?? 0) - ($a['stargazers_count'] ?? 0);
            case 'created':
                return (strtotime($b['created_at'] ?? 0) ?: 0) - (strtotime($a['created_at'] ?? 0) ?: 0);
            case 'updated':
            default:
                return (strtotime($b['updated_at'] ?? 0) ?: 0) - (strtotime($a['updated_at'] ?? 0) ?: 0);
        }
    });

    return $repositories;
}

function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d.m.Y');
}

function getGithubUserInfo($username) {
    $url = "https://api.github.com/users/{$username}";
    $userInfo = fetch_url_with_curl($url, 'PHP GitHub Portfolio');
    
    return $userInfo;
}


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_path = dirname($_SERVER['SCRIPT_NAME']);
$base_path = rtrim($base_path, '/');
$api_url = $protocol . $host . $base_path . '/get-status.php';

$statusData = fetch_url_with_curl($api_url);

$default_discord = [
    'status' => 'offline',
    'game' => '',
    'has_game' => false,
    'username' => 'kynux.dev',
    'discriminator' => '0'
];
$default_spotify = [
    'is_playing' => false,
    'song' => '',
    'artist' => '',
    'album_art' => '',
    'progress_percent' => 0
];

$discord = $default_discord;
$spotify = $default_spotify;

if (isset($statusData) && !isset($statusData['error'])) {
    $discord = array_merge($default_discord, $statusData['discord'] ?? []);
    $spotify = array_merge($default_spotify, $statusData['spotify'] ?? []);
} elseif (isset($statusData['error'])) {
    error_log("Error fetching status data from {$api_url}: " . $statusData['error']);
}

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
            <a href="<?= htmlspecialchars(getenv('GITHUB_URL') ?? 'https://github.com/KynuxDev') ?>" target="_blank"><i class="fab fa-github"></i></a>
            <a href="<?= htmlspecialchars(getenv('LINKEDIN_URL') ?? '#') ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
            <a href="<?= htmlspecialchars(getenv('TWITTER_URL') ?? '#') ?>" target="_blank"><i class="fab fa-twitter"></i></a>
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
                        <div class="status-indicator <?= htmlspecialchars($discord['status'] ?? 'offline') ?>">
                            <span class="status-dot"></span>
                            <span class="status-text">
                                <?php
                                    $status_text = 'Çevrimdışı';
                                    switch($discord['status'] ?? 'offline') {
                                        case 'online': $status_text = 'Çevrimiçi'; break;
                                        case 'idle': $status_text = 'Boşta'; break;
                                        case 'dnd': $status_text = 'Rahatsız Etmeyin'; break;
                                    }
                                    echo htmlspecialchars($status_text);
                                ?>
                            </span>
                        </div>
                        <?php if (!empty($discord['game'])): ?>
                        <div class="activity">
                            <p><i class="fas fa-gamepad"></i> Oynanıyor: <span><?= htmlspecialchars($discord['game']) ?></span></p>
                        </div>
                        <?php else: ?>
                        <div class="no-activity">
                            <p><i class="fas fa-gamepad"></i> Oyun oynamıyor</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="last-update-time">Son güncelleme: <?= htmlspecialchars(date('H:i:s')) ?></div>
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
                                <img src="<?= htmlspecialchars($spotify['album_art'] ?? '') ?>" alt="Album Art">
                                <div class="playing-animation">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                            <div class="song-info">
                                <p class="song-title"><?= htmlspecialchars($spotify['song'] ?? '') ?></p>
                                <p class="artist"><?= htmlspecialchars($spotify['artist'] ?? '') ?></p>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?= htmlspecialchars($spotify['progress_percent'] ?? 0) ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="not-playing">
                            <p>Şu anda müzik dinlenmiyor</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="last-update-time">Son güncelleme: <?= htmlspecialchars(date('H:i:s')) ?></div>
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

            $allowed_sort_options = ['updated', 'stars', 'created'];
            $sort_by = 'updated';
            // GET parametresini filter_input ile al ve beyaz listeye göre kontrol et (github-portfolio.php ile tutarlı hale getir)
            $sort_input = filter_input(INPUT_GET, 'sort', FILTER_DEFAULT);
            if ($sort_input && in_array($sort_input, $allowed_sort_options)) {
                $sort_by = $sort_input;
            }

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
                    <p><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
                </div>
            <?php else: ?>
                <div class="github-profile" data-aos="fade-up">
                    <?php if (!isset($userInfo['error'])): ?>
                        <div class="profile-summary">
                            <img class="profile-image" src="<?= htmlspecialchars($userInfo['avatar_url'] ?? '') ?>" alt="<?= htmlspecialchars($github_username) ?> profil resmi">
                            <div class="profile-details">
                                <h3 class="profile-name"><?= htmlspecialchars($userInfo['name'] ?? $github_username) ?></h3>
                                <p class="profile-username">@<?= htmlspecialchars($github_username) ?></p>

                                <?php if (!empty($userInfo['bio'])): ?>
                                    <p class="profile-bio"><?= htmlspecialchars($userInfo['bio']) ?></p>
                                <?php endif; ?>

                                <div class="profile-stats">
                                    <div class="stat">
                                        <i class="fas fa-users"></i>
                                        <span><?= htmlspecialchars($userInfo['followers'] ?? 0) ?> Takipçi</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-user-plus"></i>
                                        <span><?= htmlspecialchars($userInfo['following'] ?? 0) ?> Takip Edilen</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-code-branch"></i>
                                        <span><?= count($repositories) ?> Repo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sort-controls" data-aos="fade-up">
                    <div class="sort-options">
                        <a href="?sort=updated" class="<?= $sort_by === 'updated' ? 'active' : '' ?>">
                            <i class="fas fa-clock"></i> Son Güncellenen
                        </a>
                        <a href="?sort=stars" class="<?= $sort_by === 'stars' ? 'active' : '' ?>">
                            <i class="fas fa-star"></i> Yıldızlar
                        </a>
                        <a href="?sort=created" class="<?= $sort_by === 'created' ? 'active' : '' ?>">
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
                                <a href="<?= htmlspecialchars($repo['html_url'] ?? '#') ?>" target="_blank">
                                    <?= htmlspecialchars($repo['name'] ?? 'N/A') ?>
                                </a>
                            </div>
                            
                            <?php if (!empty($repo['description'])): ?>
                                <div class="repo-description">
                                    <?= htmlspecialchars($repo['description'] ?? '') ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="repo-stats">
                                <span>
                                    <i class="fas fa-star"></i>
                                    <?= htmlspecialchars($repo['stargazers_count'] ?? 0) ?>
                                </span>
                                <span>
                                    <i class="fas fa-code-branch"></i>
                                    <?= htmlspecialchars($repo['forks_count'] ?? 0) ?>
                                </span>
                                <span>
                                    <i class="fas fa-eye"></i>
                                    <?= htmlspecialchars($repo['watchers_count'] ?? 0) ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($repo['language'])): ?>
                                <div class="repo-language">
                                    <span class="language-color" style="background-color: <?= htmlspecialchars($languageColor) ?>"></span>
                                    <?= htmlspecialchars($repo['language'] ?? 'N/A') ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="repo-dates">
                                <span>
                                    <i class="fas fa-calendar-plus"></i> 
                                    <?= htmlspecialchars(formatDate($repo['created_at'] ?? '')) ?>
                                </span>
                                <span>
                                    <i class="fas fa-sync-alt"></i>
                                    <?= htmlspecialchars(formatDate($repo['updated_at'] ?? '')) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($repositories) > 6): ?>
                    <div class="more-repos-link" data-aos="fade-up">
                        <a href="https://github.com/<?= htmlspecialchars($github_username) ?>?tab=repositories" class="btn btn-outline" target="_blank">
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
                        <a href="mailto:<?= htmlspecialchars(getenv('CONTACT_EMAIL') ?? 'iletisim@kynux.dev.com') ?>"><?= htmlspecialchars(getenv('CONTACT_EMAIL') ?? 'iletisim@kynux.dev.com') ?></a>
                    </div>

                    <div class="contact-item">
                        <i class="fab fa-github"></i>
                        <a href="<?= htmlspecialchars(getenv('GITHUB_URL') ?? 'https://github.com/KynuxDev') ?>" target="_blank"><?= htmlspecialchars(str_replace('https://', '', getenv('GITHUB_URL') ?? 'github.com/KynuxDev')) ?></a>
                    </div>

                    <div class="contact-item">
                        <i class="fab fa-linkedin"></i>
                        <a href="<?= htmlspecialchars(getenv('LINKEDIN_URL') ?? 'https://linkedin.com/in/KynuxDev') ?>" target="_blank"><?= htmlspecialchars(str_replace('https://', '', getenv('LINKEDIN_URL') ?? 'linkedin.com/in/KynuxDev')) ?></a>
                    </div>

                    <div class="contact-item">
                        <i class="fab fa-twitter"></i>
                        <a href="<?= htmlspecialchars(getenv('TWITTER_URL') ?? 'https://twitter.com/KynuxDev') ?>" target="_blank"><?= htmlspecialchars(getenv('TWITTER_URL') ? '@'.basename(getenv('TWITTER_URL')) : '@KynuxDev') ?></a>
                    </div>
                </div>
                
                <?php
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <div class="contact-form" data-aos="fade-up" data-aos-delay="400">
                    <form action="process-form.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
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
                    <a href="<?= htmlspecialchars(getenv('GITHUB_URL') ?? 'https://github.com/KynuxDev') ?>" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="<?= htmlspecialchars(getenv('LINKEDIN_URL') ?? '#') ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <a href="<?= htmlspecialchars(getenv('TWITTER_URL') ?? '#') ?>" target="_blank"><i class="fab fa-twitter"></i></a>
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
</body>
</html>
