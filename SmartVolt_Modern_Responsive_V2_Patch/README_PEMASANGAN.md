# SmartVolt Modern Responsive V2

Paket ini mengganti tampilan SmartVolt menjadi lebih sederhana, modern, profesional, dan responsif tanpa mengubah alur utama IoT.

## File utama yang diperbarui

- Layout, sidebar, topbar, profil, notifikasi, dan navigasi mobile.
- Dashboard berwarna lembut dengan empat kartu ringkasan.
- Status ESP32, PZEM, MQTT, dan data terakhir.
- Grafik daya/energi, kontrol ruangan, riwayat terbaru, dan estimasi biaya.
- Halaman Pemakaian Listrik, Ruangan & Perangkat, Pengaturan, dan Mode Teknisi dibuat selaras.
- Login dan halaman autentikasi tetap menggunakan konsep Calm Energy Control.

## Cara pemasangan paling mudah

1. Tutup Laravel dan Serial Monitor.
2. Cadangkan folder `C:\SMARTVOLT`.
3. Ekstrak ZIP ini.
4. Salin folder `app`, `routes`, `resources`, dan `public` ke `C:\SMARTVOLT`.
5. Pilih **Replace the files in the destination**.
6. Jalankan:

```powershell
cd C:\SMARTVOLT
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000
```

7. Buka:

```text
http://127.0.0.1:8000/login
```

## Pemasangan otomatis

Buka PowerShell pada folder hasil ekstrak, lalu jalankan:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\install-smartvolt-ui.ps1
```

Script membuat backup file lama sebelum menyalin file baru.

## Catatan

- Jangan menimpa `.env`.
- Folder `vendor`, `node_modules`, database, firmware, dan konfigurasi MQTT tidak diubah.
- `chart.umd.min.js` sudah lokal sehingga grafik tidak bergantung pada CDN.
- DashboardController dalam paket ini diperlukan agar data status, grafik, ruangan, dan estimasi sesuai dengan tampilan baru.
