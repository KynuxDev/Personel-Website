# 🚀 KynuxDev Platform Değişiklik Günlüğü

<div align="center">

![KynuxDev Logo](https://i.ibb.co/JRWpKCcM/s.png)

**Platformumuzdaki tüm değişiklikleri, yenilikleri ve güncellemeleri takip edin**

![Version](https://img.shields.io/badge/Platform%20Sürümü-v3.5.1-blue?style=for-the-badge)
![Last Updated](https://img.shields.io/badge/Son%20Güncelleme-26%20Nisan%202025-success?style=for-the-badge)
![Status](https://img.shields.io/badge/Durum-Aktif-success?style=for-the-badge)

</div>

## 🔄 Sürüm Geçmişi

Bu değişiklik günlüğü, [Semantik Sürümlendirme](https://semver.org/lang/tr/) formatına uygun olarak hazırlanmıştır.

---

## [v3.5.1] - 2025-04-26

<table>
<tr>
<td width="60%">

### 🔄 Önemli Değişiklikler

- **Discord Bot Entegrasyonu Kaldırıldı**
  - Discord bot tamamen kaldırıldı
  - Lanyard API entegrasyonu eklendi (https://api.lanyard.rest)
  - Discord durumu için yeni API üzerinden doğrudan bağlantı
  - Daha hızlı ve güvenilir durum güncellemeleri
  - Daha az sistem kaynağı kullanımı

- **Güvenlik İyileştirmeleri**
  - Session yönetimi eklendi
  - CSRF token güvenliği artırıldı
  - Hata yakalama mekanizmaları geliştirildi

- **Performans Optimizasyonu**
  - API çağrıları ve istekleri optimize edildi
  - Dizin yapısı kontrolleri iyileştirildi
  - Dosya işlemleri daha güvenli hale getirildi

</td>
<td>

<h3>📊 Güncelleme Özeti</h3>

<div align="center">
<img src="https://i.ibb.co/4LW01vd/image.png" alt="Discord Entegrasyonu" width="300">
<br>
<small><i>Lanyard API ile Discord Durum Entegrasyonu</i></small>
</div>

<h4>🔍 Öne Çıkan Değişiklikler</h4>

- ✅ Discord bot yerine Lanyard API
- ✅ Daha hızlı durum güncellemeleri
- ✅ Gelişmiş oturum güvenliği
- ✅ URL formatı düzeltmeleri
- ✅ CSS seçici uyumlulukları
- ✅ Spotify token yönetimi iyileştirmesi

</td>
</tr>
</table>

## [v3.5.0] - 2025-04-20

<table>
<tr>
<td width="60%">

### 🎨 Platform Yenilikleri

- **Web Sitesi Tasarımı Tamamen Yenilendi**
  - Modern ve minimalist arayüz
  - Daha temiz başlıklar ve geliştirilmiş tipografi
  - Responsive tasarım iyileştirmeleri
  - Gelişmiş animasyonlar ve geçiş efektleri
  - Koyu mod optimizasyonu

- **GitHub Portföy Sayfası Güncellendi**
  - Repo görünümleri tamamen yenilendi
  - Daha detaylı commit ve dal istatistikleri
  - Proje zaman çizelgesi görünümü
  - Kod parçacıkları önizlemesi

- **Performans İyileştirmeleri**
  - %35 daha hızlı sayfa yükleme süresi
  - Optimize edilmiş görseller ve varlıklar
  - Önbellek stratejileri geliştirildi
  - Lazy loading implementasyonu

</td>
<td>

<h3>📊 Özet İstatistikler</h3>

<div align="center">
<img src="https://i.ibb.co/ds69MW91/image.png" alt="Platform Performans Grafiği" width="300">
<br>
<small><i>Platform Performans ve Beceri İyileştirmeleri</i></small>
</div>

<h4>🔍 Öne Çıkan Değişiklikler</h4>

- ✅ Tamamen yeniden tasarlanmış arayüz
- ✅ 3 yeni platform entegrasyonu
- ✅ Discord Bot v2.0.0 güncellendi
- ✅ Spotify bağlantısı geliştirildi
- ✅ GitHub API v4 implementasyonu
- ✅ Mobil uyumluluk %100'e çıkarıldı

</td>
</tr>
</table>

### 🤖 Lanyard API ile Discord Entegrasyonu

<details>
<summary><b>Discord Entegrasyonu Değişikliklerini Görüntüle</b></summary>

<div align="center">
  <img src="https://i.ibb.co/4LW01vd/image.png" alt="Discord Entegrasyonu" width="800"/>
  <br/>
  <i>Discord Durum ve Aktivite Entegrasyonu</i>
</div>

#### 🔄 Mimari Değişiklikler

- **Discord Bot Tamamen Kaldırıldı**
  - Discord bot ve ilgili modüller sistemden tamamen kaldırıldı
  - Sistem kaynakları optimize edildi
  - Bakım yükü azaltıldı

- **Lanyard API Entegrasyonu**
  - Lanyard API (https://api.lanyard.rest) üzerinden doğrudan bağlantı
  - Tek bir endpoint ile gerçek zamanlı durum bilgisi
  - Basit ve güvenilir API çağrıları
  - Token ve bot gerekmeden çalışır

#### ✨ Entegrasyon Özellikleri

- **Gerçek Zamanlı Durum Takibi**
  - Çevrimiçi, boşta, rahatsız etmeyin, çevrimdışı durumları
  - Oyun ve aktivite bilgileri
  - Platform bilgisi (masaüstü, web, mobil)

- **Minimum Yapılandırma**
  - Sadece Discord kullanıcı ID gerekli
  - Ek yapılandırma veya token gerektirmez
  - Otomatik bağlantı ve yeniden deneme

#### 🔧 Kullanım Örneği

```php
// Discord kullanıcı ID'niz
$user_id = '1244181502795976775';

// Lanyard API endpoint
$api_url = "https://api.lanyard.rest/v1/users/{$user_id}";

// API çağrısı
$response = file_get_contents($api_url);
$data = json_decode($response, true);

// Durum bilgilerini al
$status = $data['data']['discord_status'] ?? 'offline';
$activities = $data['data']['activities'] ?? [];
```

#### 🔒 Güvenlik ve Performans

- **Daha Az API İsteği**
  - Lanyard, Discord API rate limitlerine tabi değil
  - Daha hızlı ve güvenilir yanıt süreleri

- **Daha Az Bağımlılık**
  - Bot token ve yetkileri gerektirmez
  - Hata olasılığı azaltıldı
  - Bakım ve izleme yükü azaltıldı

</details>

### 🎵 Spotify Entegrasyonu v1.5.0

<details>
<summary><b>Spotify Entegrasyonu Değişikliklerini Görüntüle</b></summary>

<div align="center">
  <img src="https://i.ibb.co/NvGsrMP/image.png" alt="Spotify Entegrasyonu" width="800"/>
  <br/>
  <i>Spotify Şu Anda Çalınan Şarkı Entegrasyonu</i>
</div>

#### ✨ Yeni Özellikler

- **Canlı "Şimdi Çalıyor" Widget'ı**
  - Gerçek zamanlı güncellenen şarkı bilgileri
  - Albüm kapağı ve sanatçı gösterimi
  - İlerleme çubuğu eklendi

- **Otomatik Yetkilendirme Yenileme**
  - Refresh token ile kesintisiz bağlantı
  - Session yönetimi geliştirildi

- **Şarkı Geçmişi**
  - Son dinlenen 50 şarkının kaydı
  - Filtreleme ve arama özellikleri
  - Günlük/haftalık dinleme istatistikleri

#### 🔧 İyileştirmeler

- API çağrıları optimize edildi
- Önbellek mekanizması eklendi
- Hata yakalama ve bildirim sistemi geliştirildi

#### 🐛 Hata Düzeltmeleri

- Uzun süre oturum açıkken token zaman aşımı sorunu giderildi
- Şarkı değişikliği algılanamama hatası düzeltildi
- Özel karakterler içeren şarkı adlarında görüntüleme sorunu çözüldü

#### 📝 Kurulum Adımları

1. [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/) üzerinden uygulama oluşturun
2. Redirect URI: `https://your-site.com/spotify-callback.php`
3. Client ID ve Client Secret bilgilerini `.env` dosyasına ekleyin
4. Web sitesini ziyaret ederek Spotify yetkilendirmesini tamamlayın

</details>

### 💻 Web Sitesi Bileşenleri

<details>
<summary><b>Web Sitesi Değişikliklerini Görüntüle</b></summary>

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
  </table>
</div>

#### ✨ Yeni Bileşenler

- **Modern Portföy Kartları**
  - Animasyonlu açılır kapanır detaylar
  - Teknoloji rozet sistemi
  - Daha gelişmiş görsel önizlemeler

- **Filtreleme ve Sıralama Sistemi**
  - Teknolojiye göre proje filtreleme
  - Tarih, popülerlik ve aktiviteye göre sıralama
  - Kayıtlı filtre tercihleri

- **Tema Değiştirici**
  - Koyu/Açık mod geçişi
  - Tema renk paleti seçenekleri
  - Kullanıcı tercihi kaydetme

#### 🔧 İyileştirmeler

- CSS değişkenleri kullanılarak daha modüler tasarım
- Sayfa geçişlerinde daha akıcı animasyonlar
- SVG ikonlar ve vektörler ile daha kaliteli görseller
- Tüm yazı tipleri için yerel yedekleme sistemi

#### 🐛 Hata Düzeltmeleri

- Mobil görünümdeki taşma sorunları giderildi
- Safari'de yaşanan render sorunları çözüldü
- Internet Explorer desteği kaldırıldı (artık desteklenmiyor)
- Klavye navigasyonu ve erişilebilirlik sorunları düzeltildi

</details>

### 📱 Duyarlı Tasarım İyileştirmeleri

<div align="center">
  <table>
    <tr>
      <td align="center">
        <img src="https://i.ibb.co/7xHjQk8q/image.png" alt="Platform Durumları" width="400"/>
        <br/>
        <i>Platform Durumları (Responsive Görünüm)</i>
      </td>
    </tr>
  </table>
</div>

- Mobil cihazlar için tamamen optimize edilmiş görünüm
- Tablet için özel düzen optimizasyonları
- Farklı ekran boyutları için akıllı içerik yerleşimi
- Dokunma hedefleri büyütüldü
- Yazı boyutları ve kontrast geliştirildi

### 🌐 SEO ve Erişilebilirlik

- Semantik HTML yapısı iyileştirildi
- ARIA etiketleri ve roller eklendi
- Alt metinleri tamamlandı
- Sayfa başlıkları ve meta açıklamaları optimize edildi
- Yapısal veri (schema.org) eklendi

---

## [v3.4.2] - 2025-03-20

### 🐛 Hata Düzeltmeleri

- Web sitesindeki sayfalama hatası düzeltildi
- GitHub API bağlantısında yaşanan sorunlar giderildi
- Firefox'ta çalışmayan bazı animasyonlar iyileştirildi

### 🛠️ Bakım

- Bağımlılıklar güncellendi
- Güvenlik yamaları uygulandı
- Kod kalitesi iyileştirmeleri yapıldı

---

## [v3.4.1] - 2025-03-02

### 🔒 Güvenlik Güncellemeleri

- Tüm API anahtarları rotasyonu
- XSS koruması güçlendirildi
- CSRF token implementasyonu geliştirildi

---

## [v3.4.0] - 2025-02-15

### ✨ Yeni Özellikler

- **Portföy Sayfasına Filtreleme Seçenekleri Eklendi**
  - Teknolojiye göre filtreleme
  - Tarihe göre sıralama
  - Popülerliğe göre sıralama

- **Spotify Entegrasyonu Yenilendi (v1.4.0)**
  - Şarkı geçmişi özelliği eklendi
  - Çalma listesi paylaşımı geliştirildi

### 🔧 İyileştirmeler

- Sayfa yükleme süreleri optimize edildi
- Google Lighthouse puanları iyileştirildi
- Erişilebilirlik güncellemeleri yapıldı

---

## [v3.3.0] - 2025-01-10

### ✨ Yeni Özellikler

- **Discord Bot İlk Sürümü Yayınlandı (v1.0.0)**
  - Discord durum takibi
  - Aktivite gösterimi
  - Web sitesi entegrasyonu

### 🔧 İyileştirmeler

- Ana sayfa yeniden tasarlandı
- Animasyonlar optimize edildi
- Mobil uyumluluk geliştirildi

---

<div align="center">

## 📅 Gelecek Sürüm Planlaması

**v3.6.0** (Planlanan: Haziran 2025)
- Blog sistemi
- Kayıt ve kullanıcı profilleri
- Yorum sistemi

**v4.0.0** (Planlanan: Aralık 2025)
- Tamamen yeni tasarım
- SPA mimarisine geçiş
- Gerçek zamanlı işbirliği özellikleri

</div>

---

<div align="center">

Değişiklik günlüğü hakkında sorularınız mı var? [İletişime geçin](https://kynux.dev/contact)

[KynuxDev Platform](https://kynux.cloud) | [GitHub](https://github.com/kynuxdev) | [Discord](https://discord.com/channels/@me/1244181502795976775)

</div>
