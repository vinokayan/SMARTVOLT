# SMARTVOLT
SMARTVOLT adalah aplikasi web monitoring dan kontrol penggunaan listrik berbasis IoT. Aplikasi ini dibangun menggunakan Laravel, MQTT, dan perangkat ESP32 untuk memantau data listrik seperti tegangan, arus, daya, energi, frekuensi, dan power factor.
SMARTVOLT dirancang agar pengguna dapat memantau pemakaian listrik dan mengontrol perangkat listrik dengan mudah tanpa perlu memahami konfigurasi teknis perangkat IoT. Proses konfigurasi ruangan dan perangkat dilakukan oleh teknisi karena membutuhkan pengaturan kode alat seperti kode relay, kode sensor, dan identitas perangkat ESP.
## Konsep Pengguna
SMARTVOLT memiliki pembagian peran berdasarkan kebutuhan penggunaan sistem.
### User
User adalah pengguna utama aplikasi yang menggunakan SMARTVOLT untuk memantau dan mengontrol perangkat listrik.
User dapat:
- Melihat dashboard monitoring listrik
- Melihat data pemakaian listrik
- Melihat status perangkat
- Melihat daftar ruangan yang sudah dikonfigurasi
- Melihat perangkat yang tersedia di setiap ruangan
- Mengontrol perangkat ON/OFF
- Melihat riwayat penggunaan energi
- Melakukan export data riwayat energi
- Mengubah profil dan password akun
User tidak perlu menambahkan ruangan atau perangkat secara manual karena proses tersebut membutuhkan pemahaman teknis terkait kode alat IoT.
### Teknisi
Teknisi bertugas melakukan konfigurasi awal dan pengelolaan perangkat IoT agar sistem dapat digunakan oleh user.
Teknisi dapat:
- Menambahkan ruangan
- Mengubah data ruangan
- Menghapus ruangan
- Menambahkan perangkat listrik
- Mengubah data perangkat
- Menghapus perangkat
- Mengatur kode relay perangkat
- Mengatur kode sensor ESP
- Mengatur identitas perangkat ESP32
- Menghubungkan perangkat IoT dengan sistem SMARTVOLT
- Mengatur tarif listrik
- Mengatur batas daya
- Mengatur interval refresh data
Pemisahan peran ini dibuat agar user dapat menggunakan sistem secara sederhana, sementara konfigurasi teknis tetap dilakukan oleh teknisi yang memahami perangkat IoT.
## Fitur Utama
- Autentikasi pengguna
  - Login
  - Register
  - Forgot password
  - Reset password
  - Logout
- Dashboard monitoring
  - Total energi hari ini
  - Daya saat ini
  - Jumlah ruangan
  - Jumlah perangkat
  - Jumlah perangkat aktif
  - Grafik daya dan energi
- Monitoring ruangan
  - Menampilkan daftar ruangan yang sudah dikonfigurasi teknisi
  - Menampilkan perangkat berdasarkan ruangan
  - Menampilkan status perangkat pada setiap ruangan
- Kontrol perangkat
  - User dapat menyalakan perangkat
  - User dapat mematikan perangkat
  - Status perangkat dikirim ke ESP32 melalui MQTT
  - Perangkat dikontrol berdasarkan kode relay yang sudah dikonfigurasi teknisi
- Konfigurasi sistem oleh teknisi
  - Menambahkan ruangan
  - Mengubah data ruangan
  - Menghapus ruangan
  - Menambahkan perangkat
  - Mengubah perangkat
  - Menghapus perangkat
  - Mengatur kode relay perangkat
  - Mengatur kode sensor ESP
  - Mengatur identitas perangkat ESP32
  - Mengatur tarif listrik
  - Mengatur batas daya
  - Mengatur interval refresh data
- Monitoring energi
  - Menyimpan data voltage
  - Menyimpan data current
  - Menyimpan data power
  - Menyimpan data energy
  - Menyimpan data frequency
  - Menyimpan data power factor
- Riwayat energi
  - Melihat data penggunaan energi
  - Export data riwayat energi
- Pengaturan akun
  - Update profil pengguna
  - Update password
- Integrasi IoT
  - API untuk menerima data sensor dari ESP32
  - API untuk mengambil command perangkat
  - Kontrol perangkat menggunakan MQTT
  - Dukungan konfigurasi Mosquitto
## Teknologi yang Digunakan
- Laravel 12
- PHP 8.2
- SQLite / Database Laravel
- MQTT
- Mosquitto Broker
- ESP32
- Tailwind CSS
- Vite
- Axios
- DOMPDF
- php-mqtt/client
- php-mqtt/laravel-client
## Alur Kerja Sistem
1. User login ke aplikasi SMARTVOLT.
2. Teknisi melakukan konfigurasi sistem.
3. Teknisi menambahkan ruangan.
4. Teknisi menambahkan perangkat listrik ke ruangan yang sesuai.
5. Teknisi mengatur kode relay, kode sensor ESP, dan identitas perangkat ESP32.
6. ESP32 mengirim data sensor listrik ke API SMARTVOLT.
7. SMARTVOLT menyimpan data sensor ke database.
8. Dashboard menampilkan statistik penggunaan energi, status ruangan, dan status perangkat.
9. User melihat data pemakaian listrik melalui dashboard.
10. User dapat menyalakan atau mematikan perangkat dari aplikasi.
11. Saat perangkat dikontrol, Laravel mengirim perintah melalui MQTT.
12. ESP32 menerima command dari MQTT dan mengontrol relay perangkat.
13. Data penggunaan energi tersimpan dan dapat dilihat melalui halaman riwayat energi.
## Instalasi
Clone repository:
```bash
git clone https://github.com/vinokayan/SMARTVOLT.git
