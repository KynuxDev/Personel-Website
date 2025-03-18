#!/usr/bin/env node

/**
 * Discord Bot Başlatma Programı - Discord kullanıcı durumunu takip eden botu çalıştırır
 */

const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

// Çalışma klasörü
const workingDir = process.cwd();
console.log('Çalışma klasörü:', workingDir);

// Bot dosyası
const botFile = path.join(workingDir, 'discord-bot.js');

// Log klasörü
const logsDir = path.join(workingDir, 'logs');
if (!fs.existsSync(logsDir)) {
    fs.mkdirSync(logsDir, { recursive: true });
    console.log('Logs klasörü oluşturuldu:', logsDir);
}

// .env dosyası kontrolü
const envFile = path.join(workingDir, '.env');
if (!fs.existsSync(envFile)) {
    console.error('.env dosyası bulunamadı!');
    console.log('Lütfen Discord token bilgilerini içeren bir .env dosyası oluşturun:');
    console.log('DISCORD_BOT_TOKEN=your_bot_token_here');
    console.log('DISCORD_USER_ID=your_user_id_here');
    process.exit(1);
}

// Bot process'i başlat
console.log('Discord bot başlatılıyor...');
const botProcess = spawn('node', [botFile], {
    stdio: 'inherit',
    env: process.env
});

// Bot process olayları
botProcess.on('error', (err) => {
    console.error('Bot başlatılırken hata:', err);
});

botProcess.on('close', (code) => {
    console.log(`Bot process ${code} koduyla kapandı`);
});

// Uygulama kapanma olayı
process.on('SIGINT', () => {
    console.log('Program kapatılıyor, bot durduruluyor...');
    botProcess.kill('SIGINT');
    setTimeout(() => {
        process.exit(0);
    }, 1000);
});

// Başarılı başlangıç mesajı
console.log('Discord bot başarıyla başlatıldı!');
console.log('Bot kapatmak için CTRL+C tuşuna basın');
