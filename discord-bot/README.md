# Kynux Discord Durum Takip Botu

Bu bot, Discord kullanıcı durumunuzu web sitenizde göstermek için geliştirilmiş profesyonel bir çözümdür. Belirli bir Discord kullanıcısının çevrimiçi durumunu (online, idle, dnd, offline) ve oynadığı oyun/aktivite bilgilerini gerçek zamanlı olarak takip eder ve bu bilgileri JSON formatında kaydeder.

![Discord Bot](https://img.shields.io/badge/Discord-Bot-7289DA?style=for-the-badge&logo=discord&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-v16+-339933?style=for-the-badge&logo=node.js&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-Integration-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 🌟 Özellikler

- **Gerçek Zamanlı Takip**: Discord kullanıcı durumunu anlık olarak izler
- **Kapsamlı Durum Bilgisi**: Çevrimiçi durumu ve oyun aktivitelerini detaylı şekilde kaydeder
- **Web Entegrasyonu**: PHP entegrasyonu ile web sitenizde kullanımı kolay
- **Hibrit İzleme**: Hem Node.js bot hem de PHP API seçenekleriyle esnek kullanım
- **Otomatik Yeniden Bağlanma**: Bağlantı sorunlarında kendini onarır
- **Detaylı Loglar**: Sorun çözümü için kapsamlı günlük kaydı
- **Düşük Kaynak Kullanımı**: Optimize edilmiş performans

## 📋 Gereksinimler

- Node.js (v16 veya üzeri)
- NPM (v7 veya üzeri)
- Discord Bot Token
- Web sitesi için PHP 7.4+ (PHP entegrasyonu için)
- Discord Hesabı ve Sunucu Erişimi

## 🔧 Kurulum

### 1. Discord Bot Oluşturma

1. [Discord Developer Portal](https://discord.com/developers/applications)'ı ziyaret edin
2. "New Application" butonuna tıklayın ve uygulamanıza bir isim verin
3. Sol menüden "Bot" sekmesine tıklayın ve "Add Bot" butonuna basın
4. "Reset Token" butonuyla token'ı oluşturun ve güvenli bir şekilde saklayın
5. Aşağıdaki "Privileged Gateway Intents" izinlerini etkinleştirin:
   - **PRESENCE INTENT**
   - **SERVER MEMBERS INTENT**
   - **MESSAGE CONTENT INTENT**
6. Sol menüden "OAuth2" → "URL Generator" sekmesine gidin
7. Scopes: `bot` ve Bot Permissions: `Read Messages/View Channels` seçin
8. Oluşturulan URL ile botu sunucunuza ekleyin

### 2. Proje Kurulumu

```bash
# Proje dizinine gidin
cd discord-bot

# Bağımlılıkları yükleyin
npm install

# .env dosyasını oluşturun
```

`.env` dosyasını proje kök dizinine oluşturun ve şu bilgileri ekleyin:

```
DISCORD_BOT_TOKEN=BOT_TOKEN_BURAYA
DISCORD_USER_ID=TAKIP_EDILECEK_KULLANICI_ID_BURAYA
```

## 🚀 Kullanım

### Discord Bot'u Başlatma

```bash
# Normal başlatma
npm start

# Geliştirme modunda başlatma (otomatik yeniden başlatma)
npm run dev

# Sadece durum güncelleme (tek seferlik)
npm run update
```

### Web Sitesi Entegrasyonu

1. `discord-api.php` dosyasını web sitenize ekleyin
2. Durum bilgilerini çekmek için:

```php
<?php
$discord_status = json_decode(file_get_contents('logs/discord_status.json'), true);
?>

<!-- Durum gösterimi örneği -->
<div class="discord-status <?php echo $discord_status['status']; ?>">
    <?php echo $discord_status['username']; ?> şu anda <?php echo $discord_status['status']; ?> durumunda
    
    <?php if ($discord_status['has_game']): ?>
        ve <?php echo $discord_status['game']; ?> oynuyor
    <?php endif; ?>
</div>
```

## 📁 Proje Yapısı

```
discord-bot/
│
├── discord-bot.js          # Ana Discord bot kodu
├── start-discord-bot.js    # Bot başlatma yardımcısı
├── discord-api.php         # PHP API entegrasyonu
├── discord-update-cron.php # Zamanlanmış görev scripti
├── discord-help.php        # Web tabanlı yardım sayfası
├── package.json            # Node.js bağımlılıkları
└── logs/                   # Log ve durum dosyaları
    ├── discord_status.json # Kaydedilen durum bilgileri
    └── discord_bot.log     # Bot log dosyası
```

## 📊 Durum JSON Formatı

```json
{
    "status": "online",
    "game": "Minecraft",
    "has_game": true,
    "username": "kullanıcı_adı",
    "discriminator": "0",
    "last_updated": "2025-03-18T16:52:35.123Z",
    "game_details": {
        "type": 0,
        "url": "",
        "state": "Survival Modu",
        "details": "Crafting..."
    }
}
```

## ⚙️ Zamanlanmış Görevler

### Windows (Task Scheduler)

1. Windows Task Scheduler'ı açın
2. "Create Basic Task" seçeneğini tıklayın
3. İsim ve açıklama girin, "Next" tıklayın
4. "Daily" seçin ve "Next" tıklayın
5. Başlangıç saati ayarlayın ve "Recur every: 1 days" seçin, "Next" tıklayın
6. "Start a program" seçin ve "Next" tıklayın
7. Program/script: `C:\xampp\php\php.exe`
8. Argümanlar: `-f C:\xampp\htdocs\discord-bot\discord-update-cron.php`
9. "Finish" tıklayın

### Linux (Cron)

```bash
# Crontab düzenleyicisini açın
crontab -e

# Her dakika çalıştırmak için aşağıdaki satırı ekleyin
* * * * * php /path/to/htdocs/discord-bot/discord-update-cron.php
```

## 🔍 Sorun Giderme

### Bot Bağlantı Sorunları
- ✅ Bot token'ın doğru ve güncel olduğundan emin olun
- ✅ Tüm "Intents" izinlerinin Discord Developer Portal'da etkinleştirildiğini kontrol edin
- ✅ Bot hesabınızın devre dışı bırakılmadığından emin olun
- ✅ logs/discord_bot.log dosyasındaki hata mesajlarını kontrol edin

### Durum Güncellenmiyor
- ✅ Botun takip ettiğiniz kullanıcı ile aynı sunucularda olduğunu doğrulayın
- ✅ Kullanıcı ID'sinin doğru olduğunu kontrol edin
- ✅ Kullanıcının gizlilik ayarlarının durumunu göstermeye izin verdiğinden emin olun

### Web Entegrasyonu Sorunları
- ✅ discord_status.json dosyasının oluşturulduğunu ve doğru formatta olduğunu kontrol edin
- ✅ PHP'nin dosyayı okuma iznine sahip olduğundan emin olun
- ✅ Cron veya zamanlanmış görevin düzgün çalıştığını kontrol edin

## 📄 Lisans

Bu proje BSD 3-Clause "New" or "Revised" lisansı altında lisanslanmıştır. Daha fazla bilgi için [../LICENSE](../LICENSE) dosyasına bakın.

## 🤝 Katkıda Bulunma

Katkılarınızı memnuniyetle karşılıyoruz! Lütfen:

1. Projeyi fork edin
2. Yeni özellik dalı oluşturun (`git checkout -b yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik: özellik açıklaması'`)
4. Dalınızı push edin (`git push origin yeni-ozellik`)
5. Pull Request oluşturun

---

Geliştirici: [KynuxDev](https://github.com/kynux.dev) | © 2025 Tüm hakları saklıdır.
