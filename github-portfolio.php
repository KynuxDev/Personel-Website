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
</head>
<body>
    <div class="container">
        <header>
            <div class="profile">
                <?php if (!isset($userInfo['error'])): ?>
                    <img class="profile-image" src="<?php echo htmlspecialchars($userInfo['avatar_url']); ?>" alt="<?php echo htmlspecialchars($github_username); ?> profil resmi">
                    <h1 class="profile-name"><?php echo htmlspecialchars($userInfo['name'] ?? $github_username); ?></h1>
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
        
        <?php if ($error): ?>
            <div class="error-message">
                <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
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
            
            <?php if (empty($repositories)): ?>
                <p>Hiç repository bulunamadı.</p>
            <?php else: ?>
                <div class="repo-grid" id="repoGrid">
                    <?php foreach ($repositories as $repo): ?>
                        <?php
                        // Fork olan repoları atla
                        if ($repo['fork']) continue;
                        
                        // Dil rengini belirle
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
                        <div class="repo-card">
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
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
    </a>
    
    <script>
        // Repo arama işlevi
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('repoSearch');
            const repoGrid = document.getElementById('repoGrid');
            const repoCards = repoGrid ? repoGrid.querySelectorAll('.repo-card') : [];
            
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
        });
    </script>
</body>
</html>
