# KynuxDev Kişisel Tanıtım Sitesi

Bu repo, kynux.dev kişisel tanıtım web sitesinin kaynak kodlarını içerir. Modern ve interaktif web teknolojileri kullanılarak geliştirilmiş profesyonel bir kişisel portföy sitesidir.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-Modern-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Semantic-E34F26?style=for-the-badge&logo=html5&logoColor=white)

## 📋 Özellikler

- **Dinamik GitHub Portföyü**: GitHub API entegrasyonu ile güncel repo verilerini gösterir
- **Platform Durum Gösterimleri**: Discord ve Spotify hesap durumlarını gerçek zamanlı gösterir
- **Modern UI/UX**: Animasyonlar, interaktif elementler ve duyarlı tasarım
- **SEO Optimizasyonu**: Arama motorları için optimize edilmiş yapı
- **Çok Dilli Destek**: Türkçe ve İngilizce dil seçenekleri
- **Beceri Gösterimi**: Kategorilere ayrılmış beceri ve yeteneklerin görsel gösterimi
- **İletişim Formu**: Güvenli ve CSRF korumalı iletişim formu

## 🔧 Kurulum

```bash
# Repoyu klonlayın
git clone https://github.com/kynux.dev/kynux-portfolio.git

# Klasöre gidin
cd kynux-portfolio

# Gerekliyse bağımlılıkları yükleyin
npm install
```

## 🚀 Kullanım

1. Dosyaları web sunucu dizininize (ör. htdocs, www) yükleyin
2. `.env` dosyasını ana dizinde oluşturun:

```
# GitHub API
GITHUB_TOKEN=github_token_buraya

# Discord Bot
DISCORD_BOT_TOKEN=discord_token_buraya
DISCORD_USER_ID=discord_user_id_buraya

# Spotify API
SPOTIFY_CLIENT_ID=spotify_client_id_buraya
SPOTIFY_CLIENT_SECRET=spotify_client_secret_buraya
```

3. Web sunucunuzu başlatın ve tarayıcıdan erişin

## 📁 Proje Yapısı

```
kynux-portfolio/
│
├── index.php                # Ana sayfa
├── style.css                # Ana stil dosyası
├── script.js                # Ana JavaScript dosyası
├── particles.js             # Arkaplan efektleri
├── modern-portfolio.css     # Alternatif modern stil
│
├── api-status.php           # Platform durumları API
├── get-status.php           # Durum bilgisi alma 
├── github-portfolio.php     # GitHub portföy oluşturma
├── process-form.php         # İletişim formu işleme
├── spotify-callback.php     # Spotify oturum yakalama
├── spotify-auth.php         # Spotify yetkilendirme
│
├── discord-bot/             # Discord bot entegrasyonu
│   ├── discord-bot.js       # Discord bot kodu
│   ├── discord-api.php      # Discord API entegrasyonu
│   ├── README.md            # Bot dökümantasyonu
│   └── ...                  # Diğer bot dosyaları
│
└── logs/                    # Log ve durum dosyaları
    ├── discord_status.json  # Discord durum bilgileri
    └── ...                  # Diğer log dosyaları
```

## 🔄 Discord Bot Entegrasyonu

Discord bot entegrasyonu, kullanıcının Discord platformundaki durumunu (çevrimiçi, boşta, rahatsız etmeyin, çevrimdışı) ve oynadığı oyun/aktivite bilgilerini takip ederek web sitesinde gösterir.

Bot ile ilgili detaylı bilgi için [discord-bot/README.md](discord-bot/README.md) dosyasına bakın.

## 🎵 Spotify Entegrasyonu

Spotify entegrasyonu, kullanıcının o anda Spotify'da dinlediği müzik bilgilerini (şarkı adı, sanatçı, albüm kapağı) gerçek zamanlı olarak web sitesinde gösterir.

Kurulum için:

1. [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/) üzerinden bir uygulama oluşturun
2. Redirect URI olarak `https://your-site.com/spotify-callback.php` ekleyin
3. Client ID ve Client Secret bilgilerini `.env` dosyasına ekleyin
4. Web sitenizi ziyaret edin ve Spotify yetkilendirmesini tamamlayın

## 🖥️ GitHub Portföy Entegrasyonu

GitHub entegrasyonu, belirtilen GitHub kullanıcısının repolarını çeker ve portföy bölümünde gösterir. Repolar yıldız sayısı, güncelleme tarihi veya oluşturma tarihine göre sıralanabilir.

## 📄 Lisans

Bu proje BSD 3-Clause "New" or "Revised" lisansı altında lisanslanmıştır. Daha fazla bilgi için [LICENSE](LICENSE) dosyasına bakın.

## ✨ Özelleştirme

Site görünümünü veya davranışını özelleştirmek için:

- `style.css` veya `modern-portfolio.css` dosyalarını düzenleyin
- `script.js` dosyasında JavaScript davranışlarını değiştirin
- `particles.js` dosyasında arkaplan parçacık efektlerini ayarlayın
- `index.php` dosyasında HTML yapısını değiştirin

## 🤝 Katkıda Bulunma

Katkılarınızı memnuniyetle karşılıyoruz! Lütfen:

1. Projeyi fork edin
2. Yeni özellik dalı oluşturun (`git checkout -b yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik: özellik açıklaması'`)
4. Dalınızı push edin (`git push origin yeni-ozellik`)
5. Pull Request oluşturun

---

Geliştirici: [KynuxDev](https://github.com/kynux.dev) | © 2025 Tüm hakları saklıdır.
