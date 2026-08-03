SMARTVOLT UI V4 — PROFESSIONAL, MODERN, RESPONSIVE

HALAMAN YANG DIPERBARUI
- Login, daftar akun, lupa password, reset password
- Beranda
- Pemakaian Listrik
- Ruangan dan Perangkat
- Daftar Perangkat
- Detail Ruangan
- Pengaturan Profil
- Keamanan Akun
- Energi dan Sistem
- Mode Teknisi
- Notifikasi, dialog, status kosong, loading, dan error

CARA PALING MUDAH
1. Ekstrak ZIP.
2. Buka folder SmartVolt_UI_V4_Complete.
3. Klik dua kali install-smartvolt-ui-v4.cmd.
4. Setelah selesai, jalankan dari PowerShell:

   cd C:\SMARTVOLT
   php artisan serve --host=0.0.0.0 --port=8000

5. Buka http://127.0.0.1:8000
6. Tekan Ctrl + F5.

CARA POWERSHELL
Set-ExecutionPolicy -Scope Process Bypass
& "LOKASI_FOLDER\SmartVolt_UI_V4_Complete\install-smartvolt-ui-v4.ps1" -ProjectRoot "C:\SMARTVOLT"

YANG TIDAK DIUBAH
- Controller dan model
- Route
- Database dan migration
- File .env
- API IoT
- MQTT
- Firmware ESP32
- Folder vendor dan node_modules

BACKUP
File lama disimpan otomatis di:
C:\SMARTVOLT\storage\ui-backups\v4-TANGGAL-JAM
