document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: false,
        mirror: true,
        anchorPlacement: 'top-bottom',
        offset: 50
    });
    
    const navbar = document.querySelector(".navbar");
    const backToTop = document.querySelector(".back-to-top");
    const sections = document.querySelectorAll("section");
    const navLinks = document.querySelectorAll(".nav-links a");
    const platformCards = document.querySelectorAll('.platform-card');
    const skillCards = document.querySelectorAll('.skills-card');
    const projectCards = document.querySelectorAll('.project-card');
    
    window.addEventListener("scroll", function() {
        const scrollY = window.scrollY;
        
        if (scrollY > 100) {
            navbar.classList.add("scrolled");
            if (backToTop) backToTop.classList.add("visible");
        } else {
            navbar.classList.remove("scrolled");
            if (backToTop) backToTop.classList.remove("visible");
        }
        
        const parallaxElements = document.querySelectorAll('.hero-content, .section-header');
        parallaxElements.forEach(el => {
            const speed = el.dataset.speed || 0.15;
            el.style.transform = `translateY(${scrollY * speed}px)`;
        });
        
        let current = "";
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (scrollY >= sectionTop - 300) {
                current = section.getAttribute("id");
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove("active");
            if (link.getAttribute("href").substring(1) === current) {
                link.classList.add("active");
            }
        });
    });
    
    if (backToTop) {
        backToTop.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    }
    
    function animateElements() {
        animateSkills();
        init3DEffect();
        initFloatingElements();
        initTypeWriter();
        enhanceFooter();
        
        document.querySelectorAll('.section-header h2').forEach(header => {
            header.style.zIndex = '200';
        });
        
        document.querySelectorAll('.skills-content h3').forEach(header => {
            header.style.zIndex = '100';
        });
    }
    
    function animateSkills() {
        const skillItems = document.querySelectorAll('.skill-item');
        skillItems.forEach(skill => {
            const percentage = skill.getAttribute('data-percentage');
            const progressBar = skill.querySelector('.skill-progress');
            
            if (progressBar) {
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = percentage + '%';
                }, 300);
            }
        });
    }
    
    function init3DEffect() {
        const cards = document.querySelectorAll('.skills-card, .project-card, .platform-card');
        
        cards.forEach(card => {
            card.addEventListener('mousemove', handleCardMove);
            card.addEventListener('mouseleave', handleCardLeave);
        });
    }
    
    function handleCardMove(e) {
        const card = this;
        const cardRect = card.getBoundingClientRect();
        const cardCenterX = cardRect.left + cardRect.width / 2;
        const cardCenterY = cardRect.top + cardRect.height / 2;
        
        const mouseX = e.clientX - cardCenterX;
        const mouseY = e.clientY - cardCenterY;
        
        const rotateY = (mouseX / (cardRect.width / 2)) * 5;
        const rotateX = -((mouseY / (cardRect.height / 2)) * 5);
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
    }
    
    function handleCardLeave() {
        this.style.transform = '';
        this.style.boxShadow = '';
    }
    
    
    function initFloatingElements() {
        const heroTitle = document.querySelector('.hero .title');
        if (heroTitle) {
            createFloatingEffect(heroTitle, {
                count: 5,
                colors: ['#3b82f6', '#2563eb', '#60a5fa'],
                size: { min: 20, max: 60 }
            });
        }
        
        const skillsSections = document.querySelectorAll('.section-header');
        skillsSections.forEach(section => {
            createFloatingEffect(section, {
                count: 4,
                colors: ['#3b82f6', '#10b981', '#2563eb'],
                size: { min: 15, max: 40 }
            });
        });
        
        platformCards.forEach(card => {
            createFloatingEffect(card, {
                count: 3,
                colors: card.classList.contains('discord-card') 
                    ? ['#5865F2', '#738adb', '#9eafef'] 
                    : ['#1DB954', '#1ed760', '#1ed76f'],
                size: { min: 5, max: 20 }
            });
        });
    }
    
    function createFloatingEffect(element, options) {
        const { count, colors, size } = options;
        
        for (let i = 0; i < count; i++) {
            const floatingElement = document.createElement('div');
            floatingElement.className = 'floating-element';
            
            const randomSize = Math.floor(Math.random() * (size.max - size.min) + size.min);
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            const randomX = Math.random() * 100;
            const randomY = Math.random() * 100;
            const randomDuration = Math.random() * 20 + 10;
            const randomDelay = Math.random() * 5;
            
            floatingElement.style.cssText = `
                position: absolute;
                width: ${randomSize}px;
                height: ${randomSize}px;
                background-color: ${randomColor};
                border-radius: 50%;
                top: ${randomY}%;
                left: ${randomX}%;
                opacity: 0.08;
                filter: blur(${randomSize / 4}px);
                pointer-events: none;
                z-index: -1;
                animation: float ${randomDuration}s ${randomDelay}s infinite ease-in-out alternate;
            `;
            
            element.style.position = 'relative';
            element.style.overflow = 'hidden';
            element.appendChild(floatingElement);
        }
    }
    
    function initTypeWriter() {
        const sectionHeaders = document.querySelectorAll('.section-header h2, .skills-content h3');
        
        sectionHeaders.forEach(header => {
            const spans = header.querySelectorAll('span');
            if (spans.length > 0) {
                spans.forEach(span => {
                    if (span.classList.contains('highlight')) {
                        span.classList.add('typewriter-highlight');
                    }
                });
            }
        });
        
        const heroTitle = document.querySelector('.hero .title');
        if (heroTitle) {
            const highlight = heroTitle.querySelector('.highlight');
            if (highlight) {
                highlight.classList.add('typewriter-highlight');
            }
        }
    }
    
    const REFRESH_INTERVAL = 10000;
    let lastUpdateTime = 0;
    let updateTimer = null;
    let discordStatus = null;
    let spotifyStatus = null;
    
    function initPlatformCards() {
        platformCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px) scale(1.02) perspective(1000px) rotateX(2deg)';
                card.style.boxShadow = '0 20px 40px -10px rgba(0, 0, 0, 0.6), 0 1px 3px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.1)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });
            
            createParticleEffect(card);
        });
        
        // GitHub kartları için özel efektler
        document.querySelectorAll('.repo-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px) perspective(1000px) rotateX(2deg) rotateY(2deg)';
                card.style.boxShadow = '0 20px 40px -10px rgba(0, 0, 0, 0.5), 0 1px 3px rgba(0, 0, 0, 0.3), inset 0 0 0 1px rgba(255, 255, 255, 0.1)';
                const colorBar = card.querySelector('.repo-card::before');
                if (colorBar) colorBar.style.transform = 'scaleX(1)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.boxShadow = '';
                const colorBar = card.querySelector('.repo-card::before');
                if (colorBar) colorBar.style.transform = 'scaleX(0)';
            });
        });
        
        animateMusicBars();
        
        startRealtimeUpdates();
        
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopRealtimeUpdates();
            } else {
                startRealtimeUpdates();
            }
        });
    }
    
    function animateMusicBars() {
        const musicBars = document.querySelectorAll('.playing-animation span');
        if (musicBars.length > 0) {
            const bassBeat = [18, 8, 15, 10, 12, 5, 18, 8];
            let beatIndex = 0;
            
            const musicBarInterval = setInterval(() => {
                musicBars.forEach((bar, index) => {
                    const baseHeight = bassBeat[(beatIndex + index) % bassBeat.length];
                    const randomVariation = Math.floor(Math.random() * 4) - 2;
                    const randomHeight = baseHeight + randomVariation;
                    
                    bar.style.height = randomHeight + 'px';
                });
                beatIndex = (beatIndex + 1) % bassBeat.length;
            }, 280);
        }
    }
    
    function createParticleEffect(element) {
        const particleCount = 5;
        const cardClass = element.classList.contains('discord-card') ? 'discord' : 'spotify';
        const colors = cardClass === 'discord' 
            ? ['#5865F2', '#738adb', '#9eafef'] 
            : ['#1DB954', '#1ed760', '#1ed76f'];
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('span');
            particle.classList.add('floating-particle');
            
            const size = Math.random() * 4 + 2;
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            
            particle.style.width = `${size}px`;
            particle.style.height = particle.style.width;
            particle.style.background = randomColor;
            particle.style.borderRadius = '50%';
            particle.style.position = 'absolute';
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.opacity = `${Math.random() * 0.3 + 0.1}`;
            particle.style.filter = 'blur(1px)';
            particle.style.zIndex = '1';
            particle.style.pointerEvents = 'none';
            
            const animationDuration = Math.random() * 10 + 10;
            const moveX = Math.random() * 40 - 20;
            const moveY = Math.random() * 40 - 20;
            const rotate = Math.random() * 360;
            
            particle.style.animation = `particle${i} ${animationDuration}s ease-in-out infinite alternate`;
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes particle${i} {
                    0% { transform: translate(0, 0) rotate(0deg); }
                    50% { transform: translate(${moveX}px, ${moveY}px) rotate(${rotate / 2}deg); }
                    100% { transform: translate(${moveY}px, ${moveX}px) rotate(${rotate}deg); }
                }
            `;
            document.head.appendChild(style);
            
            element.appendChild(particle);
        }
    }
    
    function startRealtimeUpdates() {
        fetchStatusUpdates();
        
        updateTimer = setInterval(fetchStatusUpdates, REFRESH_INTERVAL);
        
        startProgressAnimation();
    }
    
    function stopRealtimeUpdates() {
        if (updateTimer) {
            clearInterval(updateTimer);
            updateTimer = null;
        }
    }
    
    function fetchStatusUpdates() {
        fetch('get-status.php?' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                lastUpdateTime = new Date().getTime();
                
                // Discord bilgilerini doğrudan al
                if (data.discord) {
                    updatePlatformCards(data);
                } else if (data.error) {
                    // Hata durumunda varsayılan değerleri kullan
                    const defaultStatus = {
                        discord: {status: 'dnd', username: 'kynux.dev', last_updated: new Date().toTimeString().split(' ')[0]},
                        spotify: {is_playing: false},
                        server_time: new Date().toTimeString().split(' ')[0]
                    };
                    updatePlatformCards(defaultStatus);
                }
            })
            .catch(error => {
                const defaultStatus = {
                    discord: {status: 'dnd', username: 'kynux.dev', last_updated: new Date().toTimeString().split(' ')[0]},
                    spotify: {is_playing: false},
                    server_time: new Date().toTimeString().split(' ')[0]
                };
                updatePlatformCards(defaultStatus);
            });
    }
    
    function updatePlatformCards(data) {
        discordStatus = data.discord;
        spotifyStatus = data.spotify;
        
        updateDiscordCard(discordStatus);
        
        updateSpotifyCard(spotifyStatus);
        
        document.querySelectorAll('.platform-card').forEach(card => {
            const lastUpdateTimeElement = card.querySelector('.last-update-time');
            if (lastUpdateTimeElement) {
                const timeStr = data.server_time || new Date().toTimeString().split(' ')[0];
                lastUpdateTimeElement.textContent = `Son güncelleme: ${timeStr}`;
            }
        });
    }
    
    function updateDiscordCard(status) {
        const discordCard = document.querySelector('.discord-card');
        if (!discordCard) return;
        
        const statusIndicator = discordCard.querySelector('.status-indicator');
        if (statusIndicator) {
            statusIndicator.classList.remove('online', 'idle', 'dnd', 'offline');
            statusIndicator.classList.add(status.status);
            
            const statusText = statusIndicator.querySelector('.status-text');
            if (statusText) {
                let statusName = 'Çevrimdışı';
                switch(status.status) {
                    case 'online': statusName = 'Çevrimiçi'; break;
                    case 'idle': statusName = 'Boşta'; break;
                    case 'dnd': statusName = 'Rahatsız Etmeyin'; break;
                }
                statusText.textContent = statusName;
            }
        }
        
        const activityContainer = discordCard.querySelector('.activity');
        const noActivityContainer = discordCard.querySelector('.no-activity');
        
        discordCard.querySelectorAll('.activity, .no-activity').forEach(el => el.remove());
        
        const newContainer = document.createElement('div');
        const p = document.createElement('p');
        const i = document.createElement('i');
        i.className = 'fas fa-gamepad';
        p.appendChild(i);

        if (status.game && status.has_game) {
            newContainer.className = 'activity';
            const textNode = document.createTextNode(' Oynanıyor: ');
            const span = document.createElement('span');
            span.textContent = status.game; // Use textContent for safety
            p.appendChild(textNode);
            p.appendChild(span);
        } else {
            newContainer.className = 'no-activity';
            const textNode = document.createTextNode(' Oyun oynamıyor');
            p.appendChild(textNode);
        }
        
        discordCard.querySelector('.platform-info').appendChild(newContainer);
    }
    
    function updateSpotifyCard(status) {
        const spotifyCard = document.querySelector('.spotify-card');
        if (!spotifyCard) return;
        
        const spotifyInfo = spotifyCard.querySelector('.platform-info');
        const nowPlaying = spotifyCard.querySelector('.now-playing');
        const notPlaying = spotifyCard.querySelector('.not-playing');
        
        // Log Spotify status for debugging
        console.log("Spotify Status:", status);
        
        if (status.is_playing === true) {
            if (notPlaying) {
                notPlaying.remove();
            }
            
            if (!nowPlaying) {
                const newNowPlaying = document.createElement('div');
                newNowPlaying.className = 'now-playing';

                // Create elements safely
                const albumArtDiv = document.createElement('div');
                albumArtDiv.className = 'album-art';

                const img = document.createElement('img');
                img.src = status.album_art || ''; // Set src directly
                img.alt = 'Album Art';

                const playingAnimationDiv = document.createElement('div');
                playingAnimationDiv.className = 'playing-animation';
                for (let i = 0; i < 3; i++) {
                    playingAnimationDiv.appendChild(document.createElement('span'));
                }

                albumArtDiv.appendChild(img);
                albumArtDiv.appendChild(playingAnimationDiv);

                const songInfoDiv = document.createElement('div');
                songInfoDiv.className = 'song-info';

                const songTitleP = document.createElement('p');
                songTitleP.className = 'song-title';
                songTitleP.textContent = status.song || ''; // Use textContent

                const artistP = document.createElement('p');
                artistP.className = 'artist';
                artistP.textContent = status.artist || ''; // Use textContent

                const progressBarDiv = document.createElement('div');
                progressBarDiv.className = 'progress-bar';
                const progressDiv = document.createElement('div');
                progressDiv.className = 'progress';
                progressDiv.style.width = `${status.progress_percent || 0}%`;
                progressBarDiv.appendChild(progressDiv);

                songInfoDiv.appendChild(songTitleP);
                songInfoDiv.appendChild(artistP);
                songInfoDiv.appendChild(progressBarDiv);

                newNowPlaying.appendChild(albumArtDiv);
                newNowPlaying.appendChild(songInfoDiv);
                spotifyInfo.appendChild(newNowPlaying);
                
                animateMusicBars();
            } else {
                const albumArt = nowPlaying.querySelector('.album-art img');
                const songTitle = nowPlaying.querySelector('.song-title');
                const artist = nowPlaying.querySelector('.artist');
                const progress = nowPlaying.querySelector('.progress');
                
                if (albumArt) albumArt.src = status.album_art;
                if (songTitle) songTitle.textContent = status.song;
                if (artist) artist.textContent = status.artist;
                if (progress) progress.style.width = `${status.progress_percent || 0}%`;
            }
        } else {
            if (nowPlaying) {
                nowPlaying.remove();
            }
            
            if (!notPlaying) {
                const newNotPlaying = document.createElement('div');
                newNotPlaying.className = 'not-playing';
                const p = document.createElement('p');
                p.textContent = 'Şu anda müzik dinlenmiyor'; // Use textContent
                newNotPlaying.appendChild(p);
                spotifyInfo.appendChild(newNotPlaying);
            }
        }
    }
    
    function startProgressAnimation() {
        const progressInterval = setInterval(() => {
            if (!spotifyStatus || !spotifyStatus.is_playing) return;
            
            const progressBar = document.querySelector('.spotify-card .progress');
            if (!progressBar) return;
            
            if (spotifyStatus.duration_ms > 0 && spotifyStatus.progress_ms) {
                let progress = spotifyStatus.progress_ms + 100;
                
                if (progress >= spotifyStatus.duration_ms) {
                    progress = 0;
                }
                
                spotifyStatus.progress_ms = progress;
                spotifyStatus.progress_percent = (progress / spotifyStatus.duration_ms) * 100;
                
                progressBar.style.width = `${spotifyStatus.progress_percent}%`;
            }
        }, 100);
    }
    
    const skillsSection = document.querySelector('.skills-section');
    if (skillsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateSkills();
                }
            });
        }, { threshold: 0.3 });
        
        observer.observe(skillsSection);
    }
    
    const techTags = document.querySelectorAll('.tech-tag');
    const colors = [
        'rgba(59, 130, 246, 0.3)',
        'rgba(16, 185, 129, 0.3)',
        'rgba(245, 158, 11, 0.3)',
        'rgba(239, 68, 68, 0.3)',
        'rgba(139, 92, 246, 0.3)',
        'rgba(236, 72, 153, 0.3)'
    ];
    
    techTags.forEach((tag, index) => {
        tag.style.backgroundColor = colors[index % colors.length];
    });
    
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.textContent = "Gönderildi!";
            submitButton.style.backgroundColor = "var(--success-color)";
            
            const inputs = contactForm.querySelectorAll('input, textarea');
            inputs.forEach(input => input.value = '');
            
            setTimeout(() => {
                submitButton.textContent = originalText;
                submitButton.style.backgroundColor = "";
            }, 3000);
        });
    }
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === "#") return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    function enhanceFooter() {
        const footer = document.querySelector('.footer');
        if (!footer) return;
        
        const footerGradient = document.createElement('div');
        footerGradient.className = 'footer-gradient';
        footerGradient.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(5, 10, 24, 0.05), rgba(96, 165, 250, 0.08));
            z-index: -1;
            animation: footerGradient 15s ease-in-out infinite alternate;
        `;
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes footerGradient {
                0% { background-position: 0% 0%; }
                100% { background-position: 100% 100%; }
            }
            
            .footer {
                position: relative;
                overflow: hidden;
                padding-top: 4rem !important;
                padding-bottom: 2rem !important;
            }
            
            .footer-gradient::after {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 1px;
                background: linear-gradient(to right, transparent, var(--primary-color), transparent);
            }
            
            .footer-logo {
                position: relative;
                margin-bottom: 1.5rem;
            }
            
            .footer-logo::after {
                content: "";
                position: absolute;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 80px;
                height: 3px;
                background: var(--gradient-blue);
                border-radius: 3px;
            }
            
            .footer-nav {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 1.5rem;
                margin: 2rem 0;
            }
            
            .footer-nav a {
                color: var(--text-secondary);
                transition: all 0.3s ease;
                position: relative;
            }
            
            .footer-nav a:hover {
                color: var(--primary-color);
                transform: translateY(-3px);
            }
            
            .footer-nav a::after {
                content: "";
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 0;
                height: 2px;
                background-color: var(--primary-color);
                transition: all 0.3s ease;
            }
            
            .footer-nav a:hover::after {
                width: 100%;
            }
            
            .footer-social a {
                position: relative;
                overflow: hidden;
            }
            
            .footer-social a::before {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background-color: var(--primary-color);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                opacity: 0;
                transition: all 0.4s ease;
                z-index: -1;
            }
            
            .footer-social a:hover::before {
                width: 100%;
                height: 100%;
                opacity: 0.2;
            }
            
            .footer-bottom {
                position: relative;
                padding-top: 2rem;
            }
            
            .footer-bottom::before {
                content: "";
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 70%;
                height: 1px;
                background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1), transparent);
            }
        `;
        document.head.appendChild(style);
        
        footer.insertBefore(footerGradient, footer.firstChild);
        
        const footerContent = footer.querySelector('.footer-content');
        if (footerContent) {
            const footerNav = document.createElement('div');
            footerNav.className = 'footer-nav';
            footerNav.innerHTML = `
                <a href="#platforms">Platformlar</a>
                <a href="#skills">Yetenekler</a>
                <a href="#projects">Projeler</a>
                <a href="#contact">İletişim</a>
            `;
            
            const footerSocial = footer.querySelector('.footer-social');
            if (footerSocial && footerSocial.parentNode === footerContent) {
                footerContent.insertBefore(footerNav, footerSocial);
            } else {
                footerContent.appendChild(footerNav);
            }
        }
        
        const footerBottom = footer.querySelector('.footer-bottom');
        if (footerBottom) {
            footerBottom.innerHTML = `
                <p>&copy; 2025 kynux.dev. Tüm hakları saklıdır.</p>
                <p class="credit">Tasarım ile <i class="fas fa-heart" style="color: #ef4444; font-size: 0.8rem;"></i> ve <i class="fas fa-code" style="color: var(--primary-color); font-size: 0.8rem;"></i> arasında...</p>
            `;
        }
    }
    
    function fixLayerOverlaps() {
        document.querySelectorAll('.section-header').forEach(header => {
            header.style.zIndex = '200';
            
            const h2 = header.querySelector('h2');
            if (h2) h2.style.zIndex = '200';
        });
        
        document.querySelectorAll('.skills-card h3, .platform-card h3, .project-card h3').forEach(header => {
            header.style.zIndex = '100';
        });
        
        // Sticky başlıkları aktif et
        document.querySelectorAll('#beceriSetimBaslik, #githubPortfoyumBaslik, #platformDurumumBaslik, #iletisimeGecBaslik').forEach(header => {
            header.style.position = 'sticky';
            header.style.top = '80px';
            header.style.zIndex = '1000';
        });
    }
    
    const addPageTransitionEffect = () => {
        const overlay = document.createElement('div');
        overlay.className = 'page-transition-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--background-darker);
            z-index: 9999;
            pointer-events: none;
            opacity: 1;
            transition: opacity 0.8s ease-out;
        `;
        document.body.appendChild(overlay);
        
        setTimeout(() => {
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.remove();
            }, 800);
        }, 200);
    };
    
    window.addEventListener('load', () => {
        document.body.classList.add('loaded');
        addPageTransitionEffect();
        animateElements();
        initPlatformCards();
        fixLayerOverlaps();
        
        const observer = new MutationObserver(fixLayerOverlaps);
        observer.observe(document.body, { 
            childList: true, 
            subtree: true 
        });
    });
});
