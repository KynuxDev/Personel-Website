# Discord ve Spotify Entegrasyonu

Bu proje, kişisel web sitenizde Discord durumunuzu ve Spotify'da dinlediğiniz müzikleri göstermenizi sağlayan bir PHP entegrasyonudur.

## Özellikler

### Discord Entegrasyonu

- 🔄 Node.js Discord botu yerine tamamen PHP tabanlı çözüm
- 👤 Discord kullanıcı durumunu (çevrimiçi, rahatsız etmeyin, boşta, çevrimdışı) takip etme
- 🎮 Oynadığınız oyunların durumunu görüntüleme
- ⏱️ Zamanlanmış görevler ile otomatik durum güncellemesi

### Spotify Entegrasyonu

- 🎵 Şu anda dinlediğiniz şarkıyı gösterme
- 🎧 Şarkı bilgilerini ve albüm kapağını görüntüleme
- 📊 Şarkı ilerleme durumunu gösterme
- 🔑 Kolay kurulum arayüzü ile hesap bağlama

## Kurulum

### Gereksinimler

- PHP 7.4 veya üstü
- XAMPP, WAMP veya benzeri bir web sunucusu
- curl PHP eklentisi
- Discord Bot Token
- Spotify Developer Hesabı (isteğe bağlı)

### Discord Kurulumu

1. Discord Developer Portal'dan bir bot oluşturun ve token'ı alın
2. `discord-api.php` dosyasında bot token ve kullanıcı ID değerlerinizi ayarlayın:
   ```php
   $discord_bot_token = 'BURAYA_DISCORD_BOT_TOKEN_YAZIN';
   $user_id_to_track = 'BURAYA_DISCORD_KULLANICI_ID_YAZIN';
   ```
3. Düzenli güncellemeler için `discord-update-cron.php` dosyasını zamanlanmış görev olarak ayarlayın:
   - Windows: Task Scheduler ile her dakika `C:\xampp\php\php.exe -f C:\xampp\htdocs\discord-update-cron.php` komutunu çalıştırın
   - Linux: `crontab -e` ile `* * * * * php /path/to/discord-update-cron.php` satırını ekleyin

### Spotify Kurulumu

1. `spotify-setup.php` sayfasını ziyaret edin ve adımları takip edin:
   - Spotify Developer Dashboard'dan bir uygulama oluşturun
   - Client ID ve Client Secret bilgilerini girin
   - "Spotify Hesabını Bağla" butonuyla hesabınızı yetkilendirin

## Dosya Yapısı

### Discord Dosyaları
- `discord-api.php`: Discord API ile iletişim kuran ana PHP dosyası
- `discord-update-cron.php`: Zamanlanmış görev olarak çalıştırılacak güncelleme scripti
- `discord-help.php`: Discord entegrasyonu için yardım sayfası

### Spotify Dosyaları
- `spotify-setup.php`: Spotify hesabınızı bağlamak için kurulum arayüzü
- `spotify-auth.php`: Spotify OAuth yetkilendirme başlatıcı
- `spotify-callback.php`: Spotify OAuth callback handler

### Ortak Dosyalar
- `get-status.php`: Discord ve Spotify durum bilgilerini JSON olarak döndüren API
- `index.php`: Ana web sayfası
- `style.css`: Stil dosyası
- `script.js`: JavaScript dosyası

## Kullanım

1. Web sitenizi ziyaret edin
2. Discord durum kartınız otomatik olarak güncellenecektir
3. Spotify hesabınızı bağladıysanız, dinlediğiniz müzik de görüntülenecektir
4. Durum bilgileri her 10 saniyede bir güncellenir

## Düzenli Güncelleme

Discord bot'u yerine, PHP entegrasyonu zamanlanmış görevler ile çalışır:

1. `discord-update-cron.php` dosyası düzenli olarak (örn. her dakika) çalıştırılır
2. Bu dosya Discord API'sine istek yaparak kullanıcı bilgilerini alır
3. Alınan bilgiler JSON formatında kaydedilir
4. Web sayfası bu dosyadan verileri okuyarak görüntüler

## Sorun Giderme

### Discord Sorunları

- **Durum güncellenmiyor:** Log dosyalarını kontrol edin (`logs/discord_debug.log` ve `logs/cron_log.txt`)
- **API Hataları:** Discord token'in doğru ve geçerli olduğundan emin olun
- **Yetkilendirme Hatası:** Bot'unuzun yeterli izinlere sahip olduğunu kontrol edin

### Spotify Sorunları

- **Bağlantı Hatası:** Spotify Developer Dashboard'da Redirect URI'nin doğru olduğundan emin olun
- **Müzik Bilgisi Görünmüyor:** Spotify'da müzik çalarken "Web'de Paylaş" seçeneğinin açık olduğundan emin olun
- **Token Hatası:** `spotify-setup.php` sayfasından hesabınızı yeniden bağlayın

## Discord Bot vs PHP Entegrasyonu

Bu proje, Node.js tabanlı discord-bot.js yerine PHP kullanmaktadır. Avantajları:

- **Basitlik:** Node.js kurulumu ve npm paketleri gerektirmez
- **Entegrasyon:** Web sitenizle aynı teknoloji yığını
- **Kaynak Kullanımı:** Sürekli çalışan bir bot yerine, periyodik olarak tetiklenen PHP script'i daha az kaynak kullanır
