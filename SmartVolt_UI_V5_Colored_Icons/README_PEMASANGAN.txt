SMARTVOLT UI V5 — COLORED FEATURE ICONS

Perubahan utama:
- Ikon berwarna konsisten pada seluruh menu utama.
- Ikon halaman berwarna pada topbar.
- Ikon statistik, status sistem, histori, ruangan, perangkat, dan pengaturan.
- Ikon ruangan menyesuaikan nama: dapur, kamar, ruang tamu, dan garasi.
- Ikon perangkat menyesuaikan lampu, kipas, atau perangkat umum.
- Mode Teknisi lebih ringkas dengan grid dua kolom pada desktop.
- Grid ruangan menjadi tiga kolom pada desktop besar.
- Tampilan tetap responsif pada desktop, tablet, dan HP.

CARA OTOMATIS
1. Ekstrak ZIP.
2. Klik dua kali install-smartvolt-ui-v5.cmd.
3. Jalankan Laravel dari C:\SMARTVOLT.
4. Tekan Ctrl + F5 pada browser.

CARA POWERSHELL
Set-ExecutionPolicy -Scope Process Bypass
& "C:\LOKASI_HASIL_EKSTRAK\SmartVolt_UI_V5_Colored_Icons\install-smartvolt-ui-v5.ps1" -ProjectRoot "C:\SMARTVOLT"

Script menggunakan lokasi foldernya sendiri melalui $PSScriptRoot, sehingga tidak perlu dijalankan dari C:\SMARTVOLT.

FILE YANG TIDAK DIUBAH
- .env
- controller
- model
- route
- database
- API IoT
- MQTT
- firmware ESP32
- vendor
- node_modules

Backup otomatis disimpan di:
C:\SMARTVOLT\storage\ui-backups\v5-colored-icons-TANGGAL-JAM
