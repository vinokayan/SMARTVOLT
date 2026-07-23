# SMARTVOLT

SMARTVOLT adalah aplikasi web berbasis Internet of Things (IoT) yang digunakan untuk memantau pemakaian listrik dan mengontrol perangkat listrik rumah tangga.

Sistem ini mengintegrasikan website Laravel, database MySQL, ESP32, sensor PZEM-004T, relay, REST API, dan MQTT. Sensor PZEM membaca kondisi listrik, sedangkan ESP32 mengirimkan data hasil pembacaan tersebut ke website SMARTVOLT.

Data utama yang dipantau meliputi:

- Tegangan dalam satuan volt (V)
- Arus dalam satuan ampere (A)
- Daya dalam satuan watt (W)
- Energi dalam satuan kilowatt-hour (kWh)
- Frekuensi dalam satuan hertz (Hz)
- Faktor daya atau power factor

Data tegangan, arus, daya, dan energi ditampilkan pada halaman utama dan riwayat pemakaian listrik. Data frekuensi dan faktor daya dapat disimpan melalui telemetry apabila dikirim oleh firmware ESP32 dan didukung oleh struktur database.

SMARTVOLT dirancang agar pengguna dapat memantau dan mengontrol listrik tanpa harus memahami konfigurasi teknis perangkat IoT. Pengaturan ruangan, meter listrik, ESP32, dan relay dilakukan melalui Mode Teknisi karena membutuhkan informasi teknis, seperti ESP Unit ID, meter code, relay code, dan relay channel.

## Tujuan Sistem

SMARTVOLT dikembangkan untuk:

- Membantu pengguna memantau penggunaan listrik rumah tangga
- Menampilkan kondisi listrik berdasarkan data dari sensor PZEM
- Menghitung penggunaan energi berdasarkan periode tertentu
- Menampilkan estimasi pembayaran listrik
- Mengontrol perangkat listrik melalui relay
- Menyimpan riwayat penggunaan listrik
- Mempermudah teknisi dalam mengatur perangkat IoT
- Menyediakan data yang dapat diekspor untuk kebutuhan dokumentasi

## Pembagian Akses

SMARTVOLT memiliki dua jenis penggunaan, yaitu akses pengguna dan Mode Teknisi.

### Pengguna

Pengguna merupakan pemakai utama SMARTVOLT. Pengguna dapat memantau pemakaian listrik dan mengontrol perangkat yang telah dikonfigurasi.

Pengguna dapat:

- Masuk ke dalam aplikasi
- Melihat dashboard monitoring
- Melihat energi yang digunakan hari ini
- Melihat daya listrik saat ini
- Melihat estimasi tagihan bulan berjalan
- Melihat jumlah perangkat yang aktif
- Melihat status koneksi alat
- Melihat daftar ruangan
- Melihat perangkat pada setiap ruangan
- Menyalakan perangkat
- Mematikan perangkat
- Melihat riwayat pemakaian listrik
- Memilih meter ruangan
- Memilih rentang tanggal pemakaian
- Melihat estimasi pembayaran harian, mingguan, dan bulanan
- Mengekspor data ke Excel
- Mengekspor data ke PDF
- Memperbarui profil akun
- Mengubah password
- Keluar dari aplikasi

Pengguna tidak perlu menambahkan ruangan, sensor, atau relay secara langsung karena proses tersebut membutuhkan informasi teknis perangkat IoT.

### Mode Teknisi

Mode Teknisi digunakan untuk melakukan konfigurasi perangkat IoT. Akses ke bagian ini dilindungi menggunakan PIN teknisi.

Melalui Mode Teknisi, teknisi dapat:

- Menambahkan ruangan
- Mengubah informasi ruangan
- Menghapus ruangan
- Menambahkan sensor atau meter listrik
- Mengubah konfigurasi meter listrik
- Mengaktifkan atau menonaktifkan meter
- Menghapus meter listrik
- Menambahkan perangkat relay
- Mengubah informasi perangkat
- Menghapus perangkat
- Mengatur ESP Unit ID
- Mengatur meter code
- Mengatur relay code
- Mengatur relay channel
- Menghubungkan meter dengan ruangan
- Menghubungkan relay dengan ESP32
- Mengatur tarif listrik
- Mengatur batas penggunaan daya
- Memeriksa konfigurasi perangkat IoT

Pemisahan akses ini dibuat agar penggunaan SMARTVOLT tetap sederhana bagi pengguna umum, sedangkan konfigurasi teknis hanya dilakukan oleh pihak yang memahami perangkat IoT.

## Fitur Utama

### 1. Autentikasi

SMARTVOLT menyediakan fitur autentikasi untuk melindungi akses ke dalam aplikasi.

Fitur autentikasi meliputi:

- Login
- Registrasi akun
- Lupa password
- Reset password
- Logout
- Validasi email dan password
- Perlindungan halaman menggunakan session pengguna

### 2. Dashboard Monitoring

Dashboard merupakan halaman utama setelah pengguna berhasil masuk.

Informasi yang ditampilkan meliputi:

- Energi hari ini
- Daya saat ini
- Estimasi tagihan bulan ini
- Jumlah meter aktif
- Jumlah perangkat aktif
- Status koneksi ESP32
- Grafik perubahan daya
- Daftar ruangan
- Daftar perangkat dalam setiap ruangan
- Status perangkat menyala atau mati

Energi hari ini dihitung berdasarkan perubahan nilai energi yang diterima dari sensor. Estimasi tagihan bulan ini dihitung berdasarkan total energi bulan berjalan dikalikan dengan tarif listrik per kWh.

### 3. Monitoring Pemakaian Listrik

Pengguna dapat melihat data pemakaian listrik berdasarkan meter dan rentang tanggal.

Informasi yang tersedia meliputi:

- Daya saat ini
- Daya tertinggi
- Rata-rata daya
- Rata-rata tegangan
- Total pemakaian energi
- Estimasi pembayaran
- Grafik daya
- Grafik energi
- Data terakhir setiap meter ruangan

Pengguna dapat memilih:

- Semua meter ruangan
- Satu meter tertentu
- Tanggal mulai
- Tanggal selesai

Ringkasan, grafik, estimasi pembayaran, dan tabel akan mengikuti filter yang diterapkan.

### 4. Estimasi Pembayaran Listrik

SMARTVOLT menghitung estimasi pembayaran dengan rumus:

```text
Estimasi pembayaran = Pemakaian energi × Tarif listrik per kWh