SMARTVOLT UI V3 — PEMASANGAN

1. Ekstrak folder ini.
2. Buka PowerShell di dalam folder hasil ekstrak.
3. Jalankan:

   Set-ExecutionPolicy -Scope Process Bypass
   .\install-smartvolt-ui-v3.ps1 -ProjectRoot "C:\SMARTVOLT"

4. Jalankan Laravel:

   cd C:\SMARTVOLT
   php artisan serve --host=0.0.0.0 --port=8000

5. Buka http://127.0.0.1:8000 lalu tekan Ctrl + F5.

Yang diperbarui:
- Beranda
- Pemakaian Listrik
- Ruangan & Perangkat
- Daftar Perangkat
- Pengaturan dan Mode Teknisi
- Login, registrasi, lupa/reset kata sandi
- Tampilan desktop, tablet, dan HP

Tidak diubah:
- .env
- database
- firmware ESP32
- API IoT
- MQTT
- controller dan model
