@echo off
echo ===================================================
echo         KYNUX.DEV DISCORD BOT BAŞLATICI
echo ===================================================
echo.

REM Gerekli dizinleri kontrol et
if not exist "discord-bot" (
  echo Hata: discord-bot klasörü bulunamadı!
  echo Lütfen bu scripti ana proje dizininde çalıştırın.
  pause
  exit /b 1
)

REM Log klasörü varlığını kontrol et, yoksa oluştur
if not exist "discord-bot\logs" (
  echo Log klasörü oluşturuluyor...
  mkdir "discord-bot\logs"
)

REM .env dosyasını kontrol et
if not exist "discord-bot\.env" (
  echo Uyarı: discord-bot\.env dosyası bulunamadı!
  echo discord-bot\.env.example dosyasını discord-bot\.env olarak kopyalayıp düzenleyin.
  copy "discord-bot\.env.example" "discord-bot\.env" > nul 2>&1
  echo Örnek .env dosyası oluşturuldu, lütfen düzenleyin.
  notepad "discord-bot\.env"
  echo .env dosyasını düzenledikten sonra bu scripti tekrar çalıştırın.
  pause
  exit /b 1
)

echo Discord bot servisini başlatmak için:
echo.
echo 1. Bot'u doğrudan çalıştır
echo 2. Bot'u geliştirici modunda çalıştır (nodemon) 
echo 3. Sadece durum güncellemesi yap (tek seferlik)
echo 4. Çıkış
echo.

set /p choice="Seçiminizi yapın (1-4): "

cd discord-bot

if "%choice%"=="1" (
  echo Discord bot başlatılıyor...
  echo Kapatmak için CTRL+C tuşlarına basın.
  npm start
) else if "%choice%"=="2" (
  echo Discord bot geliştirici modunda başlatılıyor...
  echo Kapatmak için CTRL+C tuşlarına basın.
  npm run dev
) else if "%choice%"=="3" (
  echo Discord durumu güncelleniyor...
  npm run update
  pause
) else if "%choice%"=="4" (
  echo Çıkış yapılıyor...
  exit /b 0
) else (
  echo Geçersiz seçim!
  pause
  exit /b 1
)

cd ..
pause
