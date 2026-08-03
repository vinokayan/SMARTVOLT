SMARTVOLT UI V4.1 — REFINEMENT

PERBAIKAN
- Judul topbar dan konten sejajar pada seluruh halaman.
- Teks keterangan, label, dan isi form lebih mudah dibaca.
- Ukuran dan gaya ikon konsisten.
- Mode Teknisi menggunakan grid dua kolom pada desktop.
- Card statistik berwarna lembut; card kerja tetap putih.
- Ruangan: 3 kolom desktop besar, 2 kolom laptop, 1 kolom tablet/HP.

PEMASANGAN
1. Ekstrak ZIP.
2. Buka folder SmartVolt_UI_V4_1_Refinement.
3. Klik dua kali install-smartvolt-ui-v4-1.cmd.

POWERSHELL
Set-ExecutionPolicy -Scope Process Bypass
& "LOKASI_FOLDER\SmartVolt_UI_V4_1_Refinement\install-smartvolt-ui-v4-1.ps1" -ProjectRoot "C:\SMARTVOLT"

SETELAH SELESAI
cd C:\SMARTVOLT
php artisan serve --host=0.0.0.0 --port=8000

Buka http://127.0.0.1:8000 lalu tekan Ctrl + F5.

Backup otomatis disimpan di:
C:\SMARTVOLT\storage\ui-backups\v4-1-TANGGAL-JAM
