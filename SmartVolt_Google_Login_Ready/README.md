# SmartVolt Google Login

Paket ini menambahkan login Google menggunakan Laravel Socialite.

## Setelah installer selesai

1. Buat OAuth Client tipe Web application di Google Cloud Console.
2. Tambahkan Authorized redirect URI:
   `http://127.0.0.1:8000/auth/google/callback`
3. Isi `.env`:
   - `GOOGLE_CLIENT_ID`
   - `GOOGLE_CLIENT_SECRET`
   - `GOOGLE_REDIRECT_URI`
4. Jalankan `php artisan optimize:clear`.

Login Google tidak dapat digunakan sebelum Client ID dan Client Secret diisi.
