# Product Requirements Document (PRD)
## SIM-BUMDes — Sistem Informasi Manajemen BUMDes Terintegrasi

**Versi:** 2.1 (update: jawaban pertanyaan terbuka, penyesuaian alur iuran & user flow, untung-rugi per BUMDes, kebutuhan autentikasi)
**Tanggal:** 17 September 2026
**Sumber acuan:** BRD v2.0, PRD v2.0, hasil diskusi teknis lanjutan
**Status:** Draft aktif — dokumen ini menjadi acuan utama pengembangan dan wajib diperbarui setiap ada perubahan requirement (lihat Riwayat Revisi di akhir dokumen)

---

## 1. Aplikasi Apa Ini?

**SIM-BUMDes** adalah aplikasi web internal untuk mengelola operasional Badan Usaha Milik Desa (BUMDes) secara digital dan berjenjang — dari tingkat unit usaha di satu kelurahan, sampai pemantauan gabungan di tingkat kecamatan.

Aplikasi ini menggantikan pencatatan manual/semi-manual yang selama ini dipakai BUMDes untuk:
- Mencatat transaksi keuangan tiap unit usaha (PAMDes, peternakan, mitra tani, sewa mobil, pengelolaan sampah)
- Menagih dan memverifikasi pembayaran pelanggan
- Mengelola iuran wajib bulanan antar-BUMDes ke koordinator kecamatan
- Menjalankan program referral untuk menumbuhkan jaringan BUMDes baru
- Memberi visibilitas real-time kepada pengawas, penasihat, dan direktur atas seluruh BUMDes yang berjalan

**Fase 1 (percobaan)** berjalan di 2 kelurahan: **Sendangsari** (koordinator pusat kecamatan) dan **Sendangrejo**, Kecamatan Minggir.

---

## 2. Latar Belakang & Masalah

Pengelolaan BUMDes di banyak kelurahan masih manual, menyulitkan:
- Pemantauan kinerja keuangan tiap unit usaha secara akurat dan tepat waktu
- Transparansi & akuntabilitas ke pengawas/penasihat/direktur tingkat kecamatan
- Koordinasi antar-BUMDes (iuran wajib, insentif referral)
- Verifikasi pembayaran pelanggan yang tersebar di berbagai unit usaha dengan skema berbeda-beda

---

## 3. Goals (Tujuan Produk)

| # | Goal | Indikator Keberhasilan |
|---|---|---|
| G1 | Digitalisasi pencatatan keuangan per unit usaha dengan skema fleksibel per jenis unit | Seluruh 5 unit di 2 BUMDes mencatat transaksi rutin selama minimal 1 bulan tanpa kendala kritis |
| G2 | Struktur data wilayah berjenjang yang scalable (provinsi > kota > kecamatan > kelurahan) | Penambahan BUMDes/kelurahan baru tidak memerlukan perubahan struktur database |
| G3 | Otomasi iuran bulanan + mekanisme referral antar-BUMDes | Minimal 1 siklus referral berhasil diuji end-to-end (generate → redeem → verifikasi → cair/gagal) |
| G4 | Kontrol akses berjenjang sesuai 9 role, termasuk aktif/nonaktifkan entitas | Tidak ada insiden akses lintas-BUMDes yang tidak sah selama uji coba |
| G5 | Dashboard monitoring real-time + mekanisme feedback dari level pengawasan | Waktu tindak lanjut feedback oleh pengurus BUMDes rata-rata < 3 hari kerja |
| G6 | Laporan keuangan yang dapat diekspor ke Excel | Laporan dapat diekspor tanpa error untuk seluruh unit dan BUMDes aktif |
| G7 | UX yang tetap dapat diandalkan pada koneksi internet terbatas (khas pedesaan) | 0 insiden kehilangan data input akibat koneksi lambat selama uji coba |

---

## 4. Target User & Persona

Total **9 role**, dibagi 2 kelompok akses: **Panel Internal** (login web biasa, akses sesuai kewenangan) dan **Portal Pengguna** (akses terbatas untuk pelanggan/warga).

| Role | Jumlah Akun | Kelompok Akses | Kebutuhan Utama |
|---|---|---|---|
| **Super Admin** | 1 | Panel Internal | Kontrol penuh seluruh sistem, monitoring seluruh akun & BUMDes, audit |
| **Pengawas** | 3 | Panel Internal | Visibilitas real-time seluruh BUMDes + kemampuan memberi arahan |
| **Penasihat** | 1 | Panel Internal | Sama seperti Pengawas |
| **Direktur** | 1 | Panel Internal | Sama seperti Pengawas |
| **Admin BUMDes** | 1/BUMDes | Panel Internal | Kelola unit usaha, tim internal, iuran, referral |
| **Sekretaris** | 1/BUMDes | Panel Internal | Administrasi non-keuangan, pelanggan, tagihan, pengumuman |
| **Bendahara** | 1/BUMDes | Panel Internal | Seluruh transaksi & pencatatan keuangan BUMDes |
| **Admin Unit** | 1/unit (5/BUMDes) | Panel Internal | Input transaksi & tagihan harian, meski koneksi lambat |
| **Pengguna/Pelanggan** | Tidak terbatas | Portal Pengguna | Cek tagihan, bayar, upload bukti transfer |

---

## 5. Scope Project

### 5.1 In-Scope (Fase 1)

- Manajemen wilayah berjenjang (provinsi/kota/kecamatan/kelurahan) dengan ID unik, uji coba 2 kelurahan
- Manajemen BUMDes & unit usaha (5 unit standar + custom), toggle aktif/nonaktif berjenjang (BUMDes → unit → pelanggan)
- Pencatatan transaksi per unit dengan skema field fleksibel (JSON) per jenis unit
- Tagihan pelanggan: manual (tunai) + upload bukti transfer + verifikasi admin unit
- Iuran bulanan wajib Rp50.000/BUMDes + referral antar-BUMDes (kode berlaku 5 hari, verifikasi aktivitas 15 hari)
- RBAC 9 role sesuai matriks akses
- Dashboard monitoring nasional real-time + modul feedback/catatan + notifikasi
- Log aktivitas seluruh akun (audit trail)
- Ekspor laporan ke Excel (per unit & per BUMDes)
- Aplikasi web murni (tanpa aplikasi native), dioptimalkan untuk koneksi lambat

### 5.2 Out-of-Scope (Fase 1 — ditunda)

- **Payment gateway** (pembayaran otomatis online) — ditunda, tidak ada anggaran pada fase ini
- Mode offline / PWA dengan sinkronisasi
- Aplikasi mobile native
- ~~FilamentPHP sebagai admin panel~~ — **dihapus permanen dari stack**, diganti custom Blade + Livewire (lihat Bab 9)
- **Docker** — **bukan dihapus**, hanya **diurutkan belakangan**: development lokal (Laragon) dan deployment awal ke server dikerjakan dulu tanpa container; containerization dipertimbangkan kembali setelah deployment lokal/production non-Docker terbukti berjalan stabil (lihat Bab 9.6)

### 5.3 Batasan (Constraints)

- Tim pengembang memiliki keahlian PHP/Laravel; stack lain tidak dipertimbangkan
- Koneksi internet di lokasi (Sendangsari, Sendangrejo) tergolong terbatas/tidak stabil
- Tidak ada anggaran untuk payment gateway berbayar pada fase ini
- Development dilakukan di Windows dengan Laragon; deployment ke VPS memakai instalasi manual (LEMP stack), bukan container

---

## 6. Fitur Utama

### 6.1 Manajemen Wilayah & BUMDes
- CRUD Region berjenjang (self-referencing: provinsi → kota → kecamatan → kelurahan)
- Penandaan 1 kelurahan sebagai koordinator kecamatan
- CRUD BUMDes per kelurahan + toggle aktif/nonaktif (khusus Super Admin)

### 6.2 Manajemen Unit Usaha
- CRUD unit usaha per BUMDes (5 jenis standar: PAMDes, Peternakan, Mitra Tani, Sewa Mobil, Sampah + custom)
- Skema field transaksi dapat dikonfigurasi berbeda per jenis unit (JSON schema)
- Toggle aktif/nonaktif unit (khusus Admin BUMDes)

### 6.3 Transaksi & Perhitungan Untung-Rugi
- Input transaksi (input/output) sesuai skema field unit
- Perhitungan otomatis untung-rugi **per unit**, per periode
- **Agregasi otomatis untung-rugi per BUMDes** (gabungan seluruh unit di bawahnya) — ditampilkan di dashboard BUMDes maupun dashboard nasional, tidak hanya level unit

### 6.4 Pelanggan & Tagihan
- CRUD pelanggan per unit + toggle aktif/nonaktif (khusus Admin Unit)
- Buat tagihan, pembayaran tunai (dicatat manual) atau transfer (upload bukti)
- Verifikasi/tolak bukti transfer oleh Admin Unit

### 6.5 Iuran & Referral Antar-BUMDes
- Generate tagihan iuran Rp50.000/bulan otomatis (scheduled job)
- Pembayaran iuran oleh Bendahara tiap BUMDes: dari kas BUMDes atau di luar kas, dengan **metode transfer atau tunai langsung**
- Bendahara **mengirimkan bukti pembayaran iuran** (foto/scan bukti transfer, atau catatan tunai) **ke Admin BUMDes Koordinator (Sendangsari)** untuk diverifikasi
- Generate kode referral unik (berlaku 5 hari) per BUMDes, ditampilkan di dashboard
- Redeem kode oleh BUMDes lain, kas referral Rp10.000 cair setelah syarat aktivitas minimal terpenuhi dalam 15 hari

### 6.6 Akun & Hak Akses
- CRUD akun berjenjang sesuai kewenangan (Super Admin buat semua; Admin BUMDes buat tim internal BUMDes-nya)
- RBAC granular per modul (lihat Bab 9.3)

### 6.7 Dashboard Monitoring & Feedback
- Dashboard nasional real-time (status iuran, saldo kas, ringkasan untung-rugi per unit **dan per BUMDes**, indikator otomatis)
- Modul feedback/catatan dari Pengawas/Penasihat/Direktur/Super Admin ke BUMDes/unit
- **Notifikasi real-time via WebSocket (Laravel Reverb)** ke pengurus BUMDes/unit terkait — instan tanpa jeda polling

### 6.8 Laporan & Ekspor
- Ekspor laporan Excel per unit dan per BUMDes, **tersedia format mingguan dan bulanan**
- **Filter** laporan berdasarkan rentang tanggal, unit, status, dan periode (mingguan/bulanan)
- **Pencarian (search)** pada seluruh tabel data utama (transaksi, tagihan, pelanggan, akun, dll.)
- Log aktivitas seluruh akun, dapat diaudit Super Admin, dilengkapi filter & pencarian yang sama

---

## 7. User Flow

### 7.1 Alur Pendaftaran & Setup Awal
```
Super Admin login
   -> pilih/buat Provinsi
      -> pilih/buat Kota/Kabupaten
         -> pilih/buat Kecamatan
            -> tambah BUMDes baru (tetapkan Kelurahan, status koordinator)
   -> buat akun Admin BUMDes untuk BUMDes tersebut
   -> (opsional, tidak wajib lewat Admin BUMDes) Super Admin JUGA dapat langsung
      membuat akun lain di dalam BUMDes tsb: Sekretaris, Bendahara, Admin Unit,
      bahkan akun Pelanggan/Pengguna — tidak terbatas hanya membuat Admin BUMDes
Admin BUMDes login
   -> lengkapi data BUMDes
   -> tambah unit usaha (5 standar / custom)
   -> buat akun Sekretaris, Bendahara, Admin Unit (1 akun = 1 unit)
```
> Catatan: pembuatan akun bersifat fleksibel dari sisi kewenangan — Super Admin punya akses penuh membuat akun jenis apa pun di BUMDes mana pun (termasuk Pelanggan), sedangkan Admin BUMDes hanya dapat membuat akun untuk tim internal BUMDes-nya sendiri (Sekretaris, Bendahara, Admin Unit). Lihat matriks akses lengkap di `SRS.md` Bab 3.10.

### 7.2 Alur Transaksi Harian
```
Admin Unit login -> pilih unit -> input transaksi (input/output)
   -> sistem hitung untung-rugi otomatis
   -> data tercermin real-time di dashboard BUMDes & nasional
```

### 7.3 Alur Tagihan & Pembayaran
```
Admin Unit buat tagihan -> Pengguna login -> lihat tagihan
   -> bayar tunai (dicatat admin) ATAU upload bukti transfer
   -> [jika transfer] Admin Unit verifikasi -> lunas / ditolak
```

### 7.4 Alur Iuran Bulanan
```
Sistem generate tagihan iuran otomatis (tanggal 1/bulan)
   -> Bendahara tiap BUMDes bayar (dari kas / di luar kas)
      -> metode: transfer ATAU tunai langsung
   -> Bendahara kirim bukti pembayaran (foto bukti transfer / catatan tunai)
      ke Admin BUMDes Koordinator (Sendangsari)
   -> Admin BUMDes Koordinator verifikasi -> status lunas
```

### 7.5 Alur Referral
```
Admin BUMDes pengaju lihat kode (berlaku 5 hari) di dashboard
   -> bagikan ke BUMDes calon anggota
   -> Admin BUMDes penerima redeem kode
   -> validasi (berlaku & belum dipakai) -> status pending
   -> [dalam 15 hari] syarat aktivitas terpenuhi? -> ya: kas cair (+Rp10.000) | tidak: status gagal
   -> kode lama nonaktif, kode baru otomatis terbit untuk pengaju
```

### 7.6 Alur Monitoring & Feedback
```
Pengawas/Penasihat/Direktur/Super Admin login -> lihat dashboard nasional
   -> sistem tampilkan indikator otomatis (tunggakan, unit tanpa aktivitas, dst)
   -> beri catatan/feedback ke BUMDes/unit tertentu
   -> notifikasi real-time diterima pengurus terkait
   -> pengurus tindak lanjuti & update status (belum/sedang/selesai)
```

---

## 8. UI/UX — Desain yang Diinginkan

### 8.1 Prinsip Desain
- **Sederhana & fungsional** — prioritas kemudahan pengguna lintas usia dan tingkat literasi digital (bukan tampilan flashy), karena banyak pengguna adalah warga desa dan pengurus BUMDes dengan latar belakang non-teknis.
- **Konsisten** — 1 sistem desain (design token warna, tipografi, spacing) dipakai di seluruh panel internal maupun portal pengguna, dibangun manual dengan Tailwind CSS (bukan template admin generik).
- **Responsif** — dapat diakses dari desktop maupun ponsel, karena admin unit dan pengguna kemungkinan besar mengakses lewat HP.
- **Toleran koneksi lambat** — setiap aksi submit menampilkan status loading yang jelas (`wire:loading` Livewire), retry otomatis saat gagal, dan data form tidak hilang saat koneksi terputus.

### 8.2 Struktur Tampilan

**Panel Internal** (Super Admin, Pengawas/Penasihat/Direktur, Admin BUMDes, Sekretaris, Bendahara, Admin Unit):
- Layout: sidebar kiri (menu navigasi sesuai role) + topbar (info akun, notifikasi) + area konten utama
- Komponen utama: tabel data dengan pencarian & filter, form create/edit modal atau halaman terpisah, badge status berwarna (aktif/nonaktif, lunas/belum bayar/menunggu verifikasi), kartu ringkasan (widget) di halaman dashboard
- Warna status: hijau (lunas/aktif/cair), kuning (menunggu/pending), merah (ditolak/gagal/menunggak), abu-abu (nonaktif)

**Portal Pengguna** (Pelanggan):
- Layout lebih sederhana: navigasi atas minimal, fokus pada 2 halaman utama (daftar tagihan, detail tagihan + upload bukti)
- Tombol besar & jelas, istilah bahasa Indonesia sehari-hari (bukan istilah teknis)
- Konfirmasi visual jelas setelah aksi (misal: "Bukti berhasil diunggah, menunggu verifikasi")

### 8.3 Wireframe Tekstual — Halaman Kunci

**Dashboard Monitoring Nasional (Pengawas/Direktur):**
```
┌───────────────────────────────────────────────────┐
│ [Logo]  SIM-BUMDes         [🔔 Notifikasi] [Akun ▾] │
├───────────┬─────────────────────────────────────────┤
│ Sidebar   │  Ringkasan Nasional                      │
│ - Dashboard│  [Total BUMDes Aktif] [Menunggak Iuran] │
│ - BUMDes  │  [Unit Tanpa Aktivitas] [Verifikasi Tertunda]│
│ - Feedback│                                           │
│           │  Tabel Status BUMDes                     │
│           │  | BUMDes | Iuran | Kas | Untung-Rugi |  │
│           │  | Sendangsari | Lunas | Rp10rb | +350rb||
│           │  | Sendangrejo | Menunggak | Rp0 | ... | │
└───────────┴─────────────────────────────────────────┘
```

**Portal Pengguna — Daftar Tagihan:**
```
┌───────────────────────────────┐
│  Tagihan Saya                  │
├───────────────────────────────┤
│  PAMDes — Rp25.000             │
│  Jatuh tempo: 25 Sep 2026      │
│  Status: [Belum Bayar]         │
│  [ Bayar / Upload Bukti ]      │
├───────────────────────────────┤
│  Sampah — Rp10.000             │
│  Status: [Lunas ✓]             │
└───────────────────────────────┘
```

---

## 9. Arsitektur Sistem (Detail)

### 9.1 Gaya Arsitektur

**Modular Monolith** dengan Laravel — satu basis kode, dipisah secara logis per modul lewat Service Layer dan namespace terstruktur. Dipilih karena skala fase percobaan kecil, tim kecil, dan kebutuhan deployment sederhana **tanpa Docker maupun admin-panel generator pihak ketiga (FilamentPHP)**.

```
┌──────────────────────────────────────────────────────────────┐
│                     Laravel Application                       │
│                                                                 │
│  ┌────────────────────────┐   ┌───────────────────────────┐   │
│  │  Panel Internal         │   │  Portal Pengguna           │   │
│  │  (Blade + Livewire,     │   │  (Blade + Livewire,        │   │
│  │   custom-built, RBAC    │   │   ringan & sederhana)      │   │
│  │   per role via Policy)  │   │                             │   │
│  └────────────┬────────────┘   └─────────────┬───────────────┘   │
│               │                              │                   │
│  ┌────────────▼──────────────────────────────▼───────────────┐  │
│  │              Service / Action Layer                         │  │
│  │  RegionService, BumdesService, UnitService, TransaksiService,│ │
│  │  TagihanService, IuranService, ReferralService,              │ │
│  │  FeedbackService, ReportService                              │ │
│  └────────────┬─────────────────────────────────────────────┘  │
│               │                                                   │
│  ┌────────────▼─────────────────────────────────────────────┐  │
│  │         Eloquent Models + Policies (RBAC)                   │  │
│  └────────────┬─────────────────────────────────────────────┘  │
│               │                                                   │
│  ┌────────────▼─────────────────────────────────────────────┐  │
│  │              MySQL 8.x                                       │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Queue Worker (Laravel Queue, database driver) -> Job:          │
│  GenerateExcelReport, KirimNotifikasiFeedback                   │
│                                                                 │
│  Scheduler (Laravel Scheduler via cron) -> Command:             │
│  iuran:generate-bulanan, referral:expire-check,                 │
│  referral:verify-check                                          │
└──────────────────────────────────────────────────────────────┘
```

### 9.2 Mengapa Tanpa FilamentPHP

Panel internal dibangun **manual dengan Blade + Livewire**, bukan memakai FilamentPHP, agar:
- Tampilan dapat sepenuhnya disesuaikan dengan kebutuhan pengguna desa (bukan konvensi admin panel generik)
- Tidak ada dependensi ke package besar pihak ketiga yang membawa banyak fitur tidak terpakai
- Tim dapat mengontrol penuh setiap komponen (form, tabel, validasi) sesuai kompleksitas aturan bisnis yang spesifik (skema field dinamis per unit, state machine referral, dll.)

**Pendekatan teknis pengganti Filament:**
- **Tabel data** — komponen Livewire custom dengan pagination, pencarian, filter (dibangun sendiri di atas Eloquent, atau memakai helper ringan seperti Livewire's built-in pagination — tanpa package admin-panel penuh)
- **Form create/edit** — komponen Livewire per modul (`UnitForm`, `TagihanForm`, dst.), validasi lewat Livewire `rules()`
- **Layout admin** — 1 Blade layout (`layouts/panel.blade.php`) dipakai bersama, dengan sidebar dinamis sesuai role yang login (dicek lewat `@can` / Policy)
- **Notifikasi & badge** — komponen Livewire kecil dengan `wire:poll` untuk update berkala

### 9.3 RBAC & Struktur Akses (tanpa Filament Panel)

Karena tidak ada multi-panel Filament, pemisahan akses per role dilakukan lewat:
1. **Middleware role** pada route group (`Route::middleware(['auth', 'role:admin_bumdes'])`)
2. **Policy per Model** (cek kombinasi role + kepemilikan data `id_bumdes`/`id_unit`)
3. **Blade directive `@role`/`@can`** untuk menyembunyikan elemen UI yang tidak relevan bagi role tertentu, sehingga 1 halaman/layout dapat dipakai beberapa role dengan tampilan menu yang menyesuaikan otomatis

Struktur route (`routes/web.php`):
```php
Route::middleware(['auth'])->group(function () {

    Route::middleware(['role:super_admin'])->prefix('super-admin')->group(function () {
        // Region, BUMDes (semua), Akun (semua), Log Aktivitas
    });

    Route::middleware(['role:admin_bumdes,sekretaris,bendahara,admin_unit'])->prefix('bumdes')->group(function () {
        // Unit, Pelanggan, Transaksi, Tagihan, Iuran, Referral (scoped ke id_bumdes akun login)
    });

    Route::middleware(['role:pengawas,penasihat,direktur,super_admin'])->prefix('monitoring')->group(function () {
        // Dashboard nasional, Feedback
    });

    Route::middleware(['role:pengguna'])->prefix('portal')->group(function () {
        // Cek tagihan, upload bukti
    });
});
```

### 9.4 Struktur Folder Laravel

```
sim-bumdes/
├── app/
│   ├── Console/Commands/          # GenerateIuranBulanan, ReferralExpireCheck, ReferralVerifyCheck
│   ├── Http/
│   │   ├── Livewire/
│   │   │   ├── SuperAdmin/        # RegionManager, BumdesManager, AkunManager, LogAktivitasViewer
│   │   │   ├── Bumdes/            # UnitManager, PelangganManager, TransaksiForm, TagihanManager, ReferralPanel
│   │   │   ├── Monitoring/        # DashboardNasional, FeedbackForm, FeedbackList
│   │   │   └── Portal/            # CekTagihan, UploadBukti
│   │   └── Middleware/
│   ├── Models/                    # Region, Bumdes, UnitUsaha, Transaksi, Pelanggan, Tagihan,
│   │                               # IuranBumdes, KasBumdes, KasMutasi, Referral, Akun, Feedback
│   ├── Policies/                  # 1 policy per model utama
│   ├── Services/                  # Business logic per modul (lihat 9.1)
│   ├── Notifications/             # FeedbackDiterima, TagihanBaru
│   └── Exports/                   # UnitTransaksiExport, BumdesLaporanExport (maatwebsite/excel)
├── resources/views/
│   ├── layouts/panel.blade.php    # Layout bersama seluruh panel internal
│   ├── layouts/portal.blade.php   # Layout portal pengguna
│   └── livewire/                  # View tiap komponen Livewire di atas
├── database/{migrations,seeders,factories}/
├── routes/{web.php,console.php}
└── tests/
```

### 9.5 Alur Data Kritis

- **Referral (state machine):** kolom `status` di tabel `referral` + 2 scheduled command (`referral:expire-check` tiap jam, `referral:verify-check` harian) — detail state machine di Bab 11.
- **Iuran Bulanan:** scheduled command `iuran:generate-bulanan` (tanggal 1 tiap bulan) membuat tagihan iuran untuk seluruh BUMDes aktif.
- **Notifikasi Feedback:** **Laravel Reverb** (WebSocket self-hosted) via `broadcast` channel — notifikasi tersampaikan real-time (instan) ke pengurus BUMDes/unit terkait tanpa jeda, menggantikan pendekatan polling pada rencana awal. Reverb dipilih karena open-source, self-hosted (tidak perlu layanan pihak ketiga berbayar seperti Pusher), dan terintegrasi native dengan Laravel Echo di sisi frontend.

### 9.6 Deployment (Docker Diimplementasikan Belakangan)

Development memakai **Laragon di Windows** (PHP + MySQL + Composer lokal, tanpa container) — ini yang dikerjakan lebih dulu sampai aplikasi berjalan stabil secara lokal. Deployment awal ke server produksi juga memakai instalasi tradisional (bukan container) agar tim fokus menyelesaikan fungsionalitas terlebih dahulu:

```
VPS (Ubuntu 22.04 LTS) — Deployment Awal (Tanpa Docker)
├── Nginx (webserver) -> reverse proxy ke PHP-FPM
├── PHP 8.2-FPM (terinstal langsung di OS, bukan container)
├── MySQL 8.x (terinstal langsung di OS)
├── Supervisor -> menjaga proses `php artisan queue:work` dan `php artisan reverb:start` tetap berjalan
└── Cron job -> menjalankan `php artisan schedule:run` tiap menit
```

Langkah deployment garis besar: clone repository ke server → `composer install --no-dev` → konfigurasi `.env` produksi → `php artisan migrate --force` → `php artisan config:cache` → setup Nginx server block → setup Supervisor untuk queue worker & Reverb → setup cron untuk scheduler.

**Docker baru dipertimbangkan pada tahap berikutnya**, setelah deployment lokal/production non-Docker ini terbukti berjalan stabil — bukan dihilangkan dari rencana, hanya diurutkan belakangan sesuai keputusan tim. Saat waktunya tiba, konfigurasi Docker (docker-compose, Dockerfile, dsb.) akan disusun ulang mengikuti struktur aplikasi final (tanpa Filament) yang sudah berjalan.

### 9.7 Keamanan

- Autentikasi sesi Laravel bawaan (`auth` scaffolding), password di-hash bcrypt
- CSRF protection aktif default di seluruh form Livewire
- Validasi upload file bukti transfer: mime-type (jpg/png/pdf), maksimum 2MB, nama file di-hash
- Rate limiting login: 5 percobaan/menit per IP
- Setiap perubahan status penting (aktif/nonaktif, verifikasi tagihan/iuran/referral) tercatat di log aktivitas (`spatie/laravel-activitylog`)

---

## 10. Database Overview

12 entitas utama + 1 tabel log otomatis dari package. Skema lengkap (kolom, tipe data, relasi) tersedia terpisah di `SRS.md` Bab 3 dan `database-schema.dbml` (import ke dbdiagram.io untuk visual ERD).

| Entitas | Ringkasan |
|---|---|
| `region` | Struktur wilayah berjenjang (self-referencing). **ID mengikuti format kode wilayah Kemendagri** (contoh: `34.04.07.2005`), ditambah kolom `jenis_wilayah` (Provinsi/Kabupaten/Kota/Kecamatan/Kelurahan-Desa) dan `nama_lengkap` — bukan hanya kode, agar admin/pengguna mudah membaca tanpa harus menghafal arti kode |
| `bumdes` | Data BUMDes per kelurahan, `status_aktif`, format ID `BMD-xxx` |
| `unit_usaha` | Unit di bawah BUMDes, skema field JSON fleksibel, format ID `UNT-xxx` |
| `akun` | Seluruh akun 9 role, format ID `AKN-xxx` |
| `pelanggan` | Data pelanggan per unit, opsional terhubung ke akun login, format ID `PLG-xxx` |
| `transaksi` | Catatan input/output tiap unit, format ID `TRX-xxx` |
| `tagihan` | Tagihan pelanggan + status verifikasi, format ID `TAG-xxx` |
| `iuran_bumdes` | Iuran bulanan wajib per BUMDes, termasuk `metode_bayar` (transfer/tunai) dan `bukti_pembayaran_url` yang dikirim ke koordinator, format ID `IUR-xxx` |
| `kas_bumdes` + `kas_mutasi` | Saldo & riwayat mutasi kas BUMDes |
| `referral` | Riwayat kode referral & state machine-nya, format ID `REF-xxx` |
| `feedback` | Catatan dari level pengawasan ke BUMDes/unit, format ID `FB-xxx` |
| `activity_log` | Log audit (otomatis dari package `spatie/laravel-activitylog`) |

**Detail skema wilayah (`region`) mengikuti Kemendagri:**

| Jenis Wilayah | Contoh Kode | Contoh Nama Lengkap |
|---|---|---|
| Provinsi | `34` | Daerah Istimewa Yogyakarta |
| Kabupaten/Kota | `34.04` | Kabupaten Sleman |
| Kecamatan | `34.04.07` | Kecamatan Minggir |
| Kelurahan/Desa | `34.04.07.2005` | Sendangsari |

Dengan pendekatan ini, tampilan di UI selalu menampilkan **kode + jenis wilayah + nama lengkap** sekaligus (misal: "34.04.07.2005 — Kelurahan/Desa — Sendangsari"), bukan kode mentah saja, sehingga memudahkan Super Admin maupun pengguna lain mengenali wilayah tanpa perlu menghafal struktur kode.

> **Catatan sinkronisasi:** perubahan skema `region` dan penambahan agregasi untung-rugi per BUMDes (Bab 6.3) perlu disinkronkan ke `SRS.md` Bab 3.1 dan `database-schema.dbml` pada pembaruan berikutnya — belum dilakukan di dokumen tersebut per revisi PRD ini.

Untung-rugi kini dihitung di **dua level**: per unit (`transaksi` diagregasi per `id_unit`) dan per BUMDes (agregasi seluruh unit di bawah `id_bumdes` yang sama) — lihat Bab 6.3.

Primary key entitas bisnis memakai **string dengan format prefix** (bukan auto-increment polos) agar mudah ditelusuri lintas modul — detail konvensi di `architecture.md` Bab 9 (dokumen arsitektur terpisah) dan `SRS.md` Bab 6.

---

## 11. Technical Requirements

### 11.1 Tech Stack (Revisi — Tanpa Filament, Docker Diimplementasikan Belakangan)

| Layer | Teknologi |
|---|---|
| Backend Framework | Laravel 11.x, PHP 8.2+ |
| Admin Panel | **Custom Blade + Livewire 3** (bukan FilamentPHP) |
| Portal Pengguna | Blade + Livewire 3 |
| CSS | Tailwind CSS |
| Database | MySQL 8.x |
| Auth & RBAC | Laravel session auth bawaan + `spatie/laravel-permission` |
| Log Audit | `spatie/laravel-activitylog` |
| Ekspor Excel | `maatwebsite/excel` |
| Notifikasi Real-time | **Laravel Reverb** (WebSocket self-hosted, broadcast channel) |
| Queue | Laravel Queue (database driver) |
| Environment Dev | **Laragon (Windows)** — PHP, MySQL, Composer lokal tanpa container |
| Environment Deploy (Fase Awal) | VPS Ubuntu dengan Nginx + PHP-FPM + MySQL terinstal langsung (tanpa Docker) |
| Environment Deploy (Fase Lanjutan) | Docker — **diimplementasikan belakangan**, setelah deployment non-container terbukti stabil |
| Testing | PHPUnit / Pest |

### 11.2 Kebutuhan Non-Fungsional

| Kategori | Spesifikasi |
|---|---|
| Keamanan | Password bcrypt, CSRF aktif, validasi upload file, rate limiting login |
| Performa | Pagination maksimum 25 data/halaman, index pada seluruh FK dan kolom filter (status, tanggal) |
| Skalabilitas | Struktur ID prefix konsisten, region self-referencing mendukung ekspansi kecamatan/kabupaten baru |
| Ketahanan Koneksi Lambat | `wire:loading` di seluruh form submit, retry otomatis (3x percobaan, exponential backoff) |
| Audit | Setiap perubahan status kritis tercatat log aktivitas |
| Kemudahan Pengguna | UI sederhana, bahasa Indonesia sehari-hari untuk portal pengguna, responsif desktop & mobile |
| Pencarian & Filter | Seluruh tabel data utama (transaksi, tagihan, pelanggan, akun, laporan) mendukung pencarian teks dan filter (tanggal, status, unit, periode mingguan/bulanan) |

### 11.3 Kebutuhan Autentikasi

| Aspek | Ketentuan |
|---|---|
| Metode Login | Username + password (bukan email — banyak pengguna desa tidak rutin memakai email) |
| Hashing Password | bcrypt (bawaan Laravel) |
| Sesi | Session-based auth (bukan token API terpisah), `SESSION_LIFETIME` 120 menit dengan opsi "Ingat Saya" (remember token) untuk perangkat pribadi admin unit/pengguna |
| Reset Password | Dilakukan oleh **Super Admin/Admin BUMDes** secara manual (reset ke password sementara) — bukan self-service via email, karena banyak akun (terutama Pelanggan) mungkin tidak memiliki email aktif. Opsi self-service via email dipertimbangkan pada fase lanjutan jika mayoritas pengguna sudah memiliki email terverifikasi |
| Rate Limiting | Maksimum 5 percobaan login gagal per menit per IP (Laravel `throttle` middleware), lockout sementara setelah melewati batas |
| Otorisasi Setelah Login | Middleware `role:` per route group + Policy per model (kombinasi role + kepemilikan data `id_bumdes`/`id_unit`) — lihat Bab 9.3 |
| Status Akun Nonaktif | Akun dengan `status_aktif = false` otomatis ditolak saat login meski password benar, dengan pesan jelas ("Akun Anda dinonaktifkan, hubungi Admin BUMDes") |
| Autentikasi Dua Faktor (2FA) | **Tidak diimplementasikan pada Fase 1** — kompleksitas tambahan belum sepadan dengan kebutuhan pengguna desa; dipertimbangkan kembali jika ada kebutuhan keamanan lebih tinggi di fase lanjutan |
| Audit Login | Setiap login/logout (berhasil maupun gagal) tercatat di `activity_log` untuk keperluan monitoring Super Admin (FR-32) |

---

## 12. Roadmap Fase Pengembangan

| Fase | Cakupan |
|---|---|
| Fase 1 — Percobaan (saat ini) | 2 kelurahan (Sendangsari & Sendangrejo), seluruh fitur in-scope Bab 5.1 |
| Fase 2 — Penyempurnaan | Evaluasi hasil uji coba, perbaikan berdasarkan masukan lapangan |
| Fase 3 — Perluasan Wilayah | Ekspansi ke 3 kelurahan tersisa di Kecamatan Minggir |
| Fase 4 — Peningkatan Fitur (belum dijadwalkan) | Payment gateway, mode offline/PWA, aplikasi mobile — dipertimbangkan setelah Fase 3 stabil |

---

## 13. Keputusan atas Pertanyaan Terbuka (Update 17 September 2026)

Seluruh pertanyaan terbuka di PRD v2.0 telah diputuskan sebagai berikut:

| # | Pertanyaan Sebelumnya | Keputusan |
|---|---|---|
| 1 | Format & periode laporan Excel (mingguan/bulanan)? | **Kedua-duanya disediakan** — pengguna dapat memilih ekspor mingguan atau bulanan. Sistem juga dilengkapi **filter** (rentang tanggal, unit, status) dan **pencarian (search)** pada seluruh tabel data laporan (lihat Bab 6.8) |
| 2 | Field detail transaksi tiap 5 jenis unit usaha? | **Masih memerlukan masukan langsung dari pengurus BUMDes** — belum bisa diputuskan sepihak oleh tim pengembang. Mekanisme teknisnya sudah disiapkan fleksibel (`skema_field` berbasis JSON per unit, lihat Bab 6.2), sehingga begitu masukan pengurus BUMDes didapat, field dapat langsung dikonfigurasi tanpa perubahan struktur database. **Item ini tetap berstatus terbuka secara operasional**, menunggu sesi wawancara/FGD dengan pengurus BUMDes Sendangsari & Sendangrejo |
| 3 | Pola kode wilayah: Kemendagri atau internal? | **Mengikuti kode resmi Kemendagri**, dengan tambahan kolom `jenis_wilayah` dan `nama_lengkap` (bukan kode saja) agar admin/pengguna mudah membaca tanpa menghafal struktur kode — detail di Bab 10 |
| 4 | Upgrade notifikasi ke broadcast/WebSocket? | **Ya, diupgrade** — memakai **Laravel Reverb** sejak awal Fase 1 (bukan ditunda ke Fase 2), menggantikan pendekatan polling pada rencana sebelumnya. Lihat Bab 9.5 dan 11.1 |

---

## Riwayat Revisi

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 16 September 2026 | Rilis awal PRD berdasarkan BRD v2.0 |
| 2.0 | 17 September 2026 | Restrukturisasi lengkap: tambah Bab UI/UX, Arsitektur detail, Database Overview; **FilamentPHP dihapus** (diganti custom Blade+Livewire); **Docker dihapus** dari tech stack utama (deployment memakai instalasi tradisional di VPS) |
| 2.1 | 17 September 2026 | Jawaban 4 pertanyaan terbuka (Bab 13); tambah agregasi untung-rugi per BUMDes (Bab 6.3); update alur iuran dengan metode bayar & pengiriman bukti ke koordinator (Bab 6.5, 7.4); update user flow Super Admin untuk hierarki provinsi>kota>kecamatan dan kewenangan membuat akun apa pun termasuk Pelanggan (Bab 7.1); notifikasi upgrade ke Laravel Reverb sejak Fase 1 (Bab 6.7, 9.5, 11.1); skema wilayah mengikuti Kemendagri + jenis wilayah & nama lengkap (Bab 10); **Docker diklarifikasi bukan dihapus, hanya diimplementasikan belakangan** setelah deployment lokal/production non-container stabil (Bab 5.2, 9.6, 11.1); tambah Bab 11.3 Kebutuhan Autentikasi; tambah kebutuhan filter & search (Bab 6.8, 11.2) |
