# 🤖 KynuxDev Discord Durum Takip Botu v2.0.0

<div align="center">

**Gelişmiş Discord kullanıcı durumu takibi ve platform entegrasyonu**

![Version](https://img.shields.io/badge/Sürüm-v2.0.0-blue?style=for-the-badge)
![Discord API](https://img.shields.io/badge/Discord%20API-v10-5865F2?style=for-the-badge&logo=discord&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-16.9+-339933?style=for-the-badge&logo=node.js&logoColor=white)
![Status](https://img.shields.io/badge/Durum-Aktif-success?style=for-the-badge)

</div>

## 📋 İçindekiler

- [Özellikler](#-özellikler)
- [Kurulum](#-kurulum)
- [Yapılandırma](#-yapılandırma)
- [Kullanım](#-kullanım)
- [Durum JSON Formatı](#-durum-json-formatı)
- [Yeni Özellikler ve Değişiklikler](#-yeni-özellikler-ve-değişiklikler)
- [Webhook Entegrasyonu](#-webhook-entegrasyonu)
- [Sorun Giderme](#-sorun-giderme)
- [İletişim](#-iletişim)

## ✨ Özellikler

<div align="center">
  <table>
    <tr>
      <td>
        <h3>🔄 Gerçek Zamanlı Takip</h3>
        <ul>
          <li>Kullanıcı durumu (online, idle, dnd, offline)</li>
          <li>Oyun ve aktivite bilgileri</li>
          <li>Spotify entegrasyonu ile şarkı bilgileri</li>
          <li>Özel durum mesajları</li>
        </ul>
      </td>
      <td>
        <h3>🔔 Bildirim Sistemi</h3>
        <ul>
          <li>Durum değişikliklerinde webhook bildirimleri</li>
          <li>Discord, Slack ve MS Teams entegrasyonu</li>
          <li>Özelleştirilebilir bildirim mesajları</li>
          <li>Filtreleme seçenekleri</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td>
        <h3>👥 Çoklu Hesap Desteği</h3>
        <ul>
          <li>Birden fazla Discord hesabı izleme</li>
          <li>Her hesap için ayrı yapılandırma</li>
          <li>Merkezi yönetim paneli</li>
          <li>Toplu durum analizleri</li>
        </ul>
      </td>
      <td>
        <h3>📊 Gelişmiş İstatistikler</h3>
        <ul>
          <li>Günlük/haftalık/aylık aktivite grafikleri</li>
          <li>Oyun ve uygulama kullanım analizleri</li>
          <li>Çevrimiçi süre takibi</li>
          <li>Özelleştirilebilir raporlar</li>
        </ul>
      </td>
    </tr>
  </table>
</div>

## 🚀 Kurulum

### Gereksinimler

- Node.js 16.9 veya daha yüksek sürüm
- npm veya yarn
- Discord hesabı ve geliştirici portalına erişim

### Adım Adım Kurulum

1. Node.js ve npm'i [buradan](https://nodejs.org/) (LTS sürümü önerilir) yükleyin
2. Repo içindeki bütün dosyaları klonlayın:

```bash
git clone https://github.com/kynuxdev/discord-status-bot.git
cd discord-status-bot
```

3. Bağımlılıkları yükleyin:

```bash
npm install
# veya
yarn install
```

### Discord Botunu Oluşturma

<div align="center">
  <img src="https://i.ibb.co/7xHjQk8q/image.png" alt="Discord Developer Portal" width="600"/>
  <br/>
  <i>Discord Developer Portal Ekranı</i>
</div>

1. [Discord Developer Portal](https://discord.com/developers/applications)'a gidin
2. "New Application" butonuna tıklayın
3. Uygulamanıza bir isim verin ve oluşturun
4. Sol menüden "Bot" sekmesine tıklayın
5. "Add Bot" butonuna tıklayın
6. Bot bilgileri altında "Reset Token" butonuna tıklayın ve token'ı kopyalayın
7. "Privileged Gateway Intents" bölümünden aşağıdaki izinleri açın:
   - PRESENCE INTENT
   - SERVER MEMBERS INTENT
   - MESSAGE CONTENT INTENT (webhook özellikleri için)
8. Sol menüden "OAuth2" sekmesine tıklayın, sonra "URL Generator" alt sekmesine geçin
9. Scope bölümünde "bot" seçeneğini işaretleyin
10. Bot Permissions bölümünde gerekli izinleri seçin:
    - Read Messages/View Channels
    - Send Messages (webhook özellikleri için)
11. Oluşturulan URL'yi kopyalayın ve botunuzu sunucunuza ekleyin

## ⚙️ Yapılandırma

`.env` dosyasını oluşturun ve aşağıdaki bilgileri ekleyin:

```env
# Discord Bot - Temel Yapılandırma
DISCORD_BOT_TOKEN=your_new_token
DISCORD_CLIENT_ID=your_client_id
DISCORD_USER_ID=your_user_id

# İsteğe Bağlı Yapılandırma
DISCORD_GUILD_ID=optional_server_id
DISCORD_STATUS_WEBHOOK=optional_webhook_url

# Gelişmiş Yapılandırma
DISCORD_REFRESH_INTERVAL=30000
DISCORD_MAX_RECONNECT_ATTEMPTS=5
DISCORD_SECURE_MODE=true
```

### Webhook Yapılandırması (İsteğe Bağlı)

Webhook bildirimleri için, desteklenen platformlardan birinin webhook URL'sini yapılandırma dosyanıza ekleyin:

```env
# Discord Webhook
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your_webhook_url

# Slack Webhook
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/your_webhook_url

# Microsoft Teams Webhook
MS_TEAMS_WEBHOOK_URL=https://outlook.office.com/webhook/your_webhook_url
```

## 🖥️ Kullanım

```bash
# Botu başlatmak için:
node discord-bot.js

# Ya da başlangıç yardımcısını kullanmak için:
node start-discord-bot.js

# Sürekli çalışma için PM2 ile (önerilen):
pm2 start discord-bot.js --name discord-status
```

Bot çalıştığında:

1. Discord API'ye bağlanır ve konsola başarılı bağlantı mesajı yazar
2. Belirtilen kullanıcı(lar)ın durumunu izlemeye başlar
3. Durum değişikliklerini algıladığında JSON dosyasını günceller
4. Webhook ayarlandıysa bildirimleri gönderir
5. İstatistikleri toplar ve analiz eder

## 📊 Durum JSON Formatı

v2.0.0 ile birlikte geliştirilmiş JSON formatı:

```json
{
    "status": "online",
    "game": "Minecraft",
    "has_game": true,
    "username": "kullanıcı_adı",
    "discriminator": "0",
    "last_updated": "2025-04-26T10:22:35.123Z",
    "game_details": {
        "type": 0,
        "url": "https://example.com/game-link",
        "state": "Survival Modu",
        "details": "Crafting...",
        "assets": {
            "large_image": "image_id",
            "large_text": "Image tooltip text",
            "small_image": "image_id",
            "small_text": "Image tooltip text"
        }
    },
    "spotify": {
        "is_playing": true,
        "song": "Şarkı Adı",
        "artist": "Sanatçı Adı",
        "album": "Albüm Adı",
        "album_cover_url": "https://i.scdn.co/image/album_cover",
        "track_id": "spotify_track_id",
        "start_timestamp": 1682231356789,
        "end_timestamp": 1682231576789
    },
    "platform": "desktop",
    "premium_since": "2023-01-01T00:00:00.000Z",
    "connected_accounts": [
        {
            "type": "twitch",
            "name": "twitch_username",
            "verified": true
        }
    ]
}
```

## 🔄 Yeni Özellikler ve Değişiklikler

<details>
<summary><b>v2.0.0 Değişiklikleri (26 Nisan 2025)</b></summary>

### 🔄 Önemli Değişiklikler

- **Tamamen yeniden yazılmış Discord API entegrasyonu**
  - Eski API erişim yöntemi kullanımdan kaldırıldı
  - Discord API v10'a güncellendi
  - Eski token formatı artık desteklenmiyor (yeni token almanız gerekecek)

- **Olay Dinleyicileri Yapısı Güncellendi**
  - Olay tabanlı mimari tamamen yenilendi
  - `listenForEvents()` metodunu kullanmak yerine, artık tekil olay dinleyicileri oluşturulmalı

- **Ortam Değişkenleri Güncellendi**
  - `.env` yapısı değiştirildi - yeni değişken isimleri eklendi
  - Eski `DISCORD_STATUS_PATH` kaldırıldı, otomatik yapılandırma kullanılıyor

### ✨ Yeni Özellikler

- **Zengin Durum Gösterimi**
  - Spotify entegrasyonu ile şarkı bilgileri dahil edildi
  - Oyun veya uygulama aktiviteleri için resimler ve bağlantılar eklendi
  - Özel durum mesajları desteği eklendi

- **Çoklu Hesap Desteği**
  - Artık birden fazla Discord hesabı için durum takibi yapılabilir
  - Her hesap için ayrı yapılandırma oluşturulabilir

- **Durum Webhook Bildirimleri**
  - Durum değişikliklerinde webhook ile bildirim gönderebilme
  - Discord, Slack ve Microsoft Teams entegrasyonları eklendi

- **Gerçek Zamanlı İstatistikler**
  - Sunucu bağlantı durumu istatistikleri
  - Mesaj sayısı ve aktivite takibi
  - Günlük/haftalık/aylık aktivite grafikleri

### 🔧 İyileştirmeler

- **Performans Optimize Edildi**
  - Yeniden bağlantı kurma hızı %40 artırıldı
  - Bellek kullanımı %25 azaltıldı
  - CPU kullanımı optimize edildi

- **Bağlantı Dayanıklılığı Artırıldı**
  - Otomatik yeniden bağlanma mantığı iyileştirildi
  - Ağ kesintilerine karşı daha iyi dayanıklılık
  - WebSocket bağlantı havuzu eklendi

- **Kod Yapısı İyileştirildi**
  - Kod modüler hale getirildi
  - Promise tabanlı yapıya geçildi
  - TypeScript tip tanımları eklendi

### 🐛 Hata Düzeltmeleri

- **Bağlantı Sorunları Giderildi**
  - Discord API sınırlamalarında oluşan hata düzeltildi
  - WebSocket bağlantısı düşmesi sorunu çözüldü
  - Gateway timeout hatası giderildi

Tam değişiklik listesi için [CHANGELOG.md](../CHANGELOG.md) dosyasına bakın.
</details>

## 🔔 Webhook Entegrasyonu

<div align="center">
  <img src="https://i.ibb.co/NvGsrMP/image.png" alt="Webhook Integration Example" width="600"/>
  <br/>
  <i>Discord Webhook Bildirimi Örneği</i>
</div>

Webhook entegrasyonu ile durum değişikliklerinde otomatik bildirimler alabilirsiniz:

1. Discord/Slack/MS Teams üzerinde bir webhook URL'si oluşturun
2. `.env` dosyanıza ilgili webhook URL'sini ekleyin
3. Webhook ayarlarını özelleştirmek için `config/webhook-settings.json` dosyasını düzenleyin:

```json
{
  "events": {
    "status_change": true,
    "game_change": true,
    "spotify_change": true,
    "platform_change": true
  },
  "format": {
    "use_embeds": true,
    "include_timestamps": true,
    "include_user_avatar": true,
    "color_coding": true
  },
  "throttling": {
    "enabled": true,
    "min_interval_seconds": 60,
    "max_notifications_per_hour": 20
  }
}
```

## ❓ Sorun Giderme

<table>
<tr>
<th>Sorun</th>
<th>Çözüm</th>
</tr>
<tr>
<td>Bot bağlanamıyor</td>
<td>
- Token'ın doğruluğunu kontrol edin<br>
- Node.js sürümünüzün 16.9+ olduğundan emin olun<br>
- Gerekli intent'lerin Discord Developer Portal'da etkinleştirildiğini kontrol edin
</td>
</tr>
<tr>
<td>Durum güncellemeleri alınamıyor</td>
<td>
- Botun izlenen kullanıcı ile aynı sunucuda olduğundan emin olun<br>
- Presence intent'in açık olduğunu kontrol edin<br>
- Kullanıcı ID'sinin doğru olduğunu doğrulayın
</td>
</tr>
<tr>
<td>Spotify bilgileri görünmüyor</td>
<td>
- Kullanıcının Discord ve Spotify hesaplarının bağlı olduğundan emin olun<br>
- Discord ayarlarında "Aktiviteyi Göster" seçeneğinin açık olduğunu kontrol edin<br>
- Spotify entegrasyonunu Discord üzerinden yeniden bağlayın
</td>
</tr>
<tr>
<td>Webhook bildirimleri çalışmıyor</td>
<td>
- Webhook URL'sinin geçerli olduğunu kontrol edin<br>
- Bot'un ilgili kanala mesaj gönderme iznine sahip olduğundan emin olun<br>
- Webhook yapılandırma dosyasındaki event ayarlarını kontrol edin
</td>
</tr>
<tr>
<td>Bellek kullanımı çok yüksek</td>
<td>
- DISCORD_REFRESH_INTERVAL değerini artırın (ör. 60000ms)<br>
- İzlenen kullanıcı sayısını azaltın<br>
- PM2 ile memory-limit ayarlayın
</td>
</tr>
</table>

## 📝 Örnek Kod Kullanımı

Discord durum izleme örneği:

```javascript
// Discord durum güncelleme örneği
client.on('presenceUpdate', async (oldPresence, newPresence) => {
  if (newPresence.userId === process.env.DISCORD_USER_ID) {
    // Durum bilgilerini güncelle
    const status = newPresence.status;
    const activity = newPresence.activities[0]?.name || '';
    
    // JSON dosyasına kaydet
    await updateStatusFile({ status, activity });
    
    // Webhook bildirimini gönder (yapılandırıldıysa)
    if (process.env.DISCORD_STATUS_WEBHOOK) {
      await sendWebhookNotification({
        status,
        activity,
        user: newPresence.user
      });
    }
  }
});
```

## 📣 İletişim

Sorularınız veya önerileriniz mi var? Bizimle iletişime geçin:

- Discord: [@kynux_dev](https://discord.com/channels/@me/1244181502795976775)
- GitHub: [KynuxDev](https://github.com/kynuxdev)
- Web: [kynux.cloud](https://kynux.cloud)

<div align="center">
  <p>© 2025 KynuxDev. Tüm hakları saklıdır.</p>
  <p>
    <img src="https://i.ibb.co/JRWpKCcM/s.png" alt="Logo" width="40" height="40"/>
  </p>
</div>
