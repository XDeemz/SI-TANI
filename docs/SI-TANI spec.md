# SPESIFIKASI KEBUTUHAN & PANDUAN PENGEMBANGAN SISTEM (SPEC.md)

**Proyek:** SI-TANI (Sistem Informasi Pemesanan dan Rekapitulasi Harian Hasil Tani)
**Stack Teknologi:** Laravel (PHP), Blade / Tailwind CSS (atau Bootstrap), MySQL / MariaDB (phpMyAdmin)
**Target Pengembangan:** 2 Minggu (Pengembangan Bertahap / Modular)

---

## 1. Gambaran Umum & Tujuan Sistem

Aplikasi web ini menjembatani **Petani** dan **Pedagang** dalam kesepakatan kontrak pra-panen yang fleksibel untuk komoditas agrikultur. Karena harga pasar dan bobot bersih komoditas pertanian bersifat fluktuatif saat panen berlangsung, kesepakatan awal hanya berfungsi sebagai **acuan/pertimbangan**, sementara tagihan akhir dihitung berdasarkan **akumulasi pencatatan realisasi harian secara bertahap**.

### Prinsip Dasar Domain

1. **Satu Akun, Peran Ganda Kontekstual (Marketplace Model):** Tidak ada pemisahan akun kaku antara petani dan pedagang. Satu pengguna dapat bertindak sebagai **Petani** (saat mengunggah komoditas miliknya) sekaligus **Pedagang** (saat mengajukan tawaran ke komoditas milik pengguna lain). Peran ditentukan secara kontekstual berdasarkan kepemilikan pada setiap transaksi.
2. **Estimasi Awal vs. Realisasi Aktual:** Kapasitas harian dan harga yang diajukan pada tahap penawaran hanyalah bahan pertimbangan awal dan **tidak** menentukan nilai tagihan akhir.
3. **Isolasi Ruang Rekap (1 Petani – 1 Pedagang):** Satu komoditas milik petani dapat disetujui untuk banyak pedagang (_multi-buyer_), namun setiap pasangan `(1 Petani – 1 Pedagang)` pada komoditas tersebut memiliki **tab/ruang rekap yang terpisah** dengan identitas jelas (**Nama Komoditas**, **Nama Petani**, dan **Nama Pedagang**).
4. **Pembayaran Non-Integrasi:** Sistem tidak memproses gerbang pembayaran otomatis. Pembayaran dilakukan secara **Tunai** atau **QRIS** di luar sistem; sistem hanya menghitung tagihan, menampilkan nota, dan mencatat konfirmasi pelunasan.

---

## 2. Definisi Peran & Hak Akses Kontekstual

| Konteks Peran | Identifikasi pada Transaksi           | Wewenang Utama                                                                                                                                                                                                                      |
| :------------ | :------------------------------------ | :---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Petani**    | `commodity.farmer_id == auth()->id()` | CRUD komoditas milik sendiri; membuka/menutup status ketersediaan komoditas secara manual; meninjau & menyetujui/menolak tawaran; melihat (_read-only_) tab rekap; menandai status tagihan menjadi `Lunas / Selesai`; melihat nota. |
| **Pedagang**  | `offer.trader_id == auth()->id()`     | Mencari komoditas milik pengguna lain; mengajukan tawaran pra-panen; memulai & mengakhiri periode rekap secara manual; melakukan CRUD baris rekap harian (tampilan _sheet_) selama periode aktif; melihat tagihan & nota.           |

> **Aturan Keamanan:** Pengguna **tidak dapat** mengajukan tawaran pada komoditas yang diunggah oleh akunnya sendiri (`commodity.farmer_id != auth()->id()`).

---

## 3. Spesifikasi Fitur & Aturan Bisnis

### Modul A: Manajemen Akun & Profil

- **F-01 Autentikasi Terpadu:** Registrasi, login, dan logout menggunakan satu akun pengguna.
- **F-02 Profil & Informasi Pembayaran:** Pengguna dapat mengisi informasi kontak (nomor telepon/WhatsApp) dan informasi pembayaran (`payment_info`, misalnya nomor rekening atau instruksi QRIS) yang akan ditampilkan pada tab rekap dan nota ketika pengguna bertindak sebagai petani.
- **F-03 Dashboard Kontekstual:** Memisahkan navigasi/tampilan antara aktivitas pengguna **sebagai Petani** (Komoditas Saya & Tawaran Masuk) dan **sebagai Pedagang** (Cari Komoditas, Tawaran Saya & Rekap Aktif).

### Modul B: Manajemen Komoditas & Penawaran Pra-Panen

- **F-04 Unggah & Kelola Komoditas (Petani):**
  - Input: nama komoditas, satuan (default `kg`), estimasi kapasitas harian awal (`initial_daily_capacity`), estimasi tanggal masuk masa panen (`estimated_harvest_date`), dan catatan tambahan.
  - Saat komoditas pertama kali dibuat, nilai `remaining_daily_capacity` diisi sama dengan `initial_daily_capacity`.
  - **Status Komoditas (`open` / `closed`):** Diubah secara **manual oleh petani** kapan saja untuk membuka atau menutup penerimaan tawaran baru pada komoditas tersebut.
- **F-05 Eksplorasi & Pengajuan Tawaran (Pedagang):**
  - Pedagang melihat daftar komoditas berstatus `open` milik pengguna lain beserta informasi **sisa estimasi kapasitas harian (`remaining_daily_capacity`)** dan estimasi tanggal panen.
  - Pedagang mengajukan tawaran berisi: **estimasi kapasitas harian awal (`offered_daily_capacity`)** dan **estimasi harga (`offered_price`)**.
- **F-06 Persetujuan Multi-Pedagang, Pengurangan & Pengembalian Sisa Kapasitas:**
  - Petani meninjau daftar tawaran masuk pada komoditasnya dan dapat menyetujui (`approved`) lebih dari satu tawaran pedagang secara independen. Keputusan persetujuan bersifat final (tidak ada tombol batal persetujuan terpisah).
  - **Pengurangan Sisa Kapasitas (Minimal `0`):** Ketika petani menyetujui suatu tawaran, sistem otomatis mengurangi `commodities.remaining_daily_capacity` sebesar `commodity_offers.offered_daily_capacity`, dengan batas bawah **minimal `0`**:
    $$\text{remaining\_daily\_capacity}_{\text{baru}} = \max(0,\; \text{remaining\_daily\_capacity}_{\text{lama}} - \text{offered\_daily\_capacity})$$
    Sistem juga mencatat nilai kapasitas yang benar-benar dikunci (`locked_capacity = min(remaining_lama, offered_daily_capacity)`) pada `recap_periods` agar pengembaliannya akurat.
  - **Pengembalian Kapasitas Saat Transaksi Selesai:** Ketika suatu transaksi selesai (`recap_periods.status` menjadi `paid` / `Selesai`—termasuk skenario pembatalan kesepakatan di mana pedagang langsung memulai lalu mengakhiri periode dengan nilai `0` dan petani menandai selesai), sistem otomatis **mengembalikan kapasitas yang terkunci** (`locked_capacity`) ke `commodities.remaining_daily_capacity`:
    $$\text{remaining\_daily\_capacity}_{\text{baru}} = \min(\text{initial\_daily\_capacity},\; \text{remaining\_daily\_capacity}_{\text{lama}} + \text{locked\_capacity})$$
  - Setiap tawaran yang berstatus `approved` otomatis membuat satu entitas `recap_periods` khusus untuk pasangan `(1 Petani – 1 Pedagang)` tersebut.
- **F-06b Catatan & Alasan Keputusan Tawaran (Petani):**
  - Petani dapat menyertakan pesan/keterangan (`farmer_notes`) saat merespons tawaran dari pedagang:
    - **Saat Menerima Tawaran (`accept`):** Input keterangan bersifat opsional (`nullable|string|max:1000`) yang dapat digunakan untuk catatan pengiriman, koordinasi awal, atau jadwal mulai pasokan.
    - **Saat Menolak Tawaran (`reject`):** Input alasan penolakan bersifat wajib diisi (`required|string|max:1000`) agar pedagang mengetahui alasan penolakan tawaran secara transparan (misal harga belum sesuai, kendala kuota, dsb.).
  - Pesan `farmer_notes` disimpan pada tabel `commodity_offers` dan ditampilkan pada halaman tawaran masuk petani serta pemantauan tawaran pedagang.

### Modul C: Periode & Pencatatan Rekap Harian (Tampilan Sheet)

- **F-07 Tab Rekap Terpisah per Pasangan:**
  - Setiap tab/kartu ruang rekap wajib menampilkan identitas lengkap: **Nama Komoditas**, **Nama Petani**, dan **Nama Pedagang**, disertai _badge_ status periode dan status pembayaran.
- **F-08 Kontrol Siklus Periode Rekap (Pedagang):**
  - **Mulai Periode (`ready` $\rightarrow$ `active`):** Dilakukan secara manual oleh pedagang saat petani mulai menyetorkan hasil panen (atau saat ingin menutup kesepakatan batal).
  - **Akhiri Periode (`active` $\rightarrow$ `closed`):** Dilakukan secara manual oleh pedagang ketika rangkaian setoran/periode selesai. Sistem otomatis menghitung `total_amount` dari akumulasi seluruh baris rekap dan **mengunci tabel rekap**.
- **F-09 CRUD Baris Rekap Harian Berbasis Sheet (Pedagang saat `active`, Petani `Read-Only`):**
  - Secara praktik bisnis umum pencatatan dilakukan pada `H+1` (setoran hari Senin dicatat Selasa), namun **sistem membebaskan pemilihan tanggal (`transaction_date`)** tanpa validasi tanggal kaku agar pedagang fleksibel mencatat realisasi harian.
  - **Unik 1 Baris per Tanggal:** Dalam satu `recap_periods`, **satu tanggal (`transaction_date`) hanya boleh memiliki maksimal 1 baris catatan** (dicegah duplikasi tanggal baik melalui _composite unique index_ database maupun validasi _form request_ saat _create_ dan _update_).
  - **Antarmuka Bergaya Sheet:** Halaman rekap dirancang menyerupai lembar kerja (_sheet_) tabel kronologis yang memudahkan pedagang dan petani melihat riwayat hari-hari sebelumnya sekaligus menambah/mengisi baris tanggal berikutnya dengan cepat.
  - Atribut per baris: **tanggal transaksi/setor (`transaction_date`)**, **bobot bersih aktual (`net_weight`)**, **harga pasar aktual (`actual_price`)**, **subtotal (`round(net_weight * actual_price, 2)`, presisi desimal 2 angka di belakang koma)**, dan catatan opsional.
  - **Kasus Libur / Tanpa Pengambilan:** Jika pada hari tertentu pedagang libur atau tidak mengambil barang, pedagang tetap dapat mencatat baris hari tersebut dengan `net_weight = 0` (subtotal otomatis `0.00`).
  - **CRUD & Penguncian Berlapis (`UI + Backend`):** Selama status `recap_periods` adalah `active`, pedagang bebas menambah (_Create_), mengubah (_Update_), dan menghapus (_Delete_) baris catatan harian. Setelah status berubah menjadi `closed` atau `paid`, baris rekap **terkunci (`read-only` bagi pedagang maupun petani)** di dua lapisan:
    1. **Lapisan UI:** Input form tambah, tombol edit, dan tombol hapus disembunyikan/dinonaktifkan.
    2. **Lapisan Backend:** `Controller` / `Policy` / `Form Request` wajib menolak seluruh permintaan `store`, `update`, dan `destroy` pada `recap_items` dengan respons error otorisasi/validasi apabila `recap_period.status !== 'active'`.

### Modul D: Tagihan, Konfirmasi Pelunasan & Nota (Terintegrasi di Tab Rekap)

- **F-10 Ringkasan Tagihan & Status Satu Halaman:** Di dalam **Tab Rekap** yang sama, ditampilkan:
  - Akumulasi total tagihan (`total_amount`) dalam format desimal Rupiah **2 angka di belakang koma** (misal: `Rp 1.250.500,00`).
  - Status periode/pembayaran (`Siap Dimulai`, `Aktif`, `Menunggu Pembayaran`, `Lunas / Selesai`).
  - Informasi metode pembayaran petani (`payment_info` untuk Tunai / QRIS non-integrasi).
- **F-11 Konfirmasi Pelunasan (Petani):** Ketika periode berstatus `closed` (menunggu pembayaran) dan pembayaran tunai/QRIS telah diterima di luar sistem (atau saat menyelesaikan transaksi yang dibatalkan tanpa tagihan), **petani** menekan tombol **"Tandai Lunas / Selesai"** langsung pada area tagihan di tab rekap. Aksi ini mengubah status menjadi `paid`, mencatat `paid_at`, dan **mengembalikan kapasitas yang terkunci (`locked_capacity`)** ke komoditas terkait.
- **F-12 Lihat Nota:** Tombol **"Lihat Nota"** tersedia pada tab rekap (muncul setelah periode berstatus `closed` atau `paid`) untuk membuka halaman/tampilan nota resmi siap cetak/simpan yang memuat seluruh elemen wajib berikut:
  - **Identitas Transaksi:** Nomor/kode referensi nota (misal: `NOTA-{recap_period_id}`), tanggal penutupan periode (`ended_at`), dan tanggal pelunasan (`paid_at`, jika sudah lunas).
  - **Identitas Pihak & Komoditas:** **Nama Komoditas** (beserta satuan), **Nama & Kontak Petani**, serta **Nama & Kontak Pedagang**.
  - **Status Akhir Nota:** _Badge_/cap jelas **`Menunggu Pembayaran`** (saat `closed`) atau **`Lunas / Selesai`** (saat `paid`).
  - **Tabel Rincian Harian:** Daftar kronologis seluruh `recap_items` yang memuat kolom `Tanggal Transaksi`, `Bobot Bersih`, `Harga Aktual`, `Subtotal` (2 angka di belakang koma), dan `Catatan`.
  - **Total Tagihan & Info Pembayaran:** Nilai `total_amount` akhir dalam format Rupiah 2 angka di belakang koma serta rincian metode pembayaran (Tunai / QRIS berdasarkan `farmer.payment_info`).

---

## 4. Rancangan Basis Data (ERD & Struktur Tabel MySQL)

### 4.1 Diagram Relasi Entitas

```text
users (id, name, email, phone, payment_info, password)
  ├── 1:N ── commodities (id, farmer_id, name, unit, initial_daily_capacity, remaining_daily_capacity, estimated_harvest_date, notes, status)
  │              └── 1:N ── commodity_offers (id, commodity_id, trader_id, offered_daily_capacity, offered_price, status, responded_at)
  │                            └── 1:1 (saat approved) ── recap_periods (id, commodity_offer_id, commodity_id, farmer_id, trader_id, locked_capacity, status, started_at, ended_at, paid_at, total_amount)
  │                                                          └── 1:N ── recap_items (id, recap_period_id, transaction_date [unique per period], net_weight, actual_price, subtotal, notes)
  └── 1:N ── commodity_offers (via trader_id)
```

### 4.2 Detail Kolom & Tipe Data Migration Laravel

1. **`users`**
   - `id`: `bigIncrements` (PK)
   - `name`: `string`
   - `email`: `string` (unique)
   - `phone`: `string` (nullable)
   - `payment_info`: `text` (nullable)
   - `password`: `string`
   - `timestamps`

2. **`commodities`**
   - `id`: `bigIncrements` (PK)
   - `farmer_id`: `foreignId` -> `users.id` (`cascadeOnDelete`)
   - `name`: `string`
   - `unit`: `string` (default `'kg'`)
   - `initial_daily_capacity`: `decimal(10, 2)`
   - `remaining_daily_capacity`: `decimal(10, 2)` (minimal `0.00`)
   - `estimated_harvest_date`: `date`
   - `notes`: `text` (nullable)
   - `status`: `enum('open', 'closed')` (default `'open'`)
   - `timestamps`

3. **`commodity_offers`**
   - `id`: `bigIncrements` (PK)
   - `commodity_id`: `foreignId` -> `commodities.id` (`cascadeOnDelete`)
   - `trader_id`: `foreignId` -> `users.id` (`cascadeOnDelete`)
   - `offered_daily_capacity`: `decimal(10, 2)`
   - `offered_price`: `decimal(15, 2)`
   - `status`: `enum('pending', 'approved', 'rejected')` (default `'pending'`)
   - `farmer_notes`: `text` (nullable, catatan koordinasi penerimaan atau alasan penolakan dari petani)
   - `responded_at`: `timestamp` (nullable)
   - `timestamps`

4. **`recap_periods`**
   - `id`: `bigIncrements` (PK)
   - `commodity_offer_id`: `foreignId` -> `commodity_offers.id` (`unique`, `cascadeOnDelete`)
   - `commodity_id`: `foreignId` -> `commodities.id` (`cascadeOnDelete`)
   - `farmer_id`: `foreignId` -> `users.id` (`cascadeOnDelete`)
   - `trader_id`: `foreignId` -> `users.id` (`cascadeOnDelete`)
   - `locked_capacity`: `decimal(10, 2)` (menyimpan besaran kapasitas yang dikurangi saat tawaran disetujui agar dapat dikembalikan saat transaksi selesai)
   - `status`: `enum('ready', 'active', 'closed', 'paid')` (default `'ready'`)
   - `started_at`: `timestamp` (nullable)
   - `ended_at`: `timestamp` (nullable)
   - `paid_at`: `timestamp` (nullable)
   - `total_amount`: `decimal(15, 2)` (default `0.00`)
   - `timestamps`

5. **`recap_items`**
   - `id`: `bigIncrements` (PK)
   - `recap_period_id`: `foreignId` -> `recap_periods.id` (`cascadeOnDelete`)
   - `transaction_date`: `date`
   - `net_weight`: `decimal(10, 2)` (minimal `0.00`)
   - `actual_price`: `decimal(15, 2)` (minimal `0.00`)
   - `subtotal`: `decimal(15, 2)` (hasil pembulatan 2 desimal `round(net_weight * actual_price, 2)`)
   - `notes`: `string` (nullable)
   - `timestamps`
   - **Composite Unique Index:** `unique(['recap_period_id', 'transaction_date'])` untuk memastikan 1 tanggal hanya memiliki 1 baris per periode rekap.

---

## 5. Alur State & Validasi Penting (Untuk Implementasi Controller / Policy)

### 5.1 Diagram Alur Transisi Status `recap_periods`

```text
[Tawaran Approved] ──► ready
                         │  (Pedagang klik "Mulai Rekap")
                         ▼
                       active  ◄──► [CRUD recap_items diizinkan khusus Pedagang; Petani Read-Only]
                         │  (Pedagang klik "Akhiri Rekap")
                         ▼
                       closed  ───► [Baris rekap TERKUNCI; total_amount final; "Lihat Nota" aktif]
                         │  (Petani klik "Tandai Lunas" setelah bayar Tunai/QRIS di luar sistem)
                         ▼
                        paid   ───► [Status Lunas tercatat; locked_capacity dikembalikan ke komoditas]
```

### 5.2 Aturan Validasi & Transaksi Database

1. **Persetujuan & Penolakan Tawaran (`commodity_offers.status`: `pending` $\rightarrow$ `approved` / `rejected`):**
   - Hanya `farmer_id` pemilik `commodity` yang boleh mengubah status dari `pending` ke `approved` atau `rejected`.
   - **Saat Disetujui (`accept`):**
     - Menerima input opsional `farmer_notes` (`nullable|string|max:1000`).
     - Gunakan `DB::transaction` + `lockForUpdate()` pada record `commodity`:
       1. Update `commodity_offers.status = 'approved'`, `farmer_notes = $validated['farmer_notes'] ?? null`, dan `responded_at = now()`.
       2. Hitung kapasitas yang benar-benar dikunci:
          `$locked = min((float) $commodity->remaining_daily_capacity, (float) $offer->offered_daily_capacity);`
       3. Kurangi sisa kapasitas komoditas (minimal `0`):
          `$commodity->remaining_daily_capacity = max(0, round((float) $commodity->remaining_daily_capacity - (float) $offer->offered_daily_capacity, 2));`
       4. Buat record baru pada `recap_periods` dengan `status = 'ready'` dan `locked_capacity = $locked`.
   - **Saat Ditolak (`reject`):**
     - Wajib menerima input alasan penolakan `farmer_notes` (`required|string|max:1000`).
     - Update `commodity_offers.status = 'rejected'`, `farmer_notes = $validated['farmer_notes']`, dan `responded_at = now()`. Kapasitas komoditas tidak berkurang dan tidak membuat `recap_periods`.

2. **Transisi Status `recap_periods`:**
   - `ready` $\rightarrow$ `active`: Hanya dapat dilakukan oleh `trader_id` (`started_at = now()`).
   - `active` $\rightarrow$ `closed`: Hanya dapat dilakukan oleh `trader_id` (`ended_at = now()`, hitung `total_amount = round($recapPeriod->recapItems()->sum('subtotal'), 2)`).
   - `closed` $\rightarrow$ `paid`: Hanya dapat dilakukan oleh `farmer_id` (gunakan `DB::transaction` + `lockForUpdate()` pada `commodity`):
     1. Update `status = 'paid'` dan `paid_at = now()`.
     2. Kembalikan kapasitas komoditas yang terkunci:
        `$commodity->remaining_daily_capacity = min((float) $commodity->initial_daily_capacity, round((float) $commodity->remaining_daily_capacity + (float) $recapPeriod->locked_capacity, 2));`

3. **Validasi `recap_items` (Sheet Harian & Penguncian Berlapis):**
   - **Otorisasi & State Check (Wajib di Policy/Controller):** Tolak request jika `auth()->id() !== $period->trader_id` ATAU `$period->status !== 'active'`.
   - **Keunikan Tanggal per Periode:**
     - Saat `store`: `Rule::unique('recap_items')->where('recap_period_id', $period->id)`
     - Saat `update`: `Rule::unique('recap_items')->where('recap_period_id', $period->id)->ignore($item->id)`
   - **Fleksibilitas Tanggal:** Tidak ada pembatasan kaku tanggal input (`H+1` bersifat panduan operasional pengguna).
   - **Validasi Angka:** `net_weight` wajib numerik `>= 0` (mendukung `0` saat libur) dan `actual_price` wajib numerik `>= 0`.
   - **Sinkronisasi Kalkulasi:** Setiap kali item ditambah, diubah, atau dihapus saat `active`, hitung `subtotal = round($netWeight * $actualPrice, 2)` serta perbarui `recap_periods.total_amount = round($period->recapItems()->sum('subtotal'), 2)`.

---

## 6. Panduan Eksekusi Bertahap untuk Agen AI (Antigravity)

Kerjakan proyek ini secara bertahap per _milestone_ agar kode bersih, mudah diuji, dan hemat kuota:

- **Milestone 1 (Fondasi, Database & Auth):**
  - Konfigurasi `.env` ke database MySQL lokal (`panen_kontrak`).
  - Buat seluruh _migration_, _model_, dan relasi Eloquent sesuai Bagian 4 (termasuk `locked_capacity` dan _composite unique_ pada `recap_items`).
  - Setup autentikasi pengguna (Login, Register, Logout) serta halaman Edit Profil (`phone`, `payment_info`).
- **Milestone 2 (Komoditas & Penawaran Pra-Panen):**
  - Fitur CRUD Komoditas milik petani, toggle manual status `open`/`closed`, serta tampilan sisa kapasitas acuan.
  - Fitur daftar komoditas berstatus `open` (eksplorasi bagi pedagang) & form pengajuan tawaran.
  - Fitur persetujuan tawaran oleh petani dengan pengurangan otomatis `remaining_daily_capacity` (minimal `0`), penyimpanan `locked_capacity`, dan pembuatan `recap_periods`.
- **Milestone 3 (Tab Rekap Bergaya Sheet & Penguncian Periode):**
  - Halaman Tab Rekap per pasangan `(1 Petani – 1 Pedagang)` dengan identitas lengkap (**Nama Komoditas**, **Nama Petani**, **Nama Pedagang**) dan otorisasi kontekstual (Pedagang = kelola, Petani = baca).
  - Tombol **Mulai Periode** (`ready` $\rightarrow$ `active`) dan **Akhiri Periode** (`active` $\rightarrow$ `closed`) oleh pedagang.
  - Antarmuka tabel bergaya _sheet_ untuk CRUD baris `recap_items` (1 tanggal = 1 baris unik, bebas pilih tanggal, bobot `0` untuk hari libur, perhitungan 2 angka di belakang koma) saat `active`, serta penguncian berlapis (UI + backend) saat `closed`/`paid`.
- **Milestone 4 (Tagihan, Konfirmasi Lunas, Pengembalian Kapasitas, Nota & Finishing):**
  - Panel ringkasan tagihan (format Rupiah 2 desimal) dan status pelunasan langsung di dalam Tab Rekap.
  - Tombol **Tandai Lunas / Selesai** khusus petani saat status `closed` (`closed` $\rightarrow$ `paid`) yang otomatis mengembalikan `locked_capacity` ke komoditas.
  - Halaman/tampilan **Lihat Nota** siap cetak/simpan dengan seluruh field wajib (F-12), diikuti pengujian seluruh _edge case_.
