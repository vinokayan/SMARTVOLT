SMARTVOLT UI V8 - UX REFINEMENT

PERBAIKAN UTAMA
- Dashboard hanya menampilkan ruangan yang sudah memiliki perangkat.
- Ruangan kosong tetap tampil pada halaman Ruangan & Perangkat.
- Istilah perangkat diperjelas: total, online, dan menyala tidak dicampur.
- Kartu Dashboard menampilkan perangkat menyala yang benar-benar online.
- Grafik Dashboard diberi judul Daya terbaru, bukan 24 jam, karena data berupa pembacaan terbaru.
- Nilai grafik diberi label Terakhir tercatat agar tidak disamakan dengan Daya saat ini.
- Status Data terlambat memiliki penjelasan saat diarahkan.
- Empty state dipadatkan.
- Ikon sidebar nonaktif dibuat lebih tenang; menu aktif tetap menonjol.
- Nama ruangan ditampilkan dengan kapitalisasi konsisten.
- Controller Dashboard diperbarui untuk menyediakan jumlah perangkat online dan memfilter ruangan kosong dari Dashboard.

CARA MEMASANG
1. Ekstrak ZIP.
2. Klik dua kali install-smartvolt-ui-v8.cmd.
3. Setelah selesai, jalankan:
   cd C:\SMARTVOLT
   php artisan serve --host=0.0.0.0 --port=8000
4. Buka http://127.0.0.1:8000
5. Tekan Ctrl + F5.

ALTERNATIF POWERSHELL
$script = Get-ChildItem "$env:USERPROFILE\Downloads", "C:\SMARTVOLT" -Recurse -Filter "install-smartvolt-ui-v8.ps1" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
Set-ExecutionPolicy -Scope Process Bypass
Unblock-File $script
& $script -ProjectRoot "C:\SMARTVOLT"

Installer mencadangkan file lama ke:
C:\SMARTVOLT\storage\ui-backups\v8-ux-refinement-TANGGAL-JAM
