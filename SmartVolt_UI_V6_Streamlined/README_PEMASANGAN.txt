SMARTVOLT UI V6 - STREAMLINED UX
================================

PERUBAHAN UTAMA
- Banner sapaan besar pada Beranda dihilangkan.
- Status teknis diringkas dan detailnya dapat dibuka saat diperlukan.
- Grafik Beranda hanya fokus pada daya terbaru.
- Grafik Pemakaian Listrik tetap menyediakan analisis daya dan energi.
- Tabel Beranda diringkas menjadi maksimal 5 data.
- Detail ESP Unit ID dan relay dihilangkan dari halaman pengguna umum.
- Ikon berwarna hanya digunakan pada navigasi, ringkasan, dan kategori penting.
- Mode Teknisi dibuat berbentuk accordion dan grid dua kolom.
- Grid ruangan menjadi 3 kolom pada desktop besar, 2 kolom pada laptop, 1 kolom pada tablet/HP.
- Posisi judul dan konten disamakan pada seluruh halaman.

CARA TERMUDAH
1. Ekstrak ZIP.
2. Klik dua kali install-smartvolt-ui-v6.cmd.
3. Setelah selesai, jalankan Laravel.
4. Buka website dan tekan Ctrl + F5.

CARA POWERSHELL
Set-ExecutionPolicy -Scope Process Bypass
& "ALAMAT_FOLDER_HASIL_EKSTRAK\install-smartvolt-ui-v6.ps1" -ProjectRoot "C:\SMARTVOLT"

Setelah pemasangan:
cd C:\SMARTVOLT
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000

File lama dicadangkan otomatis ke:
C:\SMARTVOLT\storage\ui-backups\v6-streamlined-TANGGAL-JAM

Paket ini tidak mengubah:
- .env
- database
- controller
- model
- route
- API IoT
- MQTT
- firmware ESP32
