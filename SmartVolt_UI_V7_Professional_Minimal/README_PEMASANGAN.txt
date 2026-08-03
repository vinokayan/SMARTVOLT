SMARTVOLT UI V7 - PROFESSIONAL MINIMAL

Fokus desain:
- Hierarki informasi lebih jelas.
- Warna lebih terkendali dan tidak ramai.
- Ikon tetap berwarna lembut untuk membedakan fitur.
- Kartu utama putih dengan border tipis dan bayangan ringan.
- Form Mode Teknisi menggunakan grid dua kolom pada desktop.
- Grid ruangan: 3 kolom desktop besar, 2 kolom laptop, 1 kolom tablet/HP.
- Ukuran tombol dan input ramah layar sentuh.
- Bottom navigation tersedia pada HP.
- Backend, route, controller, model, database, API IoT, MQTT, dan firmware tidak diubah.

CARA MEMASANG
1. Ekstrak ZIP.
2. Klik dua kali install-smartvolt-ui-v7.cmd.
3. Setelah selesai, jalankan:
   cd C:\SMARTVOLT
   php artisan serve --host=0.0.0.0 --port=8000
4. Buka http://127.0.0.1:8000
5. Tekan Ctrl + F5.

ALTERNATIF POWERSHELL
Set-ExecutionPolicy -Scope Process Bypass
& "ALAMAT_FOLDER\install-smartvolt-ui-v7.ps1" -ProjectRoot "C:\SMARTVOLT"

Installer mencadangkan file lama ke:
C:\SMARTVOLT\storage\ui-backups\v7-professional-minimal-TANGGAL-JAM
