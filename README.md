# 🚀 KynuxDev Kişisel Portföy v3.5.1

<div align="center">
  
  <img src="https://api.kynux.cloud/img/kynuxcloud-logo.png" alt="KynuxDev Logo" width="320" class="main-logo" style="margin: 20px auto; filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.8));" />

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
          <li>Discord aktivite ve durum takibi (Lanyard API)</li>
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

## 🛠️ Kurulum ve Yapılandırma Rehberi

### Gereksinimler

- PHP 7.4 veya daha yüksek
- Web sunucusu (Apache/Nginx)
- XAMPP, WAMP veya benzeri bir sunucu ortamı (yerel geliştirme için)
- Composer (önerilen)
- Discord Kullanıcı ID (Lanyard API için)
- Spotify Geliştirici Hesabı (Spotify entegrasyonu için)

### 1. Temel Kurulum Adımları

```bash
# Repository'yi klonlayın
git clone https://github.com/kynuxdev/personal-website.git

# Proje dizinine gidin
cd personal-website

# .env.example dosyasını .env olarak kopyalayın
cp .env.example .env

# .env dosyasını düzenleyin
nano .env
```

### 2. Çevresel Değişkenleri Yapılandırma

`.env` dosyasını aşağıdaki bilgilerle güncelleyin:

```env
# GitHub API Yapılandırması
GITHUB_TOKEN=github_token_buraya
GITHUB_USERNAME=github_kullanıcı_adınız

# Sosyal Medya Bağlantıları
LINKEDIN_URL=https://linkedin.com/in/kullaniciadi
GITHUB_URL=https://github.com/kullaniciadi
TWITTER_URL=https://twitter.com/kullaniciadi

# İletişim Bilgileri
CONTACT_EMAIL=mail@adresiniz.com
```

### 3. Spotify API Entegrasyonu

Spotify API entegrasyonu, sitenizin şu anda çalan müziği gerçek zamanlı olarak göstermesini sağlar.

#### 3.1. Spotify Geliştirici Hesabı Oluşturma

1. [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/) adresine gidin
2. Spotify hesabınızla giriş yapın (yoksa önce bir hesap oluşturun)
3. "Create an App" butonuna tıklayın
4. Uygulama için bir ad ve açıklama girin (örn. "Kişisel Web Sitesi")
5. Uygulamanın amacını seçin ve "Web API" seçeneğini işaretleyin
6. Kullanım koşullarını kabul edin ve "Create" butonuna tıklayın

#### 3.2. Redirect URI Ekleme

1. Yeni oluşturulan uygulamanızın panelinde "Edit Settings" butonuna tıklayın
2. "Redirect URIs" bölümüne aşağıdaki URI'yi ekleyin:
   ```
   http://localhost/spotify-callback.php
   ```
   (veya web siteniz canlıysa: `https://sizin-siteniz.com/spotify-callback.php`)
3. "Save" butonuna tıklayarak değişiklikleri kaydedin

#### 3.3. API Bilgilerini Yapılandırma

1. Uygulamanızın Client ID ve Client Secret bilgilerini panelden kopyalayın
2. Web sitenizde `spotify-setup.php` sayfasına gidin
3. Client ID ve Client Secret bilgilerini bu sayfadaki forma girin ve kaydedin
4. "Spotify Hesabını Bağla" butonuna tıklayın ve yetkilendirmeyi tamamlayın
5. Yetkilendirme başarılı olduğunda otomatik olarak yönlendirileceksiniz

#### 3.4. Sorun Giderme

Spotify bağlantısında bir sorun yaşarsanız:
1. `spotify-setup.php` sayfasına gidin
2. "Bağlantıyı Sıfırla" butonunu kullanarak tüm token ve log dosyalarını temizleyin
3. Client ID ve Client Secret bilgilerinizi doğrulayın ve yeniden girin
4. Yetkilendirme sürecini tekrar başlatın

### 4. Discord Entegrasyonu

Discord durumunuzu göstermek için Lanyard API kullanılmaktadır:

#### 4.1. Lanyard API İle Entegrasyon

[Lanyard API](https://github.com/Phineas/lanyard), Discord durumunuzu kolayca göstermenize olanak tanır ve herhangi bir bot gerektirmez.

1. Discord kullanıcı ID'nizi bulun:
   - Discord'da ayarlarınıza gidin > Gelişmiş > Geliştirici Modu'nu etkinleştirin
   - Profil resminize sağ tıklayın ve "ID'yi Kopyala" seçeneğini seçin
   
2. `get-status.php` dosyasındaki Discord kullanıcı ID'nizi güncelleyin:
   ```php
   $user_id = getenv('DISCORD_USER_ID') ?: 'DISCORD_KULLANICI_ID_BURAYA';
   ```

3. `.env` dosyanıza Discord ID'nizi ekleyin:
   ```env
   DISCORD_USER_ID=123456789012345678
   ```

4. [Lanyard Discord sunucusuna](https://discord.gg/lanyard) katılın (API'yi kullanabilmek için)

### 5. GitHub Portföy Entegrasyonu

GitHub projelerinizi göstermek için:

1. [GitHub](https://github.com/settings/tokens)'da bir kişisel erişim tokeni oluşturun
2. Token'a `public_repo` izinlerini verin
3. `.env` dosyasına ekleyin:
   ```env
   GITHUB_TOKEN=your_github_token_here
   GITHUB_USERNAME=your_github_username
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
├── get-status.php           # Platform durumları API
├── spotify-auth.php         # Spotify yetkilendirme
├── spotify-callback.php     # Spotify oturum yakalama
├── spotify-setup.php        # Spotify ayarlar ve bağlantı yönetimi
│
├── process-form.php         # İletişim formu işleme
├── github-portfolio.php     # GitHub portföy oluşturma
│
├── .env                     # Çevresel değişkenler
├── .env.example             # Örnek çevresel değişkenler dosyası
│
# Not: Discord entegrasyonu, 'discord-bot' klasörü yerine Lanyard API ile
# doğrudan get-status.php içinde gerçekleştirilmektedir
│
└── logs/                    # Log ve durum dosyaları
```

## 🚀 Platformları Test Etme

### Spotify Entegrasyonu Testi

1. `spotify-setup.php` sayfasına gidin ve bağlantı durumunu kontrol edin
2. Bağlantı kurulduğunda, Spotify'ı açın ve bir müzik çalmaya başlayın
3. Ana sayfaya gidin ve birkaç saniye içinde müzik bilgilerinin güncellenmesini bekleyin
4. Sorun yaşarsanız:
   - Tarayıcı konsolunu açın ve hata mesajlarını kontrol edin
   - `spotify-setup.php` sayfasından "Bağlantıyı Sıfırla" işlemini kullanın
   - Token'ların yenilenip yenilenmediğini logs klasöründeki dosyalardan kontrol edin

### Discord Entegrasyonu Testi

1. Discord'u açın ve durumunuzu değiştirin (çevrimiçi, meşgul, vb.)
2. Ana sayfada Discord durumunuzun güncellendiğini görmelisiniz (30 saniye kadar sürebilir)
3. Bir oyun başlatın veya aktivite yapın; ana sayfada görünmelidir

## 🎨 Özelleştirme

Site görünümünü değiştirmek için:

- Tema ve renkler: `modern-portfolio.css` içindeki CSS değişkenlerini düzenleyin
- Animasyonlar: `script.js` dosyasındaki animasyon ayarlarını değiştirin
- Arkaplan: `particles.js` konfigürasyonunu özelleştirin

## 💡 Sorun Giderme

### Spotify Bağlantı Sorunları

- **Sorun:** "Şu anda müzik dinlenmiyor" hatası, müzik çalıyorken
  - **Çözüm:** `spotify-setup.php` sayfasına gidin ve "Bağlantıyı Sıfırla" butonuna tıklayın, ardından hesabınızı yeniden bağlayın
  
- **Sorun:** Token yenileme hatası
  - **Çözüm:** 
    1. `spotify_config.json` dosyasını kontrol edin
    2. Spotify Developer Dashboard'da Redirect URI'nin doğru yapılandırıldığından emin olun
    3. Bağlantıyı sıfırlayın ve yeniden yetkilendirin

### Discord Durum Gösterme Sorunları

- **Sorun:** Discord durumu güncellenmiyor
  - **Çözüm:**
    1. Discord kullanıcı ID'nizin doğru girildiğinden emin olun
    2. Lanyard Discord sunucusuna katılıp katılmadığınızı kontrol edin
    3. Sayfayı yenileyin ve birkaç dakika bekleyin (Lanyard API güncellemeleri biraz zaman alabilir)

### GitHub Portföy Sorunları

- **Sorun:** Repolar görüntülenmiyor
  - **Çözüm:**
    1. GitHub token'ınızın doğru olduğundan emin olun
    2. GitHub API istek sınırını aşıp aşmadığınızı kontrol edin

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
- Web: [kynux.cloud](https://kynux.cloud)

---

<div align="center">
  <p>© 2025 KynuxDev. Tüm hakları saklıdır.</p>
  <p>
    <img src="https://i.ibb.co/JRWpKCcM/s.png" alt="Logo" width="80" height="80" style="filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.6)); margin: 10px auto;"/>
  </p>
</div>
