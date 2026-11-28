@echo off
setlocal
cd /d "%~dp0"

echo [1/2] Menyalakan server lokal PHP (127.0.0.1:8080)...
start /B "" php spark serve --host 127.0.0.1 --port 8080

timeout /t 2 /nobreak >nul

echo [2/2] Menghubungkan ngrok ke https://snooze-study-gravy.ngrok-free.dev ...
echo Tekan Ctrl+C untuk menghentikan server dan ngrok.
ngrok http 8080 --url https://snooze-study-gravy.ngrok-free.dev

