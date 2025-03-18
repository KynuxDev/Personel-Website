const { Client, GatewayIntentBits } = require('discord.js');
const fs = require('fs');
const path = require('path');

// .env dosyasından token ve kullanıcı ID bilgisini al
require('dotenv').config();

// Bot yapılandırması
const config = {
    token: process.env.DISCORD_BOT_TOKEN,
    userId: process.env.DISCORD_USER_ID || '1244181502795976775',
    statusFile: path.join(__dirname, 'logs', 'discord_status.json'),
    updateInterval: 30000 // 30 saniye
};

// Discord istemcisi oluştur - intents ayarları önemli
const client = new Client({
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildPresences,
        GatewayIntentBits.GuildMembers
    ]
});

// Logs dizini kontrolü
const logsDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logsDir)) {
    fs.mkdirSync(logsDir, { recursive: true });
    console.log('Logs dizini oluşturuldu:', logsDir);
}

// Debug log fonksiyonu
function logDebug(message) {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] ${message}\n`;
    fs.appendFileSync(path.join(logsDir, 'discord_bot.log'), logMessage);
    console.log(logMessage.trim());
}

// Kullanıcı durum bilgilerini kaydet
function saveStatusInfo(statusData) {
    try {
        fs.writeFileSync(config.statusFile, JSON.stringify(statusData, null, 4));
        logDebug(`Durum bilgileri kaydedildi: ${JSON.stringify(statusData)}`);
        return true;
    } catch (error) {
        logDebug(`Durum kaydedilirken hata: ${error.message}`);
        return false;
    }
}

// Kullanıcı bilgilerini getir
async function fetchUserInfo() {
    try {
        // Kullanıcıyı bul
        const user = await client.users.fetch(config.userId);
        if (!user) {
            logDebug(`Kullanıcı bulunamadı: ${config.userId}`);
            return null;
        }

        // Kullanıcının tüm sunucular üzerindeki presence bilgilerini kontrol et
        const guilds = client.guilds.cache.values();
        let presence = null;
        
        for (const guild of guilds) {
            try {
                const member = await guild.members.fetch(user.id);
                if (member && member.presence) {
                    presence = member.presence;
                    break;
                }
            } catch (err) {
                logDebug(`Sunucu member bilgileri alınamadı: ${guild.name}, Hata: ${err.message}`);
            }
        }

        // Varsayılan durum bilgileri
        const statusData = {
            status: 'offline',
            game: '',
            has_game: false,
            username: user.username,
            discriminator: user.discriminator || '0',
            last_updated: new Date().toISOString()
        };

        // Eğer presence bilgisi varsa, güncelle
        if (presence) {
            statusData.status = presence.status || 'offline';
            
            // Aktivite bilgisini kontrol et (oyun oynuyor mu?)
            const activity = presence.activities && presence.activities.length > 0 
                ? presence.activities[0] 
                : null;
            
            if (activity) {
                statusData.game = activity.name || '';
                statusData.has_game = true;
                statusData.game_details = {
                    type: activity.type,
                    url: activity.url || '',
                    state: activity.state || '',
                    details: activity.details || ''
                };
            }
        }

        return statusData;
    } catch (error) {
        logDebug(`Kullanıcı bilgileri alınırken hata: ${error.message}`);
        return null;
    }
}

// Durum güncellemesi yap
async function updateStatus() {
    try {
        logDebug('Durum güncellemesi başlatılıyor...');
        
        // Kullanıcı bilgilerini al
        const statusData = await fetchUserInfo();
        
        if (!statusData) {
            logDebug('Durum güncellenemedi: Kullanıcı bilgileri alınamadı');
            return;
        }
        
        // Durum bilgilerini kaydet
        if (saveStatusInfo(statusData)) {
            logDebug('Durum başarıyla güncellendi');
        } else {
            logDebug('Durum güncellenirken bir sorun oluştu');
        }
    } catch (error) {
        logDebug(`Durum güncellenirken hata: ${error.message}`);
    }
}

// Bot hazır olduğunda
client.once('ready', () => {
    logDebug(`Bot başlatıldı: ${client.user.tag}`);
    
    // İlk durum güncellemesi
    updateStatus();
    
    // Periyodik durum güncellemesi
    setInterval(updateStatus, config.updateInterval);
});

// Presences değişikliklerini dinle
client.on('presenceUpdate', async (oldPresence, newPresence) => {
    try {
        // İzlediğimiz kullanıcının durumu mu değişti kontrol et
        if (newPresence.userId === config.userId) {
            logDebug(`Kullanıcı durum değişikliği algılandı: ${config.userId}`);
            await updateStatus();
        }
    } catch (error) {
        logDebug(`Durum değişikliği işlenirken hata: ${error.message}`);
    }
});

// Hataları yakala
client.on('error', (error) => {
    logDebug(`Discord bağlantı hatası: ${error.message}`);
});

// Discord'a bağlan
client.login(config.token)
    .then(() => {
        logDebug('Discord\'a başarıyla bağlanıldı');
    })
    .catch((error) => {
        logDebug(`Giriş hatası: ${error.message}`);
    });

// Uygulama kapanırken temiz bir şekilde bağlantıyı kes
process.on('SIGINT', () => {
    logDebug('Bot kapatılıyor...');
    client.destroy();
    process.exit(0);
});

// Process hataları
process.on('unhandledRejection', (error) => {
    logDebug(`İşlenmemiş Promise Hatası: ${error.message}`);
});
