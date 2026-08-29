<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:# ⚙️ Backend UD.NANA

<p align="center">
  <strong>REST API — Sistem Manajemen Inventory dan Penjualan UD.NANA</strong>
</p>

<p align="center">
  Backend API untuk mendukung aplikasi Sistem Manajemen Inventory dan Penjualan Berbasis Web pada Grosir UD.NANA Padang.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/REST%20API-Backend-009688?style=for-the-badge" alt="REST API">
</p>

---

## 📖 Tentang Project

**Backend UD.NANA** merupakan backend dari aplikasi **Sistem Manajemen Inventory dan Penjualan Berbasis Web untuk Grosir UD.NANA Padang**.

Backend dikembangkan menggunakan **Laravel** dan berfungsi sebagai penyedia **REST API** yang digunakan oleh frontend Vue.js.

Backend bertanggung jawab terhadap:

* Pengelolaan data aplikasi.
* Autentikasi pengguna.
* Validasi request.
* Business logic.
* Pengelolaan inventory.
* Pengelolaan transaksi.
* Komunikasi dengan database.
* Penyediaan data dalam format JSON.

Frontend dan backend dikembangkan secara terpisah agar setiap bagian memiliki tanggung jawab yang jelas dan lebih mudah dikembangkan maupun dipelihara.

---

## 🎯 Tujuan

Sistem ini dikembangkan untuk membantu proses operasional UD.NANA dalam mengelola:

* Data barang.
* Kategori barang.
* Stok inventory.
* Barang masuk.
* Barang keluar.
* Data pelanggan.
* Transaksi penjualan.
* Riwayat transaksi.
* Data pengguna.

Dengan sistem berbasis web, proses pengelolaan data dapat dilakukan secara lebih terstruktur dan mengurangi ketergantungan terhadap pencatatan manual.

---

## 🛠️ Tech Stack

| Teknologi           | Penggunaan                      |
| ------------------- | ------------------------------- |
| **Laravel 11**      | Backend framework               |
| **PHP**             | Programming language            |
| **MySQL**           | Database                        |
| **Eloquent ORM**    | Database interaction            |
| **Laravel Sanctum** | Authentication                  |
| **REST API**        | Komunikasi frontend dan backend |
| **Composer**        | Dependency management           |

---

## 🏗️ Arsitektur Sistem

Project menggunakan pendekatan **separated frontend dan backend**.

```text
                         ┌──────────────────────┐
                         │       User           │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │    Vue.js Frontend   │
                         │                      │
                         │ Vue 3 + Vite         │
                         │ Vue Router           │
                         │ Pinia                │
                         │ Axios                │
                         └──────────┬───────────┘
                                    │
                                 HTTP/JSON
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │   Laravel REST API   │
                         │                      │
                         │ Routes               │
                         │ Controllers          │
                         │ Middleware           │
                         │ Models               │
                         │ Validation           │
                         │ Business Logic       │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │        MySQL         │
                         │                      │
                         │ Application Data     │
                         └──────────────────────┘
```

---

## ✨ Fitur API

### 🔐 Authentication

Backend menyediakan API untuk proses autentikasi pengguna.

* Login.
* Logout.
* Session authentication.
* Mendapatkan informasi pengguna yang sedang login.
* Proteksi endpoint menggunakan authentication middleware.

### 👤 User Management

Pengelolaan data pengguna aplikasi.

* Menampilkan pengguna.
* Menambahkan pengguna.
* Mengubah pengguna.
* Menghapus pengguna.
* Pengelolaan role pengguna.

### 📦 Master Data

API untuk mengelola data master yang digunakan dalam sistem.

* Barang.
* Kategori.
* Pelanggan.
* Data pendukung inventory dan transaksi.

### 📥 Inventory — Barang Masuk

API untuk mencatat dan mengelola barang yang masuk ke inventory.

Proses meliputi:

```text
Barang Masuk
     │
     ▼
Detail Barang
     │
     ▼
Validasi
     │
     ▼
Simpan Transaksi
     │
     ▼
Update Stok
```

### 📤 Inventory — Barang Keluar

API untuk mengelola barang yang keluar dari inventory.

Proses meliputi:

```text
Barang Keluar
     │
     ▼
Pilih Barang
     │
     ▼
Validasi Stok
     │
     ▼
Simpan Transaksi
     │
     ▼
Update Stok
```

### 💰 Penjualan

Backend menangani proses transaksi penjualan, termasuk:

* Pembuatan transaksi.
* Detail transaksi.
* Perhitungan total.
* Validasi barang.
* Pengurangan stok.
* Penyimpanan riwayat transaksi.

---

## 📁 Struktur Project

Struktur utama backend Laravel:

```text
backend-udnana/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/
│   │
│   ├── Models/
│   └── ...
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
│
├── resources/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── storage/
│
├── tests/
│
├── .env
├── artisan
├── composer.json
└── README.md
```

---

## 🔄 API Request Flow

Contoh alur ketika frontend mengambil data barang:

```text
Vue Component
      │
      │ Axios GET
      ▼
GET /api/barang
      │
      ▼
Laravel Route
      │
      ▼
Controller
      │
      ▼
Eloquent Model
      │
      ▼
MySQL
      │
      ▼
JSON Response
      │
      ▼
Vue Component
```

---

# ⚙️ Instalasi

## Prerequisites

Pastikan environment sudah memiliki:

* PHP 8.x
* Composer
* MySQL
* Laravel 11
* Node.js & npm untuk menjalankan frontend

---

## 1. Clone Repository

```bash
git clone <repository-url>
```

Masuk ke folder backend:

```bash
cd backend-udnana
```

---

## 2. Install Dependencies

Install dependency Laravel menggunakan Composer:

```bash
composer install
```

---

## 3. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`.

```bash
cp .env.example .env
```

Pada Windows, dapat dilakukan dengan:

```bash
copy .env.example .env
```

Kemudian sesuaikan konfigurasi database:

```env
APP_NAME=UDNANA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=udnana
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nilai database dengan konfigurasi lokal.

---

## 4. Generate Application Key

```bash
php artisan key:generate
```

---

## 5. Menjalankan Migration

Untuk membuat struktur database:

```bash
php artisan migrate
```

Jika ingin menjalankan migration sekaligus seeder:

```bash
php artisan migrate --seed
```

> Gunakan `migrate:fresh --seed` hanya jika ingin menghapus dan membuat ulang seluruh tabel database pada environment development.

---

## 6. Menjalankan Laravel Server

```bash
php artisan serve
```

Backend secara default dapat diakses melalui:

```text
http://127.0.0.1:8000
```

API dapat diakses melalui:

```text
http://127.0.0.1:8000/api
```

---

# 🔐 Authentication

Backend menggunakan authentication untuk membatasi akses terhadap endpoint yang membutuhkan pengguna yang telah login.

Contoh konsep route yang membutuhkan authentication:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Protected API routes
});
```

Frontend kemudian mengakses endpoint tersebut melalui Axios.

```text
Vue.js
   │
   │ Login
   ▼
Laravel API
   │
   ▼
Authentication
   │
   ▼
Session
   │
   ▼
Protected API
```

---

# 🧪 Testing API

API dapat diuji menggunakan tools seperti:

* Postman
* Insomnia
* Thunder Client
* Browser untuk endpoint GET tertentu

Contoh request:

```http
GET /api/barang
```

Contoh response:

```json
{
    "data": [
        {
            "id": 1,
            "nama": "Contoh Barang",
            "stok": 10
        }
    ]
}
```

---

# 🔗 Frontend

Backend ini digunakan bersama frontend:

**Vue.js 3 + Vite**

Contoh konfigurasi URL API pada frontend:

```env
VITE_API_URL=http://127.0.0.1:8000/api
```

Sehingga komunikasi sistem menjadi:

```text
Frontend
Vue.js 3
    │
    │ Axios
    ▼
Backend
Laravel REST API
    │
    ▼
MySQL
```

---

# 📌 Environment Variables

Beberapa environment variable utama:

| Variable        | Deskripsi            |
| --------------- | -------------------- |
| `APP_NAME`      | Nama aplikasi        |
| `APP_ENV`       | Environment aplikasi |
| `APP_DEBUG`     | Mode debugging       |
| `APP_URL`       | URL aplikasi         |
| `DB_CONNECTION` | Database driver      |
| `DB_HOST`       | Host database        |
| `DB_PORT`       | Port database        |
| `DB_DATABASE`   | Nama database        |
| `DB_USERNAME`   | Username database    |
| `DB_PASSWORD`   | Password database    |

> File `.env` tidak boleh di-commit ke repository karena dapat berisi informasi sensitif.

---

# 🗃️ Database

Database menggunakan **MySQL** sebagai media penyimpanan data aplikasi.

Laravel Eloquent ORM digunakan untuk berinteraksi dengan database melalui model.

Struktur database dikelola menggunakan Laravel Migration.

```text
Migration
    │
    ▼
Database Schema
    │
    ▼
Eloquent Model
    │
    ▼
Controller
    │
    ▼
REST API
```

---

# 🚀 Development

Untuk menjalankan backend dalam mode development:

```bash
php artisan serve
```

Jika menggunakan frontend secara bersamaan:

```text
Frontend
http://localhost:5173

Backend
http://127.0.0.1:8000
```

Keduanya berjalan secara terpisah dan berkomunikasi melalui REST API.

---

# 📦 Production Build

Sebelum deployment, pastikan konfigurasi environment telah disesuaikan dengan server production.

Beberapa langkah umum:

```bash
composer install --optimize-autoloader --no-dev

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan:

```env
APP_ENV=production
APP_DEBUG=false
```

> Langkah deployment dapat berbeda tergantung server dan hosting yang digunakan.

---

# 🔒 Security

Beberapa hal yang perlu diperhatikan:

* Jangan commit file `.env`.
* Jangan menyimpan credential database di repository.
* Gunakan `APP_DEBUG=false` pada production.
* Gunakan authentication middleware untuk endpoint yang membutuhkan autentikasi.
* Validasi setiap request dari client.
* Pastikan konfigurasi CORS sesuai dengan environment aplikasi.

---

# 📸 Preview

Screenshot API atau dokumentasi endpoint dapat ditambahkan pada bagian ini jika diperlukan.

Contoh:

```markdown
## 📸 Preview

### API Login

![API Login](./screenshots/api-login.png)

### API Barang

![API Barang](./screenshots/api-barang.png)

### API Transaksi

![API Transaksi](./screenshots/api-transaksi.png)
```

---

# 📚 Dokumentasi

Dokumentasi resmi teknologi yang digunakan:

* Laravel
* PHP
* MySQL
* Laravel Sanctum

Dokumentasi API internal dapat ditambahkan menggunakan Postman Collection atau dokumentasi endpoint apabila project dikembangkan lebih lanjut.

---

# 📄 License

Project ini dikembangkan untuk kebutuhan **Tugas Akhir / Project Akademik** dalam pengembangan Sistem Manajemen Inventory dan Penjualan Berbasis Web untuk UD.NANA Padang.

---

<p align="center">
  <strong>UD.NANA — Sistem Manajemen Inventory dan Penjualan Berbasis Web</strong>
</p>


- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
