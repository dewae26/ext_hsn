# HasnurVerif

Sistem verifikasi status data karyawan Hasnur Group. Digunakan oleh Rumah Sakit (RS) mitra untuk mengecek apakah seseorang benar karyawan aktif Hasnur hanya dengan memasukkan **NRP**.

## Fitur

- **Admin** (login via SSO Hasnur Group, whitelist NRP):
  - Kelola data RS mitra (nama, slug/link, nomor WA PIC lebih dari satu + keterangan).
  - Upload / lihat / hapus PKS (PDF).
  - Dashboard grafik pengecekan per RS per hari.
  - Log verifikasi NRP & log login RS (nomor PIC, hasil, feedback) + export Excel.
- **Rumah Sakit** (login via link unik `/rs/{slug}` + nomor WA PIC terdaftar, siap OTP WhatsApp):
  - Cek status karyawan: NRP, Nama, Entitas, PT, KTP, Nominal Kamar/Malam, Status (Valid/Tidak Valid).
  - Data karyawan dibaca langsung (read-only) dari database MHCIS.
  - Mendukung PKS view-only.

## Kebutuhan

- PHP 8.2+, Composer
- MySQL
- Node.js 18+ & npm

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
- Database MHCIS diakses **read-only**; aplikasi tidak menulis/migrasi ke sana.

## Lisensi

Semua aset pihak ketiga yang digunakan berlisensi **permissive/gratis** (tidak ada font berbayar). Font yang dipakai **di-self-host** melalui paket npm sehingga tidak mengambil daya dari server eksternal.

| Komponen | Lisensi |
|---|---|
| Nunito (font) | SIL Open Font License 1.1 |
| Bootstrap, Bootstrap Icons | MIT |
| jQuery | MIT |
| Chart.js | MIT |
| DataTables (datatables.net) | MIT |
| Alpine.js | MIT |
| Popper (`@popperjs/core`) | MIT |
| PDF.js (`pdfjs-dist`) | Apache-2.0 |
| Laravel Framework | MIT |
| Laravel Excel (`maatwebsite/excel`) | MIT |
| PhpSpreadsheet | MIT |

- **Nunito** berlisensi SIL OFL 1.1 → bebas dipakai untuk keperluan komersial, termasuk di-embed/di-self-host.
- Teks lisensi lengkap tiap paket tersedia di direktori `node_modules/<paket>/LICENSE`.
- Logo Hasnur Group merupakan aset brand milik organisasi dan hanya digunakan pada sistem internal ini.
