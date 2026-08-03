# TODO: Perbaikan Sistem Autentikasi SmartVolt

## Status Progres

- [x] Analisis struktur proyek selesai
- [x] Rencana perbaikan disetujui
- [x] **Step 1:** Perbaiki `app/Http/Controllers/AuthController.php`
  - [x] Hapus semua kode corrupt (`NaN`)
  - [x] Tambahkan method `logout()`
  - [x] Ubah min password dari 6 ke 8
  - [x] Perbaiki redirect setelah reset link terkirim
  - [x] Sesuaikan register flow
- [x] **Step 2:** Perbaiki `routes/web.php` (typo `classNaNs`) — sudah benar
- [x] **Step 3:** Perbaiki `resources/views/auth/register.blade.php` (minlength 6 -> 8)
- [x] **Step 4:** Perbaiki `resources/views/auth/reset_password.blade.php` (minlength 6 -> 8)
- [x] **Step 5:** Verifikasi dengan `php artisan route:list` — ✅ Semua route auth berfungsi tanpa error
