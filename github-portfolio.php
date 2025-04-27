<?php
function fetch_with_curl($url, $options = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
    curl_setopt($ch, CURLOPT_USERAGENT, $options['user_agent'] ?? 'PHP-GitHub-Portfolio/1.0');
    if (!empty($options['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
    }
    
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error_num = curl_errno($ch);
    $curl_error_msg = curl_error($ch);
    curl_close($ch);

    if ($curl_error_num) {
        error_log("cURL Error ({$curl_error_num}) fetching {$url}: {$curl_error_msg}");
        return ['error' => "GitHub API bağlantı hatası.", 'http_code' => $http_code];
    }
    $decoded_response = json_decode($response_body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error fetching {$url}. HTTP Code: {$http_code}. Response: " . substr($response_body, 0, 500));
        return ['error' => "GitHub API yanıtı çözümlenemedi.", 'http_code' => $http_code];
    }
    if ($http_code >= 400) {
        error_log("HTTP Error {$http_code} fetching {$url}. Response: " . json_encode($decoded_response));
        $api_message = $decoded_response['message'] ?? 'Bilinmeyen API hatası';
        return ['error' => "GitHub API hatası ({$http_code}): " . $api_message, 'http_code' => $http_code];
    }
    return $decoded_response;
}

function getGithubRepositories($username) {
    $url = "https://api.github.com/users/{$username}/repos";
    return fetch_with_curl($url, ['user_agent' => 'PHP-GitHub-Portfolio/1.0']);
}

function getGithubUserInfo($username) {
    $url = "https://api.github.com/users/{$username}";
    return fetch_with_curl($url, ['user_agent' => 'PHP-GitHub-Portfolio/1.0']);
}

function formatDate($dateString) {
    if (empty($dateString)) return '';
    try {
        $date = new DateTimeImmutable($dateString);
        return $date->format('d.m.Y');
    } catch (Exception $e) {
        error_log("Error formatting date '{$dateString}': " . $e->getMessage());
        return 'Geçersiz Tarih'; 
    }
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


$github_username = 'KynuxDev'; 

$allowed_sort_options = ['updated', 'stars', 'created'];
$sort_by = 'updated';
// Kullanımdan kaldırılan FILTER_SANITIZE_STRING yerine FILTER_DEFAULT kullan.
// Gelen değer zaten in_array ile beyaz listeye göre kontrol ediliyor.
$sort_input = filter_input(INPUT_GET, 'sort', FILTER_DEFAULT);
if ($sort_input && in_array($sort_input, $allowed_sort_options)) {
    $sort_by = $sort_input;
}

$repositories_data = getGithubRepositories($github_username);
$userInfo_data = getGithubUserInfo($github_username);

$error = null;
$repositories = [];
$userInfo = [];

if (isset($repositories_data['error'])) {
    $error = $repositories_data['error'];
} elseif (isset($userInfo_data['error'])) {
    $error = $userInfo_data['error'];
} else {
    $repositories = sortRepositories($repositories_data, $sort_by);
    $userInfo = $userInfo_data;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Portföyü - <?= htmlspecialchars($github_username); ?></title>
    <link rel="stylesheet" href="modern-portfolio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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

    <div class="container">
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="controls">
                <div class="sort-options">
                     <a href="?sort=updated" class="<?= $sort_by === 'updated' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Son Güncellenen
                    </a>
                    <a href="?sort=stars" class="<?= $sort_by === 'stars' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> Yıldızlar
                    </a>
                    <a href="?sort=created" class="<?= $sort_by === 'created' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-plus"></i> Yeni Eklenen
                    </a>
                </div>
                <div class="search-box">
                    <input type="text" id="repoSearch" placeholder="Repo ara...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <header>
                <div class="profile">
                    <?php if (!isset($userInfo['error'])): ?>
                        <h1 class="profile-name">GitHub Portföyüm</h1>
                        
                        <div class="profile-bio">
                            <img
                                src="<?= htmlspecialchars($userInfo['avatar_url'] ?? 'https://avatars.githubusercontent.com/u/default?v=4'); ?>"
                                alt="<?= htmlspecialchars($github_username); ?> profil resmi"
                                class="profile-avatar"
                            >
                            <h2><?= htmlspecialchars($userInfo['name'] ?? 'Berk'); ?></h2>
                            <p class="username">@<?= htmlspecialchars($github_username); ?></p>
                            <p class="bio"><?= htmlspecialchars($userInfo['bio'] ?? 'Kendi çapında yazılımcı'); ?></p>
                        </div>

                        <div class="profile-stats">
                            <div class="stat">
                                <i class="fas fa-users"></i>
                                <span><?= htmlspecialchars($userInfo['followers'] ?? 0); ?></span>
                                <span>Takipçi</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-user-plus"></i>
                                <span><?= htmlspecialchars($userInfo['following'] ?? 0); ?></span>
                                <span>Takip Edilen</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-code-branch"></i>
                                <span><?= count($repositories); ?></span>
                                <span>Repo</span>
                            </div>
                        </div>
                        
                        <div class="profile-links">
                            <?php if (!empty($userInfo['html_url'])): ?>
                                <a href="<?= htmlspecialchars($userInfo['html_url']); ?>" target="_blank" class="profile-link">
                                    <i class="fab fa-github"></i> GitHub Profili
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($userInfo['blog'])): ?>
                                <a href="<?= htmlspecialchars($userInfo['blog']); ?>" target="_blank" class="profile-link">
                                    <i class="fas fa-globe"></i> Web Sitesi
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($userInfo['location'])): ?>
                                <div class="profile-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($userInfo['location']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <h1 class="profile-name"><?= htmlspecialchars($github_username); ?></h1>
                    <?php endif; ?>
                </div>
            </header>
            
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
                        <a href="<?= htmlspecialchars($repo['html_url'] ?? '#'); ?>" target="_blank">
                            <?= htmlspecialchars($repo['name'] ?? 'N/A'); ?>
                        </a>
                    </div>
                    <div class="repo-description">
                        <?= !empty($repo['description']) ? htmlspecialchars($repo['description']) : 'Bu proje için henüz açıklama eklenmemiş.'; ?>
                    </div>
                    <div class="repo-stats">
                        <span>
                            <i class="fas fa-star"></i>
                            <?= htmlspecialchars($repo['stargazers_count'] ?? 0); ?>
                        </span>
                        <span>
                            <i class="fas fa-code-branch"></i>
                            <?= htmlspecialchars($repo['forks_count'] ?? 0); ?>
                        </span>
                        <span>
                            <i class="fas fa-eye"></i>
                            <?= htmlspecialchars($repo['watchers_count'] ?? 0); ?>
                        </span>
                    </div>
                    <?php
                    $language = $repo['language'] ?? null;
                    if ($language):
                        $colors = [
                            'JavaScript' => '#f1e05a', 'TypeScript' => '#2b7489', 'HTML' => '#e34c26',
                            'CSS' => '#563d7c', 'PHP' => '#4F5D95', 'Python' => '#3572A5',
                            'Java' => '#b07219', 'C#' => '#178600', 'C++' => '#f34b7d',
                            'Ruby' => '#701516', 'Go' => '#00ADD8', 'Swift' => '#ffac45',
                            'Kotlin' => '#F18E33', 'Rust' => '#dea584'
                        ];
                        $languageColor = $colors[$language] ?? '#999';
                    ?>
                    <div class="repo-language">
                        <span class="language-color" style="background-color: <?= htmlspecialchars($languageColor); ?>"></span>
                        <?= htmlspecialchars($language); ?>
                    </div>
                    <?php endif; ?>
                    <div class="repo-dates">
                        <span>
                            <i class="fas fa-calendar-plus"></i>
                            <?= htmlspecialchars(formatDate($repo['created_at'] ?? '')); ?>
                        </span>
                        <span>
                            <i class="fas fa-sync-alt"></i>
                            <?= htmlspecialchars(formatDate($repo['updated_at'] ?? '')); ?>
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
                        <a href="<?= htmlspecialchars($repo['html_url'] ?? '#'); ?>" target="_blank">
                            <?= htmlspecialchars($repo['name'] ?? 'N/A'); ?>
                        </a>
                    </div>
                    <div class="repo-description">
                        <?= !empty($repo['description']) ? htmlspecialchars($repo['description']) : 'Bu proje için henüz açıklama eklenmemiş.'; ?>
                    </div>
                    <div class="repo-stats">
                        <span>
                            <i class="fas fa-star"></i>
                            <?= htmlspecialchars($repo['stargazers_count'] ?? 0); ?>
                        </span>
                        <span>
                            <i class="fas fa-code-branch"></i>
                            <?= htmlspecialchars($repo['forks_count'] ?? 0); ?>
                        </span>
                        <span>
                            <i class="fas fa-eye"></i>
                            <?= htmlspecialchars($repo['watchers_count'] ?? 0); ?>
                        </span>
                    </div>
                     <?php
                    $language = $repo['language'] ?? null;
                    if ($language):
                        $colors = [ 
                             'JavaScript' => '#f1e05a', 'TypeScript' => '#2b7489', 'HTML' => '#e34c26',
                             'CSS' => '#563d7c', 'PHP' => '#4F5D95', 'Python' => '#3572A5',
                             'Java' => '#b07219', 'C#' => '#178600', 'C++' => '#f34b7d',
                             'Ruby' => '#701516', 'Go' => '#00ADD8', 'Swift' => '#ffac45',
                             'Kotlin' => '#F18E33', 'Rust' => '#dea584'
                        ];
                        $languageColor = $colors[$language] ?? '#999';
                    ?>
                    <div class="repo-language">
                        <span class="language-color" style="background-color: <?= htmlspecialchars($languageColor); ?>"></span>
                        <?= htmlspecialchars($language); ?>
                    </div>
                    <?php endif; ?>
                    <div class="repo-dates">
                        <span>
                            <i class="fas fa-calendar-plus"></i>
                            <?= htmlspecialchars(formatDate($repo['created_at'] ?? '')); ?>
                        </span>
                        <span>
                            <i class="fas fa-sync-alt"></i>
                            <?= htmlspecialchars(formatDate($repo['updated_at'] ?? '')); ?>
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
                        <a href="<?= htmlspecialchars($repo['html_url'] ?? '#'); ?>" target="_blank">
                            <?= htmlspecialchars($repo['name'] ?? 'N/A'); ?>
                        </a>
                    </div>
                    <div class="repo-description">
                        <?= !empty($repo['description']) ? htmlspecialchars($repo['description']) : 'Bu proje için henüz açıklama eklenmemiş.'; ?>
                    </div>
                    <div class="repo-stats">
                        <span>
                            <i class="fas fa-star"></i>
                            <?= htmlspecialchars($repo['stargazers_count'] ?? 0); ?>
                        </span>
                        <span>
                            <i class="fas fa-code-branch"></i>
                            <?= htmlspecialchars($repo['forks_count'] ?? 0); ?>
                        </span>
                        <span>
                            <i class="fas fa-eye"></i>
                            <?= htmlspecialchars($repo['watchers_count'] ?? 0); ?>
                        </span>
                    </div>
                    <?php
                    $language = $repo['language'] ?? null;
                    if ($language):
                         $colors = [ 
                             'JavaScript' => '#f1e05a', 'TypeScript' => '#2b7489', 'HTML' => '#e34c26',
                             'CSS' => '#563d7c', 'PHP' => '#4F5D95', 'Python' => '#3572A5',
                             'Java' => '#b07219', 'C#' => '#178600', 'C++' => '#f34b7d',
                             'Ruby' => '#701516', 'Go' => '#00ADD8', 'Swift' => '#ffac45',
                             'Kotlin' => '#F18E33', 'Rust' => '#dea584'
                         ];
                        $languageColor = $colors[$language] ?? '#999';
                    ?>
                    <div class="repo-language">
                        <span class="language-color" style="background-color: <?= htmlspecialchars($languageColor); ?>"></span>
                        <?= htmlspecialchars($language); ?>
                    </div>
                    <?php endif; ?>
                    <div class="repo-dates">
                        <span>
                            <i class="fas fa-calendar-plus"></i>
                            <?= htmlspecialchars(formatDate($repo['created_at'] ?? '')); ?>
                        </span>
                        <span>
                            <i class="fas fa-sync-alt"></i>
                            <?= htmlspecialchars(formatDate($repo['updated_at'] ?? '')); ?>
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
    
    <footer class="site-footer">
        <div class="copyright">
            © 2025 KynuxDev. Tüm hakları saklıdır.
        </div>
        <div class="footer-tagline">
            Tasarım ile <i class="fas fa-heart"></i> ve <i class="fas fa-code"></i> arasında...
        </div>
    </footer>
    
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
    </a>
    
    <script>
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
