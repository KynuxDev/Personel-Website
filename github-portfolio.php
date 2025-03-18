<?php
// GitHub API'den kullanıcı repolarını çeken fonksiyon
function getGithubRepositories($username) {
    $url = "https://api.github.com/users/{$username}/repos";
    
    // GitHub API isteği için gerekli ayarlar
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP GitHub Portfolio'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    
    // API'den veri çekme
    $response = @file_get_contents($url, false, $context);
    
    // Hata kontrolü
    if ($response === false) {
        return ['error' => 'GitHub API\'sine bağlanılamadı.'];
    }
    
    // JSON verisini diziye çevirme
    $repositories = json_decode($response, true);
    
    // API hata kontrolü
    if (isset($repositories['message'])) {
        return ['error' => $repositories['message']];
    }
    
    return $repositories;
}

// Repoların özelliklerine göre sıralanması
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

// Zaman okunaklı formata çevirme
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d.m.Y');
}

// Kullanıcı bilgilerini çekme
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

// GitHub kullanıcı adı
$github_username = 'KynuxDev';

// Sıralama seçeneği (varsayılan: güncelleme tarihine göre)
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'updated';

// Repoları ve kullanıcı bilgilerini çekme
$repositories = getGithubRepositories($github_username);
$userInfo = getGithubUserInfo($github_username);

// Hata kontrolü
$error = null;
if (isset($repositories['error'])) {
    $error = $repositories['error'];
    $repositories = [];
} else {
    $repositories = sortRepositories($repositories, $sort_by);
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Portföyü - <?php echo htmlspecialchars($github_username); ?></title>
    <link rel="stylesheet" href="github-portfolio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- HEADER BÖLÜMÜ -->
    <header class="site-header">
        <div class="logo">
            <a href="index.php">Kynux<span>Dev</span></a>
        </div>
        <div class="nav-container">
            <nav>
                <ul>
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="#aktif-projeler">Platformlar</a></li>
                    <li><a href="#yildizli-projeler">Yetenekler</a></li>
                    <li><a href="#son-guncellemeler">Projeler</a></li>
                    <li><a href="#">İletişim</a></li>
                </ul>
            </nav>
        </div>
        <div class="social-links">
            <a href="#" title="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
        </div>
    </header>

    <!-- ANA İÇERİK -->
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message">
                <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <!-- KONTROLLER -->
            <div class="controls">
                <div class="sort-options">
                    <a href="#son-guncellemeler" class="<?php echo $sort_by === 'updated' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Son Güncellenen
                    </a>
                    <a href="#yildizli-projeler" class="<?php echo $sort_by === 'stars' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> Yıldızlar
                    </a>
                    <a href="#aktif-projeler" class="<?php echo $sort_by === 'created' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-plus"></i> Yeni Eklenen
                    </a>
                </div>
                <div class="search-box">
                    <input type="text" id="repoSearch" placeholder="Repo ara...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <!-- PROFİL BİLGİSİ -->
            <header>
                <div class="profile">
                    <?php if (!isset($userInfo['error'])): ?>
                        <h1 class="profile-name">GitHub Portföyüm</h1>
                        
                        <div class="profile-bio">
                            <img src="data:image/png;base64,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" alt="KynuxDev profil resmi" style="width: 90px; height: 90px; border-radius: 50%; margin-bottom: 15px;">
                            <h2 style="margin: 5px 0; font-size: 1.5rem; color: var(--text-primary);">Berk</h2>
                            <p style="margin: 5px 0; color: var(--text-secondary);">@<?php echo htmlspecialchars($github_username); ?></p>
                            <p style="margin: 5px 0; color: var(--text-secondary);">Kendi çapında yazılımcı</p>
                        </div>
                        
                        <div class="profile-stats">
                            <div class="stat">
                                <i class="fas fa-users"></i>
                                <span><?php echo htmlspecialchars($userInfo['followers']); ?></span>
                                <span>Takipçi</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-user-plus"></i>
                                <span><?php echo htmlspecialchars($userInfo['following']); ?></span>
                                <span>Takip Edilen</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-code-branch"></i>
                                <span>1</span>
                                <span>Repo</span>
                            </div>
                        </div>
                        
                        <div class="profile-links">
                            <?php if (!empty($userInfo['html_url'])): ?>
                                <a href="<?php echo htmlspecialchars($userInfo['html_url']); ?>" target="_blank">
                                    <i class="fab fa-github"></i> GitHub Profili
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userInfo['blog'])): ?>
                                <a href="<?php echo htmlspecialchars($userInfo['blog']); ?>" target="_blank">
                                    <i class="fas fa-globe"></i> Web Sitesi
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userInfo['location'])): ?>
                                <div>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($userInfo['location']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <h1 class="profile-name"><?php echo htmlspecialchars($github_username); ?></h1>
                    <?php endif; ?>
                </div>
            </header>
            
            <!-- KATEGORİ KUTULARI -->
            <div class="section-container">
                <div id="aktif-projeler" class="section-box">
                    <div class="section-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="section-title">Aktif Projeler</h3>
                    <p class="section-desc">GitHub üzerindeki aktif geliştirme projelerim</p>
                </div>
                
                <div id="yildizli-projeler" class="section-box">
                    <div class="section-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="section-title">Yıldızlı Projeler</h3>
                    <p class="section-desc">En çok yıldız alan açık kaynak projelerim</p>
                </div>
                
                <div id="son-guncellemeler" class="section-box">
                    <div class="section-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="section-title">Son Güncellemeler</h3>
                    <p class="section-desc">En son üzerinde çalıştığım projeler</p>
                </div>
            </div>
            
            <!-- AKTİF PROJELER BÖLÜMÜ -->
            <h3 id="aktif-projeler-liste" style="margin-top: 3rem; margin-bottom: 1rem; text-align: center;">
                <i class="fas fa-code" style="color: var(--primary-color);"></i>
                Aktif Projeler
            </h3>
            
            <div class="repo-grid">
                <!-- AWebSite projesi -->
                <div class="repo-card">
                    <div class="repo-name">
                        <a href="#" target="_blank">AWebSite</a>
                    </div>
                    <div class="repo-description">
                        Claude API ve OpenAI tabanlı çok kullanıcılı yapay zeka sohbet uygulaması. Birden fazla AI modeli desteği, özelleştirilebilir sistem komutları, düşünme modu ve token istatistikleri özelliklerine sahip.
                    </div>
                    <div class="repo-stats">
                        <span>
                            <i class="fas fa-star"></i>
                            0
                        </span>
                        <span>
                            <i class="fas fa-code-branch"></i>
                            0
                        </span>
                        <span>
                            <i class="fas fa-eye"></i>
                            0
                        </span>
                    </div>
                    <div class="repo-language">
                        <span class="language-color" style="background-color: #4F5D95"></span>
                        PHP
                    </div>
                    <div class="repo-dates">
                        <span>
                            <i class="fas fa-calendar-plus"></i> 
                            10.03.2025
                        </span>
                        <span>
                            <i class="fas fa-sync-alt"></i> 
                            10.03.2025
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- YILDIZLI PROJELER BÖLÜMÜ -->
            <h3 id="yildizli-projeler-liste" style="margin-top: 3rem; margin-bottom: 1rem; text-align: center;">
                <i class="fas fa-star" style="color: var(--primary-color);"></i>
                Yıldızlı Projeler
            </h3>
            
            <div class="repo-grid">
                <!-- Yıldızlı proje yok durumunda mesaj -->
                <p style="text-align: center; width: 100%; grid-column: 1/-1; color: var(--text-secondary); font-style: italic;">
                    Henüz yıldızlı proje bulunmuyor...
                </p>
            </div>
            
            <!-- SON GÜNCELLEMELER BÖLÜMÜ -->
            <h3 id="son-guncellemeler-liste" style="margin-top: 3rem; margin-bottom: 1rem; text-align: center;">
                <i class="fas fa-history" style="color: var(--primary-color);"></i>
                Son Güncellemeler
            </h3>
            
            <div class="repo-grid">
                <!-- AWebSite projesi tekrar -->
                <div class="repo-card">
                    <div class="repo-name">
                        <a href="#" target="_blank">AWebSite</a>
                    </div>
                    <div class="repo-description">
                        Claude API ve OpenAI tabanlı çok kullanıcılı yapay zeka sohbet uygulaması. Birden fazla AI modeli desteği, özelleştirilebilir sistem komutları, düşünme modu ve token istatistikleri özelliklerine sahip.
                    </div>
                    <div class="repo-stats">
                        <span>
                            <i class="fas fa-star"></i>
                            0
                        </span>
                        <span>
                            <i class="fas fa-code-branch"></i>
                            0
                        </span>
                        <span>
                            <i class="fas fa-eye"></i>
                            0
                        </span>
                    </div>
                    <div class="repo-language">
                        <span class="language-color" style="background-color: #4F5D95"></span>
                        PHP
                    </div>
                    <div class="repo-dates">
                        <span>
                            <i class="fas fa-calendar-plus"></i> 
                            10.03.2025
                        </span>
                        <span>
                            <i class="fas fa-sync-alt"></i> 
                            10.03.2025
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- FOOTER BÖLÜMÜ -->
    <footer class="site-footer">
        <div class="copyright">
            © 2025 KynuxDev. Tüm hakları saklıdır.
        </div>
        <div class="footer-tagline">
            Tasarım ile <i class="fas fa-heart"></i> ve <i class="fas fa-code"></i> arasında...
        </div>
    </footer>
    
    <!-- GERİ DÖN BUTONU -->
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
    </a>
    
    <script>
        // Repo arama işlevi
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('repoSearch');
            const repoCards = document.querySelectorAll('.repo-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                repoCards.forEach(card => {
                    const repoName = card.querySelector('.repo-name').textContent.toLowerCase();
                    const repoDesc = card.querySelector('.repo-description') ? 
                                    card.querySelector('.repo-description').textContent.toLowerCase() : '';
                    const repoLang = card.querySelector('.repo-language') ?
                                    card.querySelector('.repo-language').textContent.toLowerCase() : '';
                    
                    if (repoName.includes(searchTerm) || repoDesc.includes(searchTerm) || repoLang.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            // Bölüm kartlarına tıklama işlevi
            const sectionBoxes = document.querySelectorAll('.section-box');
            sectionBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    const id = this.id + '-liste';
                    const element = document.getElementById(id);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        });
    </script>
</body>
</html>
