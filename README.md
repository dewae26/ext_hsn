# HasnurVerif

Sistem verifikasi status data karyawan Hasnur Group. Digunakan oleh Rumah Sakit (RS) mitra untuk mengecek apakah seseorang benar karyawan aktif Hasnur hanya dengan memasukkan **NRP**.

## Fitur

- **Admin** (login via SSO Hasnur Group, whitelist NRP):
  - Kelola data RS mitra (nama, slug/link, nomor WA PIC lebih dari satu + keterangan).
  - Upload / lihat / hapus PKS (PDF).
  - Dashboard grafik pengecekan per RS per hari.
  - Log verifikasi NRP & log login RS (nomor PIC, hasil, feedback) + export Excel.
- **Rumah Sakit** (login via link unik `/rs/{slug}` + nomor WA PIC terdaftar, siap OTP WhatsApp):
  - Cek status karyawan: NRP, Nama, Entitas, PT, Status (Aktif/Tidak Aktif).
  - Data karyawan dibaca langsung (read-only) dari database MHCIS.
  - Mendukung PKS view-only.

## Kebutuhan

- PHP 8.2+, Composer
- MySQL
- Node.js 18+ & npm

## Instalasi

```bash
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
```

Sesuaikan `.env`:

```dotenv
APP_URL=http://127.0.0.1:8181

# Database aplikasi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hasnur_verif
DB_USERNAME=root
DB_PASSWORD=

# Database sumber data karyawan (READ-ONLY)
MHICS_DB_HOST=127.0.0.1
MHICS_DB_PORT=3306
MHICS_DB_DATABASE=mhcis
MHICS_DB_USERNAME=root
MHICS_DB_PASSWORD=

# SSO Hasnur Group
SSO_LOGIN_URL=
SSO_APP_ID=
SSO_APP_SECRET=
SSO_CALLBACK_URL=http://127.0.0.1:8181/sso/callback

# OTP RS (WhatsApp menyusul)
OTP_ENABLED=false
```

Migrasi & build aset:

```bash
php artisan migrate
npm install
npm run build
```

Jalankan:

```bash
php artisan serve --host=127.0.0.1 --port=8181
```

- Admin: `http://127.0.0.1:8181/login` (tombol SSO)
- RS: `http://127.0.0.1:8181/rs/{slug}`

## Menambah Admin

Admin ditentukan berdasarkan whitelist NRP. Tambahkan lewat menu **Kelola Admin**, atau seed via `.env`:

```dotenv
SEED_ADMIN_NRP=
SEED_ADMIN_NAME=
SEED_ADMIN_EMAIL=
```

lalu:

```bash
php artisan db:seed
```

## Test

```bash
php artisan test
```

## Catatan

- Folder `public/build` dan `vendor`/`node_modules` tidak disimpan di repo → **wajib** `composer install` dan `npm run build` saat deploy.
- `public/pdf.worker.min.mjs` disimpan di repo untuk mendukung viewer PDF (dibuat ulang otomatis oleh `npm run build`).
- Database MHCIS diakses **read-only**; aplikasi tidak menulis/memigrasi ke sana.
