<?php
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
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Portföyü - <?php echo htmlspecialchars($github_username); ?></title>
    <link rel="stylesheet" href="modern-portfolio.css">
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
            <a href="https://github.com/<?php echo htmlspecialchars($github_username); ?>" target="_blank" title="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
        </div>
    </header>

    <!-- ANA İÇERİK -->
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <!-- KONTROLLER -->
            <div class="controls">
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
            
            <!-- PROFİL BİLGİSİ -->
            <header>
                <div class="profile">
                    <?php if (!isset($userInfo['error'])): ?>
                        <h1 class="profile-name">GitHub Portföyüm</h1>
                        
                        <div class="profile-bio">
                            <img 
                                src="<?php echo isset($userInfo['avatar_url']) ? htmlspecialchars($userInfo['avatar_url']) : 'https://avatars.githubusercontent.com/u/default?v=4'; ?>" 
                                alt="<?php echo htmlspecialchars($github_username); ?> profil resmi" 
                                class="profile-avatar"
                            >
                            <h2><?php echo isset($userInfo['name']) ? htmlspecialchars($userInfo['name']) : 'Berk'; ?></h2>
                            <p class="username">@<?php echo htmlspecialchars($github_username); ?></p>
                            <p class="bio"><?php echo isset($userInfo['bio']) ? htmlspecialchars($userInfo['bio']) : 'Kendi çapında yazılımcı'; ?></p>
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
                                <span><?php echo count($repositories); ?></span>
                                <span>Repo</span>
                            </div>
                        </div>
                        
                        <div class="profile-links">
                            <?php if (!empty($userInfo['html_url'])): ?>
                                <a href="<?php echo htmlspecialchars($userInfo['html_url']); ?>" target="_blank" class="profile-link">
                                    <i class="fab fa-github"></i> GitHub Profili
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userInfo['blog'])): ?>
                                <a href="<?php echo htmlspecialchars($userInfo['blog']); ?>" target="_blank" class="profile-link">
                                    <i class="fas fa-globe"></i> Web Sitesi
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userInfo['location'])): ?>
                                <div class="profile-location">
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
            <div class="section-header">
                <h3 id="aktif-projeler-liste">
                    <i class="fas fa-code"></i>
                    Aktif Projeler
                </h3>
            </div>
            
            <div class="repo-grid">
                <?php 
                $activeFound = false;
                foreach ($repositories as $repo): 
                    if (!$repo['fork'] && !$repo['archived'] && !$repo['disabled']):
                        $activeFound = true;
                ?>
                <div class="repo-card">
                    <div class="repo-name">
                        <a href="<?php echo htmlspecialchars($repo['html_url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($repo['name']); ?>
                        </a>
                    </div>
                    <div class="repo-description">
                        <?php echo !empty($repo['description']) ? htmlspecialchars($repo['description']) : 'Bu proje için henüz açıklama eklenmemiş.'; ?>
                    </div>
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
                        <span class="language-color" style="background-color: 
                            <?php 
                            $colors = [
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
                                'Rust' => '#dea584'
                            ];
                            echo isset($colors[$repo['language']]) ? $colors[$repo['language']] : '#999';
                            ?>
                        "></span>
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
                <?php 
                    endif;
                endforeach; 
                
                if (!$activeFound):
                ?>
                <p class="empty-message">
                    Henüz aktif proje bulunmuyor... İlk projenizi oluşturmak için GitHub'ı ziyaret edin.
                </p>
                <?php endif; ?>
            </div>
            
            <!-- YILDIZLI PROJELER BÖLÜMÜ -->
            <div class="section-header">
                <h3 id="yildizli-projeler-liste">
                    <i class="fas fa-star"></i>
                    Yıldızlı Projeler
                </h3>
            </div>
            
            <div class="repo-grid">
                <?php 
                $starredFound = false;
                $starredRepos = array_filter($repositories, function($repo) {
                    return $repo['stargazers_count'] > 0;
                });
                
                usort($starredRepos, function($a, $b) {
                    return $b['stargazers_count'] - $a['stargazers_count'];
                });
                
                foreach ($starredRepos as $repo): 
                    $starredFound = true;
                ?>
                <div class="repo-card">
                    <div class="repo-name">
                        <a href="<?php echo htmlspecialchars($repo['html_url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($repo['name']); ?>
                        </a>
                    </div>
                    <div class="repo-description">
                        <?php echo !empty($repo['description']) ? htmlspecialchars($repo['description']) : 'Bu proje için henüz açıklama eklenmemiş.'; ?>
                    </div>
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
                        <span class="language-color" style="background-color: 
                            <?php 
                            $colors = [
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
                                'Rust' => '#dea584'
                            ];
                            echo isset($colors[$repo['language']]) ? $colors[$repo['language']] : '#999';
                            ?>
                        "></span>
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
                <?php 
                endforeach; 
                
                if (!$starredFound):
                ?>
                <p class="empty-message">
                    Henüz yıldızlı proje bulunmuyor... Projelerinizin daha fazla ilgi görmesi için GitHub'da tanıtımlarını yapın!
                </p>
                <?php endif; ?>
            </div>
            
            <!-- SON GÜNCELLEMELER BÖLÜMÜ -->
            <div class="section-header">
                <h3 id="son-guncellemeler-liste">
                    <i class="fas fa-history"></i>
                    Son Güncellemeler
                </h3>
            </div>
            
            <div class="repo-grid">
                <?php 
                $recentRepos = array_slice($repositories, 0, 6);
                if (count($recentRepos) > 0):
                    foreach ($recentRepos as $repo): 
                ?>
                <div class="repo-card">
                    <div class="repo-name">
                        <a href="<?php echo htmlspecialchars($repo['html_url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($repo['name']); ?>
                        </a>
                    </div>
                    <div class="repo-description">
                        <?php echo !empty($repo['description']) ? htmlspecialchars($repo['description']) : 'Bu proje için henüz açıklama eklenmemiş.'; ?>
                    </div>
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
                        <span class="language-color" style="background-color: 
                            <?php 
                            $colors = [
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
                                'Rust' => '#dea584'
                            ];
                            echo isset($colors[$repo['language']]) ? $colors[$repo['language']] : '#999';
                            ?>
                        "></span>
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
                <?php 
                    endforeach;
                else:
                ?>
                <p class="empty-message">
                    Henüz güncel proje bulunmuyor... İlk projenizi oluşturmak için GitHub'ı ziyaret edin.
                </p>
                <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Repo arama işlevi
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
            
            // Animasyon efektleri
            const animateElements = document.querySelectorAll('.repo-card, .section-box, .profile-avatar, .stat');
            animateElements.forEach((element, index) => {
                setTimeout(() => {
                    element.classList.add('fade-in');
                }, index * 100);
            });
        });
    </script>
</body>
</html>
