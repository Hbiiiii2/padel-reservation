# Padel Reservation v2

Padel Reservation v2 adalah aplikasi manajemen reservasi lapangan padel berbasis **Laravel 10** dan **Filament**. Proyek ini menyediakan panel admin untuk staf, lengkap dengan dashboard statistik, kalender interaktif, manajemen member, dan modul reservasi yang terintegrasi.

## Fitur Utama

- **Dashboard Filament** dengan widget statistik, tabel reservasi hari ini, dan kalender FullCalendar (24 jam).
- **Manajemen Reservasi**: CRUD lengkap, penjadwalan, perhitungan harga otomatis, status, serta relasi ke court & member.
- **Manajemen Member**: Registrasi member oleh staf, informasi membership (kode, level, masa berlaku, catatan), relasi ke riwayat reservasi.
- **Kalender Reservasi**: Menggunakan `saade/filament-fullcalendar` untuk menampilkan booking per rentang tanggal yang dipilih user.
- **Seeder Demo**: Menyediakan akun admin dan data reservasi bulan November agar dashboard langsung terisi.

## Prasyarat

- PHP 8.2+
- Composer
- Node.js & npm (opsional, hanya jika ingin build asset tambahan)
- MySQL/MariaDB

## Instalasi

```bash
git clone https://github.com/your-org/padel-reservation-v2.git
cd padel-reservation-v2
composer install
npm install # opsional
```

1. Duplikasi `.env.example` menjadi `.env` dan sesuaikan kredensial database.
2. Generate application key:
   ```bash
   php artisan key:generate
   ```
3. Jalankan migrasi & seeder:
   ```bash
   php artisan migrate
   php artisan db:seed
   # atau jalankan seeder tertentu:
   # php artisan db:seed --class=ReservationDemoSeeder
   ```
4. (Opsional) Build asset frontend:
   ```bash
   npm run dev   # untuk development
   npm run build # untuk production
   ```
5. Jalankan server lokal:
   ```bash
   php artisan serve
   ```

## Akun Demo

Seeder `userSeed` membuat akun admin default:

| Email              | Password  |
| ------------------ | --------- |
| `admin@padel.com`  | `password` |

Seeder `ReservationDemoSeeder` menambahkan:

- 1 member demo (`demo-member@padel.com` / password `password`)
- 1 lapangan aktif “Galaxy Center Court”
- 4 reservasi contoh pada November 2025

## Struktur Penting

- `app/Filament/Pages` – Halaman panel khusus.
- `app/Filament/Resources` – Resource Filament (Member, Reservation, dll).
- `app/Filament/Widgets` – Widget dashboard (statistik, tabel, kalender).
- `database/migrations` – Skema users, courts, reservations, membership fields.
- `database/seeders` – `userSeed` dan `ReservationDemoSeeder`.

## Kalender FullCalendar

Proyek menggunakan paket `saade/filament-fullcalendar` (v3). Penyesuaian:

- Widget `ReservationCalendarWidget` melakukan `fetchEvents` sesuai rentang tanggal yang diminta kalender.
- Plugin dikonfigurasi di `AdminPanelProvider` agar menampilkan waktu dalam format 24 jam (`eventTimeFormat` & `slotLabelFormat`).

Jika melakukan perubahan pada konfigurasi kalender, jalankan `php artisan optimize:clear` agar cache Panel Filament diperbarui.

## Troubleshooting

- **PowerShell menolak menjalankan npm**: jalankan PowerShell sebagai Administrator dan set execution policy `Set-ExecutionPolicy RemoteSigned -Scope CurrentUser`.
- **Kalender tidak menampilkan event**: pastikan sudah menjalankan `ReservationDemoSeeder` atau memiliki data reservasi pada bulan yang aktif.
- **Perubahan tidak muncul di panel**: jalankan `php artisan optimize:clear` untuk membersihkan cache config/route/view/Filament.

## Lisensi

Proyek internal. Silakan sesuaikan bagian ini jika ingin didistribusikan secara publik.

---
Jika ada kebutuhan fitur tambahan (notifikasi, integrasi pembayaran, booking publik, dll.), buka issue atau hubungi maintainer.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

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
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
