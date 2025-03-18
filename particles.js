/**
 * Modern Portfolio için Hareketli Parçacıklar
 * 
 * Parlayan hareketli noktalar oluşturan ve konuma göre mouse ile etkileşime giren
 * özel tasarlanmış hafif bir parçacık animasyon sistemi.
 */

class ParticleSystem {
  constructor(options = {}) {
    this.options = {
      selector: '.bg-particles',
      particleCount: 80,
      connectParticles: true,
      minDistance: 120,
      speed: 0.5,
      color: '#3b82f6',
      size: { min: 1, max: 3 },
      responsive: [
        {
          breakpoint: 768,
          options: {
            particleCount: 40,
            connectParticles: false
          }
        },
        {
          breakpoint: 425,
          options: {
            particleCount: 25
          }
        }
      ],
      ...options
    };

    this.particles = [];
    this.isRunning = false;
    this.mouse = { x: 0, y: 0, radius: 150 };
    this.init();
  }

  init() {
    // Konteyner elementi seç
    this.container = document.querySelector(this.options.selector);
    if (!this.container) return;

    // Canvas oluştur
    this.canvas = document.createElement('canvas');
    this.ctx = this.canvas.getContext('2d');
    this.container.appendChild(this.canvas);

    // Tam ekran kaplamak için canvas boyutlarını ayarla
    this.resizeCanvas();
    window.addEventListener('resize', () => this.resizeCanvas());

    // Responsif ayarlamalar
    this.setResponsiveOptions();
    window.addEventListener('resize', () => this.setResponsiveOptions());

    // Mouse takibi
    window.addEventListener('mousemove', (e) => {
      this.mouse.x = e.clientX;
      this.mouse.y = e.clientY;
    });

    // Dokunmatik ekranda takip
    window.addEventListener('touchmove', (e) => {
      if (e.touches[0]) {
        this.mouse.x = e.touches[0].clientX;
        this.mouse.y = e.touches[0].clientY;
      }
    });

    // Mouse alanından çıkınca pozisyonu sıfırla
    window.addEventListener('mouseout', () => {
      this.mouse.x = undefined;
      this.mouse.y = undefined;
    });

    // Parçacıkları oluştur
    this.createParticles();

    // Animasyonu başlat
    this.isRunning = true;
    this.animate();
  }

  setResponsiveOptions() {
    // Varsayılan options ile başla
    const defaultOptions = { ...this.options };

    // Ekran genişliğini al
    const windowWidth = window.innerWidth;

    // Eğer responsive array varsa ve içinde eleman varsa
    if (this.options.responsive && this.options.responsive.length) {
      // Her bir breakpoint'i kontrol et, küçükten büyüğe sırala
      const sortedBreakpoints = [...this.options.responsive].sort((a, b) => a.breakpoint - b.breakpoint);

      // Mevcut ekran genişliğine göre uygun breakpoint'i bul
      for (const item of sortedBreakpoints) {
        if (windowWidth <= item.breakpoint) {
          // Bulunan breakpoint'in ayarlarını uygula
          this.activeOptions = { ...defaultOptions, ...item.options };
          
          // Parçacıkları yeniden oluştur
          if (this.particles.length !== this.activeOptions.particleCount) {
            this.createParticles();
          }
          return;
        }
      }
    }

    // Eğer hiçbir breakpoint uygun değilse, varsayılan options'ı kullan
    this.activeOptions = defaultOptions;
  }

  resizeCanvas() {
    this.canvas.width = window.innerWidth;
    this.canvas.height = window.innerHeight;
    
    // Parçacıkları yeniden konumlandır
    if (this.particles.length) {
      this.particles.forEach(particle => {
        if (particle.x > this.canvas.width) particle.x = Math.random() * this.canvas.width;
        if (particle.y > this.canvas.height) particle.y = Math.random() * this.canvas.height;
      });
    }
  }

  createParticles() {
    // Parçacıkları temizle
    this.particles = [];

    // Yeni parçacıklar oluştur
    for (let i = 0; i < this.activeOptions.particleCount; i++) {
      const size = Math.random() * (this.activeOptions.size.max - this.activeOptions.size.min) + this.activeOptions.size.min;
      
      this.particles.push({
        x: Math.random() * this.canvas.width,
        y: Math.random() * this.canvas.height,
        size: size,
        color: this.activeOptions.color,
        speedX: (Math.random() - 0.5) * this.activeOptions.speed,
        speedY: (Math.random() - 0.5) * this.activeOptions.speed,
        opacity: Math.random() * 0.5 + 0.3 // 0.3 - 0.8 arası opaklık
      });
    }
  }

  animate() {
    if (!this.isRunning) return;

    // Canvas'ı temizle
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

    // Her parçacığı çiz ve hareket ettir
    this.particles.forEach(particle => {
      // Parçacığı çiz
      this.ctx.beginPath();
      this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
      this.ctx.closePath();
      
      // Parlaklık ayarı için gradient kullan
      const gradient = this.ctx.createRadialGradient(
        particle.x, particle.y, 0,
        particle.x, particle.y, particle.size * 2
      );
      gradient.addColorStop(0, `rgba(96, 165, 250, ${particle.opacity})`);
      gradient.addColorStop(1, 'rgba(96, 165, 250, 0)');
      
      this.ctx.fillStyle = gradient;
      this.ctx.fill();

      // Parçacıklar arasında bağlantı çiz
      if (this.activeOptions.connectParticles) {
        this.connectToNearParticles(particle);
      }

      // Mouse ile etkileşim
      if (this.mouse.x && this.mouse.y) {
        const dx = this.mouse.x - particle.x;
        const dy = this.mouse.y - particle.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        
        if (distance < this.mouse.radius) {
          const forceFactor = (this.mouse.radius - distance) / this.mouse.radius;
          particle.x -= dx * 0.02 * forceFactor;
          particle.y -= dy * 0.02 * forceFactor;
        }
      }

      // Hareket
      particle.x += particle.speedX;
      particle.y += particle.speedY;

      // Sınır kontrolleri (ekrandan çıkmasını engelle)
      if (particle.x < 0 || particle.x > this.canvas.width) {
        particle.speedX = -particle.speedX;
      }
      
      if (particle.y < 0 || particle.y > this.canvas.height) {
        particle.speedY = -particle.speedY;
      }
    });

    requestAnimationFrame(() => this.animate());
  }

  connectToNearParticles(particle) {
    this.particles.forEach(otherParticle => {
      if (particle === otherParticle) return;

      const dx = particle.x - otherParticle.x;
      const dy = particle.y - otherParticle.y;
      const distance = Math.sqrt(dx * dx + dy * dy);

      if (distance < this.activeOptions.minDistance) {
        // Uzaklık azaldıkça bağlantı çizgisi kalınlığı da azalsın
        const opacity = 1 - (distance / this.activeOptions.minDistance);
        this.ctx.strokeStyle = `rgba(96, 165, 250, ${opacity * 0.2})`;
        this.ctx.lineWidth = 1;
        
        this.ctx.beginPath();
        this.ctx.moveTo(particle.x, particle.y);
        this.ctx.lineTo(otherParticle.x, otherParticle.y);
        this.ctx.stroke();
      }
    });
  }

  stop() {
    this.isRunning = false;
  }

  start() {
    if (!this.isRunning) {
      this.isRunning = true;
      this.animate();
    }
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // Parçacık sistemini başlat
  const particleSystem = new ParticleSystem({
    selector: '.bg-particles',
    particleCount: 100,
    connectParticles: true,
    speed: 0.3,
    color: '#60a5fa',
    size: { min: 1, max: 3 }
  });

  // Scroll efektleri
  window.addEventListener('scroll', function() {
    const scrollY = window.scrollY;
    
    // Parallax efektleri için elementleri seç
    const bgParticles = document.querySelector('.bg-particles');
    const bgGrid = document.querySelector('.bg-grid');
    
    if (bgParticles) {
      bgParticles.style.transform = `translateY(${scrollY * 0.1}px)`;
    }
    
    if (bgGrid) {
      bgGrid.style.transform = `translateY(${scrollY * 0.05}px)`;
    }
  });
});
