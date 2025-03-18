# Discord Kullanıcı Durum Takip Botu

Bu bot, belirli bir Discord kullanıcısının çevrimiçi durumunu (online, idle, dnd, offline) ve oynadığı oyun bilgilerini izleyerek JSON formatında kaydetmenizi sağlar.

## Özellikler

- Belirli bir Discord kullanıcısının durumunu gerçek zamanlı takip eder
- Kullanıcının durumunu (online, idle, dnd, offline) algılar
- Kullanıcının oynadığı oyun/aktivite bilgilerini algılar
- Tüm durum bilgilerini JSON formatında kaydeder
- Değişikliklere anında tepki verir
- Otomatik yeniden bağlanma

## Kurulum

1. Node.js ve npm'i [buradan](https://nodejs.org/) (LTS sürümü önerilir) yükleyin
2. Repo içindeki bütün dosyaları indirin
3. Komut satırını açın ve proje dizinine gidin
4. Bağımlılıkları yükleyin:

```bash
npm install
```

## Discord Botunu Oluşturma

1. [Discord Developer Portal](https://discord.com/developers/applications)'a gidin
2. "New Application" butonuna tıklayın
3. Uygulamanıza bir isim verin ve oluşturun
4. Sol menüden "Bot" sekmesine tıklayın
5. "Add Bot" butonuna tıklayın
6. Bot bilgileri altında "Reset Token" butonuna tıklayın ve token'ı kopyalayın
7. "Privileged Gateway Intents" bölümünden aşağıdaki izinleri açın:
   - PRESENCE INTENT
   - SERVER MEMBERS INTENT
8. Sol menüden "OAuth2" sekmesine tıklayın, sonra "URL Generator" alt sekmesine geçin
9. Scope bölümünde "bot" seçeneğini işaretleyin
10. Bot Permissions bölümünde gerekli izinleri seçin:
    - Read Messages/View Channels
11. Oluşturulan URL'yi kopyalayın ve botunuzu sunucunuza ekleyin

## Yapılandırma

`.env` dosyasını oluşturun ve aşağıdaki bilgileri ekleyin:

```
DISCORD_BOT_TOKEN=BOT_TOKEN_BURAYA
DISCORD_USER_ID=IZLENECEK_KULLANICI_ID_BURAYA
```

- `BOT_TOKEN_BURAYA`: Discord Developer Portal'dan aldığınız bot token'ı
- `IZLENECEK_KULLANICI_ID_BURAYA`: Durumunu takip etmek istediğiniz kullanıcının Discord ID'si

## Kullanım

```bash
# Botu başlatmak için:
node discord-bot.js

# Ya da başlangıç yardımcısını kullanmak için:
node start-discord-bot.js
```

Bot çalıştığında:

1. Discord API'ye bağlanır
2. Belirtilen kullanıcının durumunu izlemeye başlar
3. Durum ve oyun bilgilerini `logs/discord_status.json` dosyasına kaydeder
4. Durum değişikliklerini gerçek zamanlı izler ve dosyayı günceller

## Durum JSON Formatı

Bot tarafından oluşturulan JSON dosyası aşağıdaki formattadır:

```json
{
    "status": "online",
    "game": "Minecraft",
    "has_game": true,
    "username": "kullanıcı_adı",
    "discriminator": "0",
    "last_updated": "2025-03-11T00:22:35.123Z",
    "game_details": {
        "type": 0,
        "url": "",
        "state": "Survival Modu",
        "details": "Crafting..."
    }
}
```

- `status`: Kullanıcının durumu: "online", "idle", "dnd" veya "offline"
- `game`: Kullanıcının oynadığı oyun/aktivite adı
- `has_game`: Kullanıcının aktif bir oyun/aktivitesi olup olmadığı
- `username`: Kullanıcının Discord kullanıcı adı
- `discriminator`: Kullanıcının Discord ayrımcısı (discriminator)
- `last_updated`: Son güncelleme zamanı (ISO formatında)
- `game_details`: Aktivite hakkında ek bilgiler (varsa)

## Sorun Giderme

- **Bot bağlanamıyor**: Token'ın doğruluğunu ve `.env` dosyasının formatını kontrol edin
- **Durum güncellenmiyor**: Botun ilgili kullanıcıyla aynı sunucularda olduğundan emin olun
- **Oyun bilgisi alınamıyor**: Discord istemcisinde "Oyun Aktivitesi"nin açık olduğundan emin olun
