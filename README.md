# 🚀 KynuxDev Kişisel Portföy (EXAMPLE)

<div align="center">
  
  ![Banner](https://i.ibb.co/JRWpKCcM/s.png)

  Modern ve interaktif teknolojilerle geliştirilmiş profesyonel kişisel web sitesi

  [![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
  [![CSS3](https://img.shields.io/badge/CSS3-Modern-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
  [![HTML5](https://img.shields.io/badge/HTML5-Semantic-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
  
</div>

## 📸 Ekran Görüntüleri

<div align="center">
  <table>
    <tr>
      <td align="center">
        <img src="https://i.ibb.co/V0cJSm5x/image.png" alt="Ana Sayfa" width="400"/>
        <br/>
        <i>Ana Sayfa</i>
      </td>
      <td align="center">
        <img src="https://i.ibb.co/HLLFNXmV/image.png" alt="GitHub Portföy" width="400"/>
        <br/>
        <i>GitHub Portföy</i>
      </td>
    </tr>
    <tr>
      <td align="center">
        <img src="https://i.ibb.co/7xHjQk8q/image.png" alt="Platform Durumları" width="400"/>
        <br/>
        <i>Platform Durumları</i>
      </td>
      <td align="center">
        <img src="https://i.ibb.co/ds69MW91/image.png" alt="Beceri Setim" width="400"/>
        <br/>
        <i>Beceri Setim</i>
      </td>
    </tr>
  </table>
</div>

## ✨ Özellikler

<div align="center">
  <table>
    <tr>
      <td>
        <h3>🔄 Gerçek Zamanlı Platform Entegrasyonları</h3>
        <ul>
          <li>GitHub Repo verileri otomatik güncellenir</li>
          <li>Discord aktivite ve durum takibi</li>
          <li>Spotify dinleme bilgileri canlı gösterimi</li>
        </ul>
      </td>
      <td>
        <h3>🎨 Modern Tasarım</h3>
        <ul>
          <li>Animasyonlar ve geçiş efektleri</li>
          <li>Responsive tasarım (mobil uyumlu)</li>
          <li>Dark mode & neon vurgular</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td>
        <h3>🔒 Güvenlik</h3>
        <ul>
          <li>CSRF korumalı formlar</li>
          <li>Girdi doğrulama ve temizleme</li>
          <li>Güvenli API entegrasyonları</li>
        </ul>
      </td>
      <td>
        <h3>🌐 SEO ve Erişilebilirlik</h3>
        <ul>
          <li>Semantik HTML yapısı</li>
          <li>Arama motoru optimizasyonu</li>
          <li>Çoklu dil desteği</li>
        </ul>
      </td>
    </tr>
  </table>
</div>

## 🛠️ Kurulum

### Gereksinimler

- PHP 7.4 veya daha yüksek
- Web sunucusu (Apache/Nginx)
- Composer (önerilen)
- Discord Bot Token (isteğe bağlı)
- Spotify Geliştirici Hesabı (isteğe bağlı)

### Hızlı Başlangıç

```bash
# Repository'yi klonlayın
git clone https://github.com/kynuxdev/personal-website.git

# Proje dizinine gidin
cd personal-website

# .env.example dosyasını .env olarak kopyalayın
cp .env.example .env

# .env dosyasını düzenleyin ve API anahtarlarını ekleyin
nano .env
```

### 🔧 Yapılandırma

`.env` dosyasını ana dizinde oluşturun:

```env
# GitHub API
GITHUB_TOKEN=github_token_buraya

# Discord Bot
DISCORD_BOT_TOKEN=discord_token_buraya
DISCORD_USER_ID=discord_user_id_buraya

# Spotify API
SPOTIFY_CLIENT_ID=spotify_client_id_buraya
SPOTIFY_CLIENT_SECRET=spotify_client_secret_buraya
```

## 📂 Proje Yapısı

```
kynux-portfolio/
│
├── index.php                # Ana sayfa
├── style.css                # Ana stil dosyası
├── script.js                # Ana JavaScript dosyası
├── particles.js             # Arkaplan efektleri
├── modern-portfolio.css     # Alternatif modern stil
│
├── api/                     # API entegrasyonları
│   ├── api-status.php       # Platform durumları API
│   ├── get-status.php       # Durum bilgisi alma
│   └── process-form.php     # İletişim formu işleme
│
├── github/                  # GitHub entegrasyonu
│   └── github-portfolio.php # GitHub portföy oluşturma
│
├── spotify/                 # Spotify entegrasyonu
│   ├── spotify-callback.php # Spotify oturum yakalama
│   └── spotify-auth.php     # Spotify yetkilendirme
│
├── discord-bot/             # Discord bot entegrasyonu
│   ├── discord-bot.js       # Discord bot kodu
│   ├── discord-api.php      # Discord API entegrasyonu
│   └── README.md            # Bot dökümantasyonu
│
└── logs/                    # Log ve durum dosyaları
    └── discord_status.json  # Discord durum bilgileri
```

## 🔌 Platform Entegrasyonları

### 🎮 Discord Entegrasyonu

<div align="center">
  <img src="https://i.ibb.co/4LW01vd/image.png" alt="Discord Entegrasyonu" width="800"/>
  <br/>
  <i>Discord Durum ve Aktivite Entegrasyonu</i>
</div>

Discord entegrasyonu aşağıdaki bilgileri gösterir:
- Çevrimiçi durumu (çevrimiçi, boşta, rahatsız etmeyin, çevrimdışı)
- Oynadığınız oyun veya kullandığınız uygulama
- Oturum süresi ve aktivite bilgileri

```javascript
// Discord durum güncelleme örneği
client.on('presenceUpdate', async (oldPresence, newPresence) => {
  if (newPresence.userId === process.env.DISCORD_USER_ID) {
    // Durum bilgilerini güncelle
    const status = newPresence.status;
    const activity = newPresence.activities[0]?.name || '';
    
    // JSON dosyasına kaydet
    await updateStatusFile({ status, activity });
  }
});
```

### 🎵 Spotify Entegrasyonu

<div align="center">
  <img src="https://i.ibb.co/NvGsrMP/image.png" alt="Spotify Entegrasyonu" width="800"/>
  <br/>
  <i>Spotify Şu Anda Çalınan Şarkı Entegrasyonu</i>
</div>

Spotify entegrasyonu kurulumu:

1. [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/) üzerinden uygulama oluşturun
2. Redirect URI: `https://your-site.com/spotify-callback.php`
3. Client ID ve Client Secret bilgilerini `.env` dosyasına ekleyin
4. Web sitesini ziyaret ederek Spotify yetkilendirmesini tamamlayın

### 💻 GitHub Portföy Entegrasyonu

GitHub portföy özelliği aşağıdaki gruplandırmalarla projeleri gösterir:
- En çok yıldız alan projeler
- Son güncellenen projeler
- Aktif geliştirilen projeler

## 🎨 Özelleştirme

Site görünümünü değiştirmek için:

- Tema ve renkler: `modern-portfolio.css` içindeki CSS değişkenlerini düzenleyin
- Animasyonlar: `script.js` dosyasındaki animasyon ayarlarını değiştirin
- Arkaplan: `particles.js` konfigürasyonunu özelleştirin

## 📄 Lisans

Bu proje, BERK tarafından geliştirilmiş özgün bir çalışmadır ve KynuxCloud Korumalı Geliştirici Lisansı ile korunmaktadır. Tüm hakları saklıdır.

- Bu kodun kopyalanması, değiştirilmesi veya dağıtılması **KESİNLİKLE YASAKTIR**
- Kod, otomatik izleme sistemleri ile telif ihlallerine karşı düzenli olarak taranmaktadır
- İhlaller tespit edildiğinde, GitHub DMCA işlemleri ve yasal kovuşturma başlatılacaktır

Detaylı lisans metni için [LICENSE](LICENSE) dosyasını inceleyiniz.

## 📬 İletişim

Sorularınız veya önerileriniz mi var? Benimle iletişime geçin:

- GitHub: [KynuxDev](https://github.com/kynuxdev)
- Discord: [@kynux_dev](https://discord.com/channels/@me/1244181502795976775)
- Web: [kynux.dev](https://kynux.cloud)

---

<div align="center">
  <p>© 2025 KynuxDev. Tüm hakları saklıdır.</p>
  <p>
    <img src="https://i.ibb.co/JRWpKCcM/s.png" alt="Logo" width="40" height="40"/>
  </p>
</div>
