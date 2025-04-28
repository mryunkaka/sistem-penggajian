# 📊 Sistem Penggajian Otomatis

## 🌟 Tentang Proyek

Proyek ini dibuat oleh **Sayid Adam Kaharianto**, seorang pengembang dari **Kotabaru, Kalimantan Selatan**. Sistem ini dirancang untuk mempermudah proses penggajian karyawan dengan cara yang efisien dan otomatis.

Sistem Penggajian ini adalah solusi modern untuk mengelola absensi dan perhitungan gaji karyawan. Anda hanya perlu mengunggah data absensi bulanan yang ditarik dari mesin fingerprint, dan sistem akan secara otomatis membaca data seperti:

-   **Terlambat**
-   **Ijin**
-   **Sakit**
-   **Cuti**
-   **Alfa**

Setelah data absensi diproses, sistem akan menghitung gaji karyawan berdasarkan parameter yang telah ditentukan. Laporan bulanan dan slip gaji juga akan dihasilkan secara otomatis, sehingga mempermudah proses penggajian.

---

## 🚀 Fitur Utama

-   **Unggah Absensi Bulanan**: Cukup unggah file absensi dari mesin fingerprint.
-   **Pembacaan Data Otomatis**: Sistem mendeteksi terlambat, ijin, sakit, cuti, dan alfa secara otomatis.
-   **Perhitungan Gaji Otomatis**: Hitung gaji berdasarkan aturan perusahaan.
-   **Laporan Bulanan**: Hasilkan laporan bulanan dalam format yang mudah dibaca.
-   **Slip Gaji Otomatis**: Slip gaji untuk setiap karyawan dihasilkan secara otomatis.
-   **Efisiensi Tinggi**: Hemat waktu dan usaha dalam proses penggajian.

---

## 🛠️ Teknologi yang Digunakan

-   **Bahasa Pemrograman**: PHP
-   **Framework**: Laravel 12 + Filament
-   **Database**: MySQL
-   **Tools Tambahan**: Laragon

---

## ⚙️ Proses Instalasi

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan proyek ini di lokal Anda:

1. **Clone Repository**

    ```bash
    git clone https://github.com/username/sistem-penggajian-otomatis.git
    cd sistem-penggajian-otomatis
    ```

2. **Install Dependency PHP**

    ```bash
    composer install
    ```

3. **Copy File Environment**

    ```bash
    cp .env.example .env
    ```

4. **Generate Application Key**

    ```bash
    php artisan key:generate
    ```

5. **Atur Konfigurasi Database**

    - Edit file `.env` dan sesuaikan bagian berikut:
        ```plaintext
        DB_DATABASE=nama_database
        DB_USERNAME=username_database
        DB_PASSWORD=password_database
        ```

6. **Jalankan Migrasi Database**

    ```bash
    php artisan migrate
    ```

7. **(Opsional) Jalankan Seeder**
   Jika ada data awal yang perlu diisi:

    ```bash
    php artisan db:seed
    ```

8. **Build Frontend (Jika Ada menggunakan Vite)**

    ```bash
    npm install
    npm run dev
    ```

9. **Jalankan Server Laravel**

    ```bash
    php artisan serve
    ```

10. **Akses Aplikasi**
    - Buka browser Anda dan akses: [http://localhost:8000](http://localhost:8000)

---

## 📋 Cara Menggunakan

1. **Persiapkan File Absensi**:

    - Tarik data absensi bulanan dari mesin fingerprint.
    - Pastikan format file sesuai dengan yang didukung sistem (misalnya `.xls`, `.xlsx`, atau `.csv`).

2. **Unggah File**:

    - Masuk ke sistem penggajian.
    - Unggah file absensi melalui antarmuka yang tersedia.

3. **Proses Data**:

    - Sistem akan membaca data absensi dan menghitung gaji secara otomatis.
    - Periksa hasil perhitungan untuk memastikan semuanya sesuai.

4. **Hasilkan Laporan**:

    - Unduh laporan bulanan dan slip gaji untuk setiap karyawan.

---

## 📞 Kontak Pengembang

Jika Anda memiliki pertanyaan atau ingin berdiskusi lebih lanjut tentang proyek ini, silakan hubungi saya:

-   **Nama**: Sayid Adam Kaharianto
-   **Asal**: Kotabaru, Kalimantan Selatan
-   **Email**: mryunkaka@gmail.com
-   **LinkedIn**: [LinkedIn Sayid Adam](https://www.linkedin.com/in/saidadam/)

---

## 📜 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE). Anda bebas menggunakan, memodifikasi, dan mendistribusikan proyek ini sesuai dengan ketentuan lisensi.

---

## 💡 Kontribusi

Kontribusi sangat diterima! Jika Anda ingin berkontribusi pada proyek ini, silakan buat pull request atau laporkan masalah melalui halaman **Issues**.

---

> "Membuat pekerjaan lebih mudah, satu langkah menuju efisiensi."

🚀✨
