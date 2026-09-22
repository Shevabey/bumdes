# Architecture Document
## SIM-BUMDes — Sistem Informasi Manajemen BUMDes Terintegrasi

**Versi:** 2.0 (revisi besar: Filament dihapus permanen, Docker diimplementasikan belakangan, sinkron dengan PRD v2.1)
**Tanggal:** 17 September 2026
**Acuan:** PRD v2.1, SRS v1.0

---

## 1. Gaya Arsitektur

**Modular Monolith** menggunakan Laravel 11 — satu basis kode, dipisah secara logis per modul lewat Service Layer dan namespace terstruktur. Dipilih karena skala fase percobaan kecil (2 kelurahan), tim kecil, dan kebutuhan kontrol penuh atas UI tanpa dependensi ke admin-panel generator pihak ketiga.

**Dua keputusan arsitektur penting yang membedakan dari rancangan awal:**
1. **Tidak memakai FilamentPHP.** Seluruh panel internal dibangun manual dengan Blade + Livewire agar tampilan sepenuhnya dapat disesuaikan kebutuhan pengguna desa dan tim mengontrol penuh setiap komponen.
2. **Docker tidak dipakai di tahap awal.** Development memakai Laragon (Windows), deployment awal ke VPS memakai instalasi tradisional (LEMP stack langsung di OS). Docker dipertimbangkan kembali sebagai lapisan tambahan **setelah** deployment non-container terbukti stabil — bukan dihapus dari rencana jangka panjang, hanya diurutkan belakangan.

```
┌──────────────────────────────────────────────────────────────────┐
│                     Laravel Application (Monolith)                │
│                                                                     │
│  ┌─────────────────────────┐   ┌────────────────────────────────┐ │
│  │   Panel Internal          │   │  Portal Pengguna                │ │
│  │   (Blade + Livewire,      │   │  (Blade + Livewire, ringan,     │ │
│  │    custom-built)          │   │   bahasa awam)                  │ │
│  │  - Super Admin            │   │  - Cek tagihan                  │ │
│  │  - Pengawas/Penasihat/    │   │  - Upload bukti transfer        │ │
│  │    Direktur                │   │  - Lihat perkembangan unit      │ │
│  │  - Admin BUMDes            │   │    yang diikuti                 │ │
│  │  - Sekretaris               │   └────────────────────────────────┘ │
│  │  - Bendahara                │                                      │
│  │  - Admin Unit                │                                      │
│  └────────────┬───────────────┘                                      │
│               │                                                       │
│  ┌────────────▼────────────────────────────────────────────────┐    │
│  │            Service / Action Layer                              │    │
│  │  RegionService, BumdesService, UnitService, TransaksiService,   │    │
│  │  TagihanService, IuranService, ReferralService, FeedbackService,│    │
│  │  ReportService, AuthService                                     │    │
│  └────────────┬────────────────────────────────────────────────┘    │
│               │                                                       │
│  ┌────────────▼────────────────────────────────────────────────┐    │
│  │         Eloquent Models + Policies (RBAC)                       │    │
│  └────────────┬────────────────────────────────────────────────┘    │
│               │                                                       │
│  ┌────────────▼────────────────────────────────────────────────┐    │
│  │              MySQL 8.x                                          │    │
│  └────────────────────────────────────────────────────────────┘    │
│                                                                     │
│  Queue Worker (Laravel Queue, database driver)                     │
│    -> Job: GenerateExcelReport, KirimNotifikasiFeedback             │
│                                                                     │
│  Laravel Reverb (WebSocket server, self-hosted)                    │
│    -> Broadcast notifikasi feedback & tagihan real-time             │
│                                                                     │
│  Scheduler (Laravel Scheduler via cron)                            │
│    -> iuran:generate-bulanan, referral:expire-check,                │
│       referral:verify-check                                         │
└──────────────────────────────────────────────────────────────────┘
```

---

## 2. Tech Stack

| Layer | Teknologi | Catatan |
|---|---|---|
| Backend Framework | Laravel 11.x, PHP 8.2+ | — |
| Admin Panel | **Custom Blade + Livewire 3** | Bukan FilamentPHP — lihat Bab 5 |
| Portal Pengguna | Blade + Livewire 3 | — |
| CSS | Tailwind CSS | Design token konsisten di panel internal & portal |
| Database | MySQL 8.x | — |
| Auth & RBAC | Laravel session auth bawaan + `spatie/laravel-permission` | Lihat Bab 9 |
| Log Audit | `spatie/laravel-activitylog` | — |
| Ekspor Excel | `maatwebsite/excel` | Mendukung filter mingguan/bulanan |
| Notifikasi Real-time | **Laravel Reverb** (broadcast channel via WebSocket) | Menggantikan pendekatan polling |
| Queue | Laravel Queue (database driver) | — |
| Environment Dev | **Laragon (Windows)** | PHP, MySQL, Composer lokal — tanpa container |
| Environment Deploy (Awal) | VPS Ubuntu 22.04, Nginx + PHP-FPM + MySQL terinstal langsung | Tanpa Docker |
| Environment Deploy (Lanjutan) | Docker | Diimplementasikan belakangan, lihat Bab 10 |
| Testing | PHPUnit / Pest | — |

---

## 3. Struktur Folder Laravel

```
sim-bumdes/
├── app/
│   ├── Console/Commands/
│   │   ├── GenerateIuranBulanan.php
│   │   ├── ReferralExpireCheck.php
│   │   └── ReferralVerifyCheck.php
│   ├── Http/
│   │   ├── Livewire/
│   │   │   ├── SuperAdmin/
│   │   │   │   ├── RegionManager.php          # CRUD region berjenjang (Kemendagri)
│   │   │   │   ├── BumdesManager.php          # CRUD BUMDes + toggle status
│   │   │   │   ├── AkunManager.php            # CRUD seluruh akun, semua role
│   │   │   │   └── LogAktivitasViewer.php     # Filter & search log aktivitas
│   │   │   ├── Bumdes/
│   │   │   │   ├── UnitManager.php            # CRUD unit + skema_field dinamis
│   │   │   │   ├── PelangganManager.php
│   │   │   │   ├── TransaksiForm.php
│   │   │   │   ├── TagihanManager.php
│   │   │   │   ├── IuranPanel.php             # Bayar iuran, kirim bukti ke koordinator
│   │   │   │   └── ReferralPanel.php          # Generate/redeem kode
│   │   │   ├── Monitoring/
│   │   │   │   ├── DashboardNasional.php      # Untung-rugi per unit & per BUMDes
│   │   │   │   ├── FeedbackForm.php
│   │   │   │   └── FeedbackList.php
│   │   │   ├── Portal/
│   │   │   │   ├── CekTagihan.php
│   │   │   │   └── UploadBukti.php
│   │   │   └── Shared/
│   │   │       ├── NotifikasiBadge.php        # Realtime via Reverb (Echo)
│   │   │       └── DataTable.php              # Komponen tabel reusable (search+filter+pagination)
│   │   └── Middleware/
│   │       └── EnsureAccountActive.php        # Tolak login jika status_aktif = false
│   ├── Models/
│   │   # Region, Bumdes, UnitUsaha, Transaksi, Pelanggan, Tagihan, IuranBumdes,
│   │   # KasBumdes, KasMutasi, Referral, Akun, Feedback
│   ├── Policies/                               # 1 policy per model utama
│   ├── Services/                               # Business logic (lihat diagram Bab 1)
│   ├── Notifications/
│   │   ├── FeedbackDiterima.php                # broadcast + database channel
│   │   └── TagihanBaru.php
│   └── Exports/
│       ├── UnitTransaksiExport.php             # Mendukung filter mingguan/bulanan
│       └── BumdesLaporanExport.php             # Agregasi untung-rugi per BUMDes
├── resources/views/
│   ├── layouts/
│   │   ├── panel.blade.php                     # Layout bersama seluruh panel internal
│   │   └── portal.blade.php                    # Layout portal pengguna
│   ├── components/
│   │   ├── data-table.blade.php
│   │   ├── status-badge.blade.php              # Warna status (hijau/kuning/merah/abu)
│   │   └── search-filter-bar.blade.php
│   └── livewire/                               # View tiap komponen di atas
├── database/{migrations,seeders,factories}/
├── routes/
│   ├── web.php
│   ├── console.php
│   └── channels.php                            # Otorisasi channel broadcast Reverb
└── tests/
```

---

## 4. Mengapa Tanpa FilamentPHP (Detail Implementasi Pengganti)

| Kebutuhan Filament | Solusi Pengganti (Custom) |
|---|---|
| Tabel data dengan pencarian, filter, pagination | Komponen Livewire `Shared\DataTable` reusable — menerima query builder + kolom sebagai parameter, dipakai ulang di semua modul (lihat Bab 6) |
| Form builder otomatis | Komponen Livewire per modul (`UnitManager`, `TagihanManager`, dst.) dengan `rules()` Livewire untuk validasi, field disesuaikan manual per kebutuhan bisnis (termasuk field dinamis `skema_field` unit) |
| Multi-panel per role | Middleware `role:` per route group (bukan Filament Panel Provider) — lihat Bab 9.2 |
| Widget dashboard | Komponen Livewire kustom (`DashboardNasional`) menghitung agregasi langsung dari Service Layer |
| Notifikasi bawaan | Laravel Notification + Reverb broadcast, ditampilkan lewat komponen `NotifikasiBadge` |
| Action tombol khusus (verifikasi, toggle status) | Method Livewire (`wire:click="verifikasi($id)"`) dengan konfirmasi modal custom (Alpine.js ringan, dibundel Livewire) |

Keuntungan pendekatan ini: tidak ada "fitur tersembunyi" dari package besar yang harus dipelajari/di-override, dan UI 100% dapat disesuaikan bahasa serta alur kerja pengguna desa.

---

## 5. Komponen `Shared\DataTable` (Reusable, Pengganti Fitur Tabel Filament)

Konvensi komponen tabel dipakai konsisten di seluruh modul (memenuhi kebutuhan search & filter PRD Bab 6.8 & 11.2):

```php
class DataTable extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filters = [];   // contoh: ['status' => 'belum_bayar', 'periode' => 'bulanan']
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = ['search', 'filters'];

    public function updatingSearch() { $this->resetPage(); }
}
```

Setiap modul (Tagihan, Transaksi, Pelanggan, Akun, Log Aktivitas) memakai pola yang sama, hanya mengganti query dasar dan kolom yang ditampilkan — sehingga perilaku search/filter/sort konsisten di seluruh sistem sesuai prinsip desain PRD Bab 8.1.

---

## 6. RBAC & Struktur Akses (Tanpa Filament Panel)

Karena tidak ada multi-panel Filament, pemisahan akses dilakukan lewat 3 lapis:

### 6.1 Middleware Role per Route Group

```php
// routes/web.php
Route::middleware(['auth', 'account.active'])->group(function () {

    Route::middleware(['role:super_admin'])->prefix('super-admin')->group(function () {
        Route::get('/region', RegionManager::class)->name('super-admin.region');
        Route::get('/bumdes', BumdesManager::class)->name('super-admin.bumdes');
        Route::get('/akun', AkunManager::class)->name('super-admin.akun');
        Route::get('/log-aktivitas', LogAktivitasViewer::class)->name('super-admin.log');
    });

    Route::middleware(['role:admin_bumdes,sekretaris,bendahara,admin_unit'])->prefix('bumdes')->group(function () {
        Route::get('/unit', UnitManager::class)->name('bumdes.unit');
        Route::get('/pelanggan', PelangganManager::class)->name('bumdes.pelanggan');
        Route::get('/transaksi', TransaksiForm::class)->name('bumdes.transaksi');
        Route::get('/tagihan', TagihanManager::class)->name('bumdes.tagihan');
        Route::get('/iuran', IuranPanel::class)->name('bumdes.iuran');
        Route::get('/referral', ReferralPanel::class)->name('bumdes.referral');
    });

    Route::middleware(['role:pengawas,penasihat,direktur,super_admin'])->prefix('monitoring')->group(function () {
        Route::get('/dashboard', DashboardNasional::class)->name('monitoring.dashboard');
        Route::get('/feedback', FeedbackList::class)->name('monitoring.feedback');
    });

    Route::middleware(['role:pengguna'])->prefix('portal')->group(function () {
        Route::get('/tagihan', CekTagihan::class)->name('portal.tagihan');
    });
});
```

### 6.2 Policy per Model (Kepemilikan Data)

```php
// app/Policies/UnitUsahaPolicy.php
public function update(Akun $akun, UnitUsaha $unit): bool
{
    if ($akun->hasRole('super_admin')) return true;
    if ($akun->hasRole('admin_bumdes') && $akun->id_bumdes === $unit->id_bumdes) return true;
    return false;
}

public function toggleStatus(Akun $akun, UnitUsaha $unit): bool
{
    return $akun->hasRole('admin_bumdes') && $akun->id_bumdes === $unit->id_bumdes;
}
```

### 6.3 Blade Directive untuk Elemen UI

```blade
@role('admin_bumdes')
    <button wire:click="createAccount">Tambah Akun Tim</button>
@endrole

@can('toggleStatus', $unit)
    <x-status-toggle :model="$unit" />
@endcan
```

### 6.4 Kewenangan Pembuatan Akun (Update PRD v2.1 Bab 7.1)

| Pembuat | Dapat Membuat Akun Untuk |
|---|---|
| Super Admin | **Semua jenis role, di BUMDes mana pun** — termasuk Admin BUMDes, Sekretaris, Bendahara, Admin Unit, dan **Pelanggan** |
| Admin BUMDes | Hanya tim internal BUMDes-nya sendiri: Sekretaris, Bendahara, Admin Unit |
| Sekretaris/Admin Unit | Hanya data Pelanggan (bukan akun internal) — dan hanya boleh memberi akses login ke Pelanggan yang sudah terdaftar di unit yang sama |

Diimplementasikan di `AkunService::create()` dengan validasi kombinasi `role pembuat` + `role target` sebelum insert, bukan hanya dicek di UI.

---

## 7. Skema Wilayah (Region) — Mengikuti Kemendagri

Sesuai keputusan PRD v2.1 Bab 10, struktur `region` tidak hanya menyimpan kode, tapi juga jenis wilayah dan nama lengkap:

```php
Schema::create('region', function (Blueprint $table) {
    $table->string('id_region', 20)->primary();      // format Kemendagri: 34.04.07.2005
    $table->enum('jenis_wilayah', ['provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa']);
    $table->string('nama_lengkap', 150);              // "Sendangsari", bukan kode saja
    $table->string('parent_id', 20)->nullable();
    $table->boolean('is_koordinator')->default(false);
    $table->timestamps();

    $table->foreign('parent_id')->references('id_region')->on('region')->nullOnDelete();
});
```

**Tampilan UI** selalu menggabungkan ketiganya, contoh breadcrumb: `34 — Provinsi — DI Yogyakarta > 34.04 — Kabupaten/Kota — Kabupaten Sleman > 34.04.07 — Kecamatan — Minggir > 34.04.07.2005 — Kelurahan/Desa — Sendangsari`, sehingga Super Admin/pengguna lain tidak perlu menghafal arti kode.

---

## 8. Perhitungan Untung-Rugi (Per Unit dan Per BUMDes)

Sesuai PRD v2.1 Bab 6.3, agregasi dilakukan di 2 level lewat `ReportService`:

```php
class ReportService
{
    public function untungRugiUnit(string $idUnit, ?string $periode = null): array
    {
        $query = Transaksi::where('id_unit', $idUnit);
        $this->applyPeriode($query, $periode); // filter mingguan/bulanan

        return [
            'total_input' => $query->clone()->where('tipe', 'input')->sum('jumlah'),
            'total_output' => $query->clone()->where('tipe', 'output')->sum('jumlah'),
        ];
    }

    public function untungRugiBumdes(string $idBumdes, ?string $periode = null): array
    {
        $unitIds = UnitUsaha::where('id_bumdes', $idBumdes)->pluck('id_unit');

        $query = Transaksi::whereIn('id_unit', $unitIds);
        $this->applyPeriode($query, $periode);

        return [
            'total_input' => $query->clone()->where('tipe', 'input')->sum('jumlah'),
            'total_output' => $query->clone()->where('tipe', 'output')->sum('jumlah'),
        ];
    }
}
```

Untuk performa saat data transaksi bertambah banyak di fase lanjutan, pertimbangkan tabel ringkasan harian (`unit_saldo_harian`) yang di-update via Job setelah tiap transaksi — belum diperlukan di skala Fase 1 (2 kelurahan).

---

## 9. Autentikasi (Implementasi Teknis — Detail PRD Bab 11.3)

| Aspek | Implementasi |
|---|---|
| Login | Form Livewire dengan field `username` + `password` (bukan `email`), memakai `Auth::attempt(['username' => ..., 'password' => ...])` |
| Middleware Custom | `EnsureAccountActive` — dijalankan setelah `auth`, cek `status_aktif` akun, logout paksa + redirect dengan pesan jika nonaktif |
| Reset Password | Halaman khusus di panel Super Admin/Admin BUMDes: pilih akun → generate password sementara → tampilkan sekali ke admin untuk disampaikan manual ke pengguna (bukan email otomatis) |
| Rate Limiting | `RateLimiter::for('login', ...)` — 5 percobaan/menit per kombinasi IP + username |
| Session | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, remember token aktif jika pengguna centang "Ingat Saya" |
| Broadcast Auth | `routes/channels.php` — channel privat per BUMDes (`Broadcast::channel('bumdes.{id}', ...)`) memastikan hanya akun terkait BUMDes tersebut yang menerima notifikasi Reverb |

---

## 10. Deployment

### 10.1 Development (Sekarang)

**Laragon di Windows** — PHP 8.2, MySQL 8, Composer, Node.js lokal tanpa container. Lihat `SETUP_GUIDE.md` untuk instalasi bertahap.

### 10.2 Production — Fase Awal (Tanpa Docker)

```
VPS Ubuntu 22.04 LTS
├── Nginx           -> reverse proxy ke PHP-FPM, terminasi SSL (Let's Encrypt)
├── PHP 8.2-FPM      -> terinstal langsung di OS
├── MySQL 8.x        -> terinstal langsung di OS
├── Supervisor        -> menjaga `php artisan queue:work` dan `php artisan reverb:start` tetap hidup
└── Cron              -> `php artisan schedule:run` tiap menit
```

Langkah garis besar: clone repo → `composer install --no-dev --optimize-autoloader` → `.env` produksi → `php artisan migrate --force` → `php artisan config:cache && route:cache && view:cache` → setup Nginx server block + SSL → setup Supervisor (queue worker + Reverb) → setup cron scheduler.

### 10.3 Production — Fase Lanjutan (Docker, Belakangan)

**Tidak dikerjakan sekarang.** Setelah Fase 10.2 terbukti stabil berjalan di produksi, tim dapat mempertimbangkan containerization (Docker Compose: service `app`, `webserver`, `db`, `queue-worker`, `reverb`, `scheduler`) untuk mempermudah replikasi lingkungan atau migrasi ke penyedia cloud lain. Konfigurasi Docker akan disusun ulang saat fase itu tiba, mengikuti struktur final aplikasi (tanpa Filament) — bukan konfigurasi lama yang sempat dibuat sebelum keputusan ini.

---

## 11. Keamanan

- Autentikasi sesi Laravel bawaan, password di-hash bcrypt (lihat Bab 9 untuk detail)
- CSRF protection aktif default di seluruh form Livewire
- Validasi upload file bukti transfer: mime-type (jpg/png/pdf), maksimum 2MB, nama file di-hash
- Rate limiting login: 5 percobaan/menit per kombinasi IP + username
- Broadcast channel Reverb diotorisasi per BUMDes (tidak ada kebocoran notifikasi lintas-BUMDes)
- Setiap perubahan status penting (aktif/nonaktif, verifikasi tagihan/iuran/referral, login/logout) tercatat di `activity_log`

---

## 12. Konvensi Penamaan & ID

| Entitas | Format ID | Contoh |
|---|---|---|
| Region | Kode Kemendagri berjenjang | `34.04.07.2005` |
| BUMDes | `BMD-{kode_kelurahan}-{urut3digit}` | `BMD-SDS-001` |
| Unit Usaha | `UNT-{id_bumdes}-{kode_jenis}-{urut2digit}` | `UNT-BMD-SDS-001-PAM-01` |
| Akun | `AKN-{urut6digit}` | `AKN-000123` |
| Pelanggan | `PLG-{id_unit}-{urut4digit}` | `PLG-UNT-BMD-SDS-001-PAM-01-0001` |
| Transaksi | `TRX-{YYYYMMDD}-{urut6digit}` | `TRX-20260917-000345` |
| Tagihan | `TAG-{YYYYMMDD}-{urut6digit}` | `TAG-20260917-000112` |
| Iuran BUMDes | `IUR-{id_bumdes}-{YYYYMM}` | `IUR-BMD-SDS-001-2026-09` |
| Referral | `REF-{id_bumdes_pengaju}-{6karakter_acak}` | `REF-BMD-SDS-001-A1B2C3` |
| Feedback | `FB-{urut6digit}` | `FB-000078` |

Primary key entitas bisnis memakai **string dengan format prefix** (`$incrementing = false`, `$keyType = 'string'` di Eloquent Model), bukan auto-increment polos, agar mudah ditelusuri lintas modul dan lintas dokumen (SRS, API spec, UI).

---

## 13. Referensi Dokumen Lain

- `PRD.md` — konteks produk, goals, fitur, user flow, UI/UX, scope (v2.1)
- `SRS.md` — detail model data & aturan bisnis per entitas *(perlu update lanjutan menyesuaikan skema Region Kemendagri & untung-rugi per BUMDes — lihat catatan di PRD Bab 10)*
- `database-schema.dbml` — skema database untuk dbdiagram.io *(idem, perlu update lanjutan)*
- `api-spec.json` — kontrak endpoint (OpenAPI 3.0)
- `progress.md` — status pengerjaan proyek terkini

---

## Riwayat Revisi

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 16 September 2026 | Rilis awal arsitektur: modular monolith Laravel, multi-panel Filament, deployment Docker |
| 1.1 | 16 September 2026 | Tambah catatan environment development (Laragon) vs deployment (Docker) |
| 2.0 | 17 September 2026 | **Revisi besar sesuai PRD v2.1**: FilamentPHP dihapus permanen (diganti custom Blade+Livewire dengan komponen `DataTable` reusable, Bab 4-6); Docker diklarifikasi sebagai fase lanjutan bukan dihapus (Bab 10.3); notifikasi upgrade ke Laravel Reverb (broadcast, Bab 9); skema Region mengikuti Kemendagri + jenis wilayah + nama lengkap (Bab 7); tambah perhitungan untung-rugi per BUMDes selain per unit (Bab 8); tambah Bab 9 detail implementasi autentikasi; tambah Bab 6.4 kewenangan pembuatan akun berjenjang |
