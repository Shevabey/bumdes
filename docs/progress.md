# Progress & Development Guide — SIM-BUMDes

**Terakhir diperbarui:** 17 September 2026
**Dokumen ini adalah gabungan** dari status proyek + panduan setup development + alur uji coba, menggantikan seluruh file `SETUP_GUIDE*.md` dan `UJI_COBA_END_TO_END.md` sebelumnya (sudah tidak dipakai lagi). Bersama `PRD.md`, `architecture.md`, `database-schema.dbml`, dan `api-spec.json`, dokumen ini adalah **5 dokumen resmi** proyek.

**Cara pakai:** centang `[x]` tiap item selesai. Update dokumen ini di akhir setiap sesi kerja agar siapa pun (termasuk AI coding agent) tahu persis titik progres tanpa membaca ulang histori diskusi.

---

## 1. Status Ringkas

| Aspek                                                       | Status                                                                             |
| ----------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| Dokumen resmi (PRD, architecture, dbml, api-spec, progress) | 🟢 Seluruhnya sinkron (v2.1/v2.0)                                                  |
| Setup environment development (Laragon)                     | 🟢 Fondasi Laravel, paket, migration & asset selesai                               |
| Migration & Model                                           | 🟡 Region, BUMDes, dan Unit Usaha selesai; entitas domain berikutnya belum dimulai |
| Fitur inti (CRUD, transaksi, tagihan, iuran, referral)      | ⚪ Belum dimulai                                                                   |
| Portal Pengguna                                             | ⚪ Belum dimulai                                                                   |
| Feedback & Notifikasi Real-time (Reverb)                    | ⚪ Belum dimulai                                                                   |
| Ekspor Laporan                                              | ⚪ Belum dimulai                                                                   |
| Uji Coba Internal                                           | ⚪ Belum dimulai                                                                   |
| UAT dengan pengurus BUMDes riil                             | ⚪ Belum dimulai                                                                   |
| Deployment Fase Awal (tanpa Docker)                         | ⚪ Belum dimulai                                                                   |
| Deployment Fase Lanjutan (Docker)                           | ⚪ Belum dimulai (sengaja ditunda)                                                 |

Legenda: ⚪ Belum dimulai · 🟡 Sedang berjalan/sebagian · 🟢 Selesai

---

## 2. Keputusan Penting (Ringkasan Kronologis)

| Tanggal     | Keputusan                                                                                                                                          |
| ----------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| 16 Sep 2026 | BRD v2.0 disepakati: struktur wilayah berjenjang, 5 unit usaha standar, iuran Rp50.000/bulan, referral Rp10.000/berhasil                           |
| 17 Sep 2026 | **FilamentPHP dihapus permanen** — diganti Blade+Livewire custom                                                                                   |
| 17 Sep 2026 | **Docker tidak dipakai di tahap awal** — dev via Laragon, deploy awal non-container, Docker jadi fase lanjutan opsional                            |
| 17 Sep 2026 | Batas verifikasi aktivitas referral: **15 hari** (final), masa berlaku kode referral **5 hari**                                                    |
| 17 Sep 2026 | Notifikasi upgrade ke **Laravel Reverb** (WebSocket real-time) sejak Fase 1                                                                        |
| 17 Sep 2026 | Skema wilayah mengikuti **kode resmi Kemendagri** + kolom `jenis_wilayah` & `nama_lengkap`                                                         |
| 17 Sep 2026 | Untung-rugi dihitung di **2 level**: per unit dan per BUMDes (agregasi)                                                                            |
| 17 Sep 2026 | Login pakai **username + password** (bukan email); reset password manual oleh Super Admin/Admin BUMDes                                             |
| 17 Sep 2026 | Iuran: Bendahara bayar transfer/tunai, **wajib kirim bukti ke Admin BUMDes Koordinator** untuk diverifikasi                                        |
| 17 Sep 2026 | Super Admin berwenang membuat **akun jenis apa pun** (termasuk Pelanggan), tidak terbatas hanya Admin BUMDes                                       |
| 17 Sep 2026 | Payment gateway **ditunda** (tidak ada anggaran Fase 1)                                                                                            |
| 17 Sep 2026 | Dokumentasi disederhanakan jadi 5 file: PRD, architecture, progress (ini), dbml, api-spec — SRS & seluruh setup guide/UAT digabung ke PRD/progress |

---

## 3. Isu Terbuka

| #   | Isu                                                       | Menunggu                                              | Dampak Jika Belum Selesai                                                                               |
| --- | --------------------------------------------------------- | ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| 1   | Field detail pencatatan transaksi tiap 5 jenis unit usaha | Masukan langsung pengurus BUMDes (wawancara/FGD)      | Modul transaksi tetap bisa dibangun (skema `skema_field` JSON fleksibel), field final diisi setelah FGD |
| 2   | Format pasti kolom pada laporan Excel per jenis laporan   | Diskusi lanjutan / contoh format dari pengurus BUMDes | Export dasar dibangun dulu dengan kolom standar (lihat Bab 6.6), disesuaikan setelah masukan            |

---

## 4. Panduan Setup Development (Windows + Laragon, Tanpa Docker)

> Development dikerjakan di Windows dengan **Laragon**. Docker **tidak dipakai di tahap ini** — lihat Bab 8 untuk kapan Docker baru relevan.

### 4.1 Prasyarat

- Laragon Full (PHP 8.2+, Composer, MySQL, Node.js bundel)
- Nyalakan Laragon → **Start All**

### 4.2 Inisialisasi Project

```powershell
cd C:\laragon\www
composer create-project laravel/laravel sim-bumdes "11.*"
cd sim-bumdes
```

Akses otomatis lewat `http://sim-bumdes.test` (virtual host Laragon).

### 4.3 Database

Buat database `sim_bumdes` via HeidiSQL (bundel Laragon, collation `utf8mb4_unicode_ci`). Konfigurasi `.env`:

```env
APP_NAME="SIM-BUMDes"
APP_URL=http://sim-bumdes.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=bumdes
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=simbumdes
REVERB_APP_KEY=simbumdeskey
REVERB_APP_SECRET=simbumdessecret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
```

```powershell
php artisan key:generate
php artisan migrate
php artisan storage:link
```

### 4.4 Package Wajib (Sesuai PRD Bab 11.1 — Tanpa Filament)

```powershell
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"

composer require maatwebsite/excel

composer require laravel/reverb
php artisan reverb:install

php artisan migrate
```

Jalankan Reverb server secara terpisah saat development:

```powershell
php artisan reverb:start
```

### 4.5 Asset Frontend (Tailwind + Livewire + Echo)

```powershell
npm install
npm install --save-dev laravel-echo pusher-js
npm run build
```

Konfigurasi `resources/js/echo.js` untuk konsumsi channel Reverb dari Livewire (dipakai komponen `NotifikasiBadge`, lihat `architecture.md` Bab 3 & 9).

### 4.6 Struktur Folder Modul

```powershell
mkdir app\Services app\Http\Livewire app\Notifications app\Exports app\Policies
```

Ikuti struktur lengkap di `architecture.md` Bab 3.

### 4.7 Checklist Verifikasi Setup

- [ ] `http://sim-bumdes.test` tampil
- [ ] `php artisan migrate:status` tanpa error
- [ ] Tabel `roles`, `permissions`, `activity_log` ada di database
- [ ] `php artisan reverb:start` berjalan tanpa error
- [ ] `npm run build` sukses, folder `public/build` ada

**Commit (Conventional Commits):**

```
chore: inisialisasi project Laravel 11 + package RBAC, activity log, excel, reverb
```

---

## 5. Migration, Model, dan Seeder

### 5.1 Urutan Migration (13 Tabel, sesuai `database-schema.dbml`)

```powershell
php artisan make:migration create_region_table
php artisan make:migration create_bumdes_table
php artisan make:migration create_unit_usaha_table
php artisan make:migration create_akun_table
php artisan make:migration create_pelanggan_table
php artisan make:migration create_transaksi_table
php artisan make:migration create_tagihan_table
php artisan make:migration create_iuran_bumdes_table
php artisan make:migration create_kas_bumdes_table
php artisan make:migration create_kas_mutasi_table
php artisan make:migration create_referral_table
php artisan make:migration create_feedback_table
```

(`activity_log` sudah otomatis dari package Bab 4.4.)

**Isi tiap migration mengikuti persis kolom & tipe data di `database-schema.dbml`** — import file itu ke dbdiagram.io untuk referensi visual saat menulis `Schema::create()`. Contoh migration `region` (skema Kemendagri, lihat `architecture.md` Bab 7):

```php
Schema::create('region', function (Blueprint $table) {
    $table->string('id_region', 20)->primary();
    $table->enum('jenis_wilayah', ['provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa']);
    $table->string('nama_lengkap', 150);
    $table->string('parent_id', 20)->nullable();
    $table->boolean('is_koordinator')->default(false);
    $table->timestamps();
    $table->foreign('parent_id')->references('id_region')->on('region')->nullOnDelete();
});
```

### 5.2 Model Eloquent

```powershell
php artisan make:model Region
php artisan make:model Bumdes
php artisan make:model UnitUsaha
php artisan make:model Akun
php artisan make:model Pelanggan
php artisan make:model Transaksi
php artisan make:model Tagihan
php artisan make:model IuranBumdes
php artisan make:model KasBumdes
php artisan make:model KasMutasi
php artisan make:model Referral
php artisan make:model Feedback
```

Seluruh model (kecuali `KasMutasi`) memakai primary key string custom:

```php
protected $primaryKey = 'id_bumdes'; // sesuaikan per model
public $incrementing = false;
protected $keyType = 'string';
```

### 5.3 Seeder Wilayah & Role

`RegionSeeder` mengisi hierarki Kemendagri untuk Kec. Minggir (format `34.04.07.2005`, dst — lihat `architecture.md` Bab 7 untuk contoh lengkap kode & nama). `RoleSeeder` mendaftarkan 9 role via `spatie/laravel-permission`:

```php
foreach (['super_admin','pengawas','penasihat','direktur','admin_bumdes','sekretaris','bendahara','admin_unit','pengguna'] as $role) {
    Role::firstOrCreate(['name' => $role]);
}
```

```powershell
php artisan migrate:fresh --seed
```

**Checklist:** tabel `region` berisi hierarki provinsi→kelurahan Kec. Minggir dengan kode Kemendagri benar; tabel `roles` berisi 9 baris.

**Checkpoint 1 (22 September 2026):** migration `region`, model `Region` dengan relasi `parent`/`children`, dan `RegionSeeder` untuk DI Yogyakarta → Kabupaten Sleman → Kecamatan Minggir → Sendangrejo/Sendangsari selesai. Sendangsari ditandai sebagai koordinator. `RegionTest` lulus 1 test/5 assertions; seeder sudah diuji dua kali tanpa duplikasi. Migration dijalankan tanpa `--force`.

**Checkpoint 2 (22 September 2026):** migration `bumdes`, model `Bumdes` dengan relasi `kelurahan`, dan `BumdesSeeder` untuk dua BUMDes fase-1 (`BMD-SDS-001` dan `BMD-SDR-001`) selesai. Foreign key `id_kelurahan` menjaga relasi ke tabel `region`; seeder sudah diuji idempotent. Diagnostics dan `php -l` seluruh file checkpoint bersih; seluruh test lulus 4 test/13 assertions. Migration dijalankan tanpa `--force`.

**Checkpoint 3 (22 September 2026):** migration `unit_usaha`, model `UnitUsaha` dengan relasi `bumdes`, dan relasi `Bumdes::units()` selesai. `UnitUsahaSeeder` mengisi lima jenis standar (`pamdes`, `peternakan`, `mitra_tani`, `sewa_mobil`, `sampah`) untuk masing-masing BUMDes fase-1, total 10 unit. `skema_field` memakai JSON array kosong sementara detail field masih menunggu FGD sesuai isu terbuka PRD. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 5 test/19 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah migration model dan seeder bumdes
```

**Commit:**

```
feat(database): tambah migration model dan seeder unit usaha
```

**Checkpoint 4 (22 September 2026):** migration `akun`, model `Akun` sebagai authenticatable berbasis `username` dan `password_hash`, konfigurasi provider auth diarahkan ke `App\Models\Akun`, serta relasi ke BUMDes dan unit selesai. `RoleSeeder` mendaftarkan 9 role Spatie; `AkunSeeder` mengisi 10 akun uji dengan password development `password`. Pivot Spatie disesuaikan agar `model_id` memakai string maksimal 30 karakter sesuai primary key `AKN-*`. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 6 test/26 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(auth): tambah akun dan rbac sembilan role
```

**Checkpoint 5 (22 September 2026):** migration `pelanggan`, model `Pelanggan`, relasi pelanggan ke `UnitUsaha` dan `Akun`, serta `PelangganSeeder` selesai. Seeder membuat satu pelanggan per unit fase-1, total 10 pelanggan; akun portal `pengguna.sds` terhubung eksplisit ke pelanggan PAMDes Sendangsari. Format ID pelanggan mengikuti contoh arsitektur dan kolom memakai panjang 40 karena contoh tersebut melebihi 30 karakter di DBML. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 7 test/32 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah pelanggan dan relasi portal
```

**Checkpoint 6 (22 September 2026):** migration `transaksi`, model `Transaksi`, relasi transaksi ke `UnitUsaha` dan akun pencatat, serta `TransaksiSeeder` selesai. Seeder membuat input dan output untuk 10 unit, total 20 transaksi; `detail` memakai JSON fleksibel sesuai isu FGD dan ID seed dibuat deterministik untuk idempotensi. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 8 test/39 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah transaksi dan relasi pencatat
```

**Checkpoint 7 (22 September 2026):** migration `tagihan`, model `Tagihan`, relasi ke `Pelanggan`, `UnitUsaha`, dan akun verifikator, serta `TagihanSeeder` selesai. Seeder membuat 10 tagihan dengan variasi 1 lunas tunai, 1 menunggu verifikasi transfer, dan 8 belum bayar. Kolom `id_pelanggan` memakai panjang 40 agar konsisten dengan ID pelanggan aktual. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 9 test/47 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah tagihan dan status pembayaran
```

**Checkpoint 8 (22 September 2026):** migration `iuran_bumdes`, model `IuranBumdes`, relasi ke BUMDes dan akun verifikator, serta `IuranBumdesSeeder` selesai. Seeder membuat iuran bulan `2026-09` untuk dua BUMDes dengan total Rp100.000: satu lunas dari kas secara tunai dan satu menunggu verifikasi dari luar kas melalui transfer dengan bukti pembayaran. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 10 test/57 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah iuran bumdes dan verifikasi koordinator
```

**Checkpoint 9 (22 September 2026):** migration `kas_bumdes` dan `kas_mutasi`, model `KasBumdes`/`KasMutasi`, relasi ke BUMDes, serta `KasSeeder` selesai. Seeder membuat 2 kas dengan total saldo Rp400.000 dan 3 mutasi deterministik, termasuk mutasi keluar sumber iuran. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 11 test/65 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah kas bumdes dan mutasi kas
```

**Checkpoint 10 (23 September 2026):** migration `referral`, model `Referral`, relasi BUMDes pengaju/penerima, dan `ReferralSeeder` selesai. Seeder mencakup 4 state (`aktif`, `pending`, `cair`, `gagal`), masa berlaku kode 5 hari, tanggal redeem, batas verifikasi 15 hari, dan tanggal pencairan. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 12 test/74 assertions. Migration dijalankan tanpa `--force` setelah MySQL Laragon diaktifkan.

**Commit:**

```
feat(database): tambah referral dan state verifikasi
```

**Checkpoint 11 (23 September 2026):** migration `feedback`, model `Feedback`, relasi pengirim ke akun serta target ke BUMDes/unit, dan `FeedbackSeeder` selesai. Seeder membuat 3 feedback dengan status tindak lanjut `belum`, `sedang`, dan `selesai`, termasuk target level BUMDes dan unit. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 13 test/82 assertions. Migration dijalankan tanpa `--force`.

**Commit:**

```
feat(database): tambah feedback dan status tindak lanjut
```

Dokumen handoff agent tersedia di `docs/AI_AGENT_HANDOFF.md`, berisi sumber acuan, pola checkpoint, aturan import/type hint, validasi, larangan command destruktif, urutan backend lalu frontend, dan format summary.

**Checkpoint 12 (23 September 2026):** `ReportService` selesai untuk agregasi input, output, dan untung-rugi per unit serta per BUMDes. Filter periode `mingguan` dan `bulanan` tersedia; periode tidak dikenal ditolak dengan `InvalidArgumentException`. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 15 test/90 assertions. Runtime report BUMDes Sendangsari pada fixture bulanan menghasilkan input Rp500.000, output Rp200.000, untung-rugi Rp300.000.

**Commit:**

```
feat(report): tambah agregasi untung rugi per unit dan bumdes
```

**Checkpoint 13 (23 September 2026):** `IuranService::generateBulanan()` dan command `iuran:generate-bulanan` selesai. Generator hanya memproses BUMDes aktif, membuat iuran Rp50.000 secara idempotent, dan tidak menimpa record yang sudah ada. Scheduler Laravel terdaftar setiap tanggal 1 pukul 00:05 melalui `bootstrap/app.php`; opsi `--bulan=YYYY-MM` tersedia untuk eksekusi deterministik. Diagnostics, `php -l`, dan Laravel Pint bersih; `schedule:list` menampilkan jadwal; seluruh test lulus 17 test/99 assertions. Runtime command membuat 2 iuran untuk periode `2026-12`.

**Commit:**

```
feat(console): tambah generator iuran bulanan idempotent
```

**Checkpoint 14 (23 September 2026):** command `referral:expire-check` dan `referral:verify-check` selesai. Command expire menandai kode aktif lewat 5 hari sebagai `kedaluwarsa` dan menerbitkan kode aktif pengganti; command verify memproses pending lewat 15 hari, mencairkan Rp10.000 ke kas penerima bila ada transaksi aktivitas, atau menandai `gagal` bila tidak ada aktivitas. Scheduler terdaftar hourly dan daily bersama scheduler iuran. Diagnostics, `php -l`, dan Laravel Pint bersih; `schedule:list` menampilkan 3 jadwal; seluruh test lulus 19 test/109 assertions.

**Commit:**

```
feat(console): tambah automation referral dan pencairan kas
```

**Checkpoint 15 (23 September 2026):** fondasi autentikasi username selesai. `AuthController` menangani login/logout berbasis `username` + `password`, `EnsureAccountActive` menolak akun nonaktif setelah autentikasi, `RateLimiter::for('login')` membatasi 5 request per menit berdasarkan IP+username, dan route panel/login/logout tersedia. Halaman login Blade juga dirender dan diuji. Diagnostics, `php -l`, dan Laravel Pint bersih; seluruh test lulus 23 test/133 assertions.

**Commit:**

```
feat(auth): tambah login username dan middleware akun aktif
```

**Checkpoint berikutnya:** konfigurasi RBAC route group dan policy ownership untuk panel internal, dimulai dari akses BUMDes/unit sesuai role.

**Commit:**

```
feat(database): tambah migration 12 entitas, model, dan seeder wilayah+role
```

---

## 6. Business Logic & Fitur Utama

### 6.1 RBAC — Policy per Model

Lihat contoh lengkap `UnitUsahaPolicy` di `architecture.md` Bab 6.2. Buat policy serupa untuk `Bumdes`, `Transaksi`, `Tagihan`, `Referral`, `Akun` (kewenangan buat akun sesuai tabel Bab 6.4 `architecture.md`).

### 6.2 Service Layer

**`ReferralService`** — state machine lengkap (generate kode 5 hari, redeem, cek aktivitas minimal, pencairan kas 15 hari). **`IuranService`** — generate tagihan bulanan otomatis, proses bayar (transfer/tunai + kirim bukti ke koordinator), verifikasi oleh koordinator. **`TagihanService`** — verifikasi/tolak bukti transfer. **`ReportService`** — agregasi untung-rugi per unit dan per BUMDes (kode contoh lengkap ada di `architecture.md` Bab 8).

Ringkasan alur referral (detail lengkap di PRD Bab 7.5):

```
generate kode (5 hari) -> redeem -> pending -> [15 hari: syarat aktivitas terpenuhi?]
   -> ya: kas +Rp10.000 (cair) | tidak: gagal
```

Ringkasan alur iuran (PRD Bab 7.4 — sudah update dengan metode & bukti):

```
generate otomatis (tgl 1) -> Bendahara bayar (kas/luar kas, transfer/tunai)
   -> kirim bukti ke Admin BUMDes Koordinator -> verifikasi -> lunas
```

### 6.3 Scheduled Command (`routes/console.php`)

| Command                  | Jadwal             | Fungsi                                                                                  |
| ------------------------ | ------------------ | --------------------------------------------------------------------------------------- |
| `iuran:generate-bulanan` | Tanggal 1, `00:05` | Generate tagihan iuran untuk seluruh BUMDes aktif                                       |
| `referral:expire-check`  | Tiap jam           | Tandai kode referral lewat 5 hari jadi kedaluwarsa, generate kode baru                  |
| `referral:verify-check`  | Harian             | Cek referral pending, cairkan jika syarat aktivitas terpenuhi dalam 15 hari, atau gagal |

**Commit:**

```
feat(services): tambah service layer referral, iuran, tagihan, dan report
feat(console): tambah scheduled command iuran dan referral
```

### 6.4 Panel Internal (Livewire, Bukan Filament)

Bangun komponen `Shared\DataTable` reusable dulu (search + filter + pagination, lihat `architecture.md` Bab 5), baru komponen per modul: `RegionManager`, `BumdesManager`, `AkunManager`, `UnitManager`, `PelangganManager`, `TransaksiForm`, `TagihanManager`, `IuranPanel`, `ReferralPanel`, `DashboardNasional`, `FeedbackForm/List`, `LogAktivitasViewer`. Struktur route per role ada di `architecture.md` Bab 6.1.

**Commit:**

```
feat(panel): tambah komponen DataTable reusable dan manager CRUD 10 modul
```

### 6.5 Portal Pengguna & Notifikasi Real-time

Komponen `CekTagihan` dan `UploadBukti` (dengan `wire:loading` untuk toleransi koneksi lambat). Notifikasi feedback/tagihan memakai **Laravel Reverb** — broadcast ke channel privat per BUMDes (`routes/channels.php`), diterima real-time oleh `NotifikasiBadge` via Laravel Echo (bukan lagi polling).

**Commit:**

```
feat(portal): tambah portal pengguna cek tagihan dan upload bukti transfer
feat(notifikasi): tambah broadcast real-time via Laravel Reverb
```

### 6.6 Ekspor Laporan Excel

`UnitTransaksiExport` dan `BumdesLaporanExport` (agregasi per BUMDes) mendukung parameter periode (`mingguan`/`bulanan`) sesuai `api-spec.json` endpoint `/reports/*/export`.

**Commit:**

```
feat(reports): tambah ekspor laporan Excel per unit dan per BUMDes dengan filter periode
```

---

## 7. Alur Uji Coba (Ringkasan — 9 Role, Sesuai PRD Bab 7)

Uji coba internal dilakukan berurutan, tiap langkah menghasilkan data untuk langkah berikutnya:

1. **Super Admin** — buat hierarki Provinsi→Kota→Kecamatan→BUMDes (2 BUMDes: Sendangsari & Sendangrejo), buat akun Admin BUMDes untuk keduanya, **plus contoh buat langsung 1 akun Sekretaris & 1 akun Pelanggan** (uji kewenangan penuh Super Admin).
2. **Admin BUMDes** — tambah 5 unit usaha, buat akun Sekretaris/Bendahara/Admin Unit (1 unit = 1 akun).
3. **Sekretaris** — kelola pelanggan, input tagihan, catat pembayaran tunai, kirim pengumuman.
4. **Admin Unit** — catat transaksi input/output, buat tagihan, verifikasi/tolak bukti transfer pelanggan.
5. **Pengguna/Pelanggan** — cek tagihan, upload bukti transfer (uji validasi file).
6. **Bendahara** — CRUD transaksi & tagihan lintas unit, **bayar iuran (transfer/tunai) + kirim bukti ke koordinator**, ekspor laporan Excel (uji filter mingguan/bulanan).
7. **Referral lintas BUMDes** — generate kode (Sendangsari) → redeem (Sendangrejo) → transaksi aktivitas → `referral:verify-check` → kas cair; uji juga skenario gagal (syarat tidak terpenuhi dalam 15 hari).
8. **Iuran bulanan** — `iuran:generate-bulanan` → Bendahara bayar → kirim bukti → Admin BUMDes Koordinator verifikasi.
9. **Pengawas/Penasihat/Direktur** — buka dashboard nasional (cek untung-rugi **per unit dan per BUMDes** tampil), kirim feedback ke BUMDes/unit, **cek notifikasi diterima real-time via Reverb** (bukan delay polling).
10. **Admin BUMDes** — terima notifikasi, tindak lanjuti status feedback (belum→sedang→selesai).
11. **Super Admin** — uji toggle aktif/nonaktif berjenjang (BUMDes→unit→pelanggan), cek log aktivitas per akun.

**Akun uji coba (password default `password`, wajib diganti sebelum produksi):**

| Role                        | Username                                                 |
| --------------------------- | -------------------------------------------------------- |
| Super Admin                 | superadmin                                               |
| Admin BUMDes                | admin.sendangsari, admin.sendangrejo                     |
| Sekretaris                  | sekretaris.sds                                           |
| Bendahara                   | bendahara.sds                                            |
| Admin Unit                  | admin.pamdes.sds (dst per unit)                          |
| Pengawas/Penasihat/Direktur | pengawas1-3, penasihat1, direktur1                       |
| Pengguna                    | (dikaitkan ke data Pelanggan yang dibuat di langkah 1/3) |

**Traceability:** seluruh 36 FR di PRD/architecture tercakup dalam 11 langkah di atas — jika semua lolos, lanjut ke FGD & UAT dengan pengurus BUMDes riil (lihat Bab 3, isu terbuka #1).

---

## 8. Deployment

### 8.1 Fase Awal (Sekarang Direncanakan, Tanpa Docker)

VPS Ubuntu 22.04 + Nginx + PHP-FPM + MySQL terinstal langsung, Supervisor untuk `queue:work` & `reverb:start`, cron untuk `schedule:run`. Detail lengkap ada di `architecture.md` Bab 10.2.

### 8.2 Fase Lanjutan (Docker — Belakangan)

**Belum dikerjakan.** Docker baru disusun setelah deployment Fase 8.1 terbukti stabil di produksi. Saat waktunya tiba, buat ulang `docker-compose.yml` (service: app, webserver, db, queue-worker, reverb, scheduler) mengikuti struktur final aplikasi (tanpa Filament) — jangan pakai konfigurasi Docker versi lama yang sempat dibuat sebelum keputusan "tanpa Filament" ini.

---

## 9. Checklist Menyeluruh (Rekap Semua Fase)

- [x] Bab 4 — Setup Laragon, project Laravel, database, package (RBAC, activity log, excel, **Reverb**), asset frontend _(PHP 8.3, database `bumdes`, migration dasar + package, Reverb config, Echo/Pusher, storage link, dan `npm run build` tervalidasi)_
- [ ] Bab 5 — Migration 12 tabel (skema Kemendagri untuk region), Model, Seeder
- [ ] Bab 6.1-6.2 — Policy RBAC, Service Layer (Referral, Iuran, Tagihan, Report)
- [ ] Bab 6.3 — Scheduled Command (iuran, referral expire/verify)
- [ ] Bab 6.4 — Komponen `DataTable` + 10 modul manager Livewire (pengganti Filament)
- [ ] Bab 6.5 — Portal Pengguna + notifikasi real-time via Reverb
- [ ] Bab 6.6 — Ekspor Excel dengan filter periode
- [ ] Bab 7 — Uji coba internal 11 langkah, seluruh 36 FR tercakup
- [ ] FGD dengan pengurus BUMDes (isu terbuka #1 & #2)
- [ ] UAT dengan pengurus BUMDes riil
- [ ] Bab 8.1 — Deployment fase awal (tanpa Docker)
- [ ] Bab 8.2 — Deployment fase lanjutan (Docker, opsional/belakangan)

---

## 10. Riwayat Update Dokumen Ini

| Tanggal                    | Perubahan                                                                                                                                                                                                                                                                                                         |
| -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 17 September 2026          | Dibuat pertama kali sebagai pelacak status terpisah dari setup guide                                                                                                                                                                                                                                              |
| 17 September 2026 (revisi) | **Digabung total** dengan isi `SETUP_GUIDE.md`, `SETUP_GUIDE_LANJUTAN.md`, `SETUP_GUIDE_3.md`, dan `UJI_COBA_END_TO_END.md` menjadi satu dokumen; keempat file tersebut **tidak dipakai lagi**. Proyek kini hanya memakai 5 dokumen resmi: PRD, architecture, progress (ini), database-schema.dbml, api-spec.json |
| 22 September 2026          | Dependensi fondasi terpasang: Livewire 3.8.9, Spatie Permission 6.25, Activitylog 4.12, Laravel Excel 3.1, dan Reverb 1.11. Database `bumdes` terdeteksi tetapi masih fresh; publish, migration, dan build asset menjadi checkpoint berikutnya.                                                                   |
| 22 September 2026          | Setup fondasi selesai: tabel migration, permission, activity log, dan session berhasil dibuat; konfigurasi broadcast diarahkan ke Reverb, filesystem ke public, storage link dibuat, dan Vite production build berhasil.                                                                                          |
| 22 September 2026          | Verifikasi checkpoint setup lulus: `php artisan test` menghasilkan 2 test lulus (2 assertions); `php artisan migrate` tidak memiliki migration tertunda.                                                                                                                                                          |
