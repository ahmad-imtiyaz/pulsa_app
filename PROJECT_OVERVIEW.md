# Pulsa App - Project Overview

## 🎯 System Overview
**Pulsa App** is a Laravel-based management system for a pulsa (mobile credit) and voucher retail business. It tracks:
- **Dompet Pulsa** (e-wallet balances for providers like Mobo, Dompul, Digipos, Payafast, DANA)
- **Voucher** sales (Game vouchers, PLN, Pulsa, etc.)
- **Aksesoris** inventory & sales (Chargers, Cables, Headsets, etc.)
- **Pengeluaran** (Operational, Salary, Personal expenses)
- **Daily Summary** reports with profit/loss calculations

---

## 🗄️ Database Schema (Key Tables)

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `dompet_pulsa` | E-wallet accounts per provider | `nama`, `kode`, `saldo_awal`, `sisa_saldo_awal`, `saldo_tersedia`, `saldo_delta`, `is_active` |
| `dompet_pulsa_transactions` | Topup & Sales transactions | `dompet_pulsa_id`, `jenis` (topup/penjualan), `tanggal`, `nominal`, `harga_jual`, `laba`, `nomor_hp`, `provider` |
| `vouchers` | Voucher products | `nama`, `kode`, `jenis`, `harga_modal`, `harga_jual`, `stok`, `tanggal_berlaku`, `status` |
| `voucher_transactions` | Voucher sales | `voucher_id`, `tanggal`, `jumlah`, `harga_modal`, `harga_jual`, `total_modal`, `total_penjualan`, `laba` |
| `aksesoris` | Accessory products | `nama`, `sku`, `kategori`, `harga_modal`, `harga_jual`, `stok`, `is_active` |
| `aksesoris_transactions` | Purchase & Sales | `aksesoris_id`, `jenis` (pembelian/penjualan), `tanggal`, `jumlah`, `harga_modal`, `harga_jual`, `total_modal`, `total_penjualan`, `laba` |
| `pengeluaran` | Expenses | `kategori` (operasional/gaji/pribadi), `tanggal`, `jumlah`, `keterangan`, `karyawan_nama` |
| `daily_summaries` | Pre-calculated daily reports | All aggregated fields per date (see migration) |
| `dompet_pulsa_adjustments` | Manual balance corrections | `dompet_pulsa_id`, `jenis` (tambah/kurang), `nominal`, `tanggal`, `keterangan` |

---

## 🔗 Model Relationships

```
DompetPulsa
  ├─ hasMany → DompetPulsaTransaction (dompet_pulsa_id)
  │    ├─ scopeTopup()
  │    └─ scopePenjualan()
  └─ hasMany → DompetPulsaAdjustment (dompet_pulsa_id)

Voucher
  └─ hasMany → VoucherTransaction (voucher_id)

Aksesoris
  └─ hasMany → AksesorisTransaction (aksesoris_id)
       ├─ scopePembelian()
       └─ scopePenjualan()

Pengeluaran (standalone)
  ├─ scopeOperasional()
  ├─ scopeGaji()
  └─ scopePribadi()

DailySummary (standalone, 1 per date)
```

---

## 🧮 Key Business Logic (DompetPulsa Model)

### Balance Calculations
```php
// Total topup all time
$dompet->total_topup = $dompet->topupTransactions()->sum('nominal');

// Total penjualan all time
$dompet->total_penjualan = $dompet->penjualanTransactions()->sum('nominal');

// Formula: saldo_awal + total_topup - total_penjualan
$dompet->hitungSaldoTersedia()

// Adjustments sum (tambah = +, kurang = -)
$dompet->adjustment_sum

// Adjusted balance
$dompet->sisa_saldo_disesuaikan = hitungSaldoTersedia() + adjustment_sum

// Effective balance (with manual override delta)
$dompet->sisa_saldo_efektif = sisa_saldo_disesuaikan + saldo_delta
```

### Daily Summary Calculation (DailySummaryService)

**Pulsa Laba/Rugi per wallet:**
```
modal_awal = saldo_awal (fixed)
selisih = modal_awal - penjualan_hari_ini
sisa_saldo_saat_ini = sisa_saldo_efektif (includes adjustments + delta)
laba_rugi = sisa_saldo_saat_ini - selisih
```

**Voucher Laba:** `SUM(total_penjualan - total_modal)` per date
**Aksesoris Laba:** `SUM(laba)` for `jenis=penjualan` per date

**Total Laba Kotor = Pulsa + Voucher + Aksesoris**
**Total Pengeluaran = Operasional + Gaji + Pribadi**
**Sisa Laba = Total Laba Kotor - Total Pengeluaran**

---

## 🎮 Controllers & Routes

### Resource Routes (authenticated + verified)
| Resource | Controller | Custom Routes |
|----------|------------|---------------|
| `/dashboard` | DashboardController | - |
| `/dompet-pulsa` | DompetPulsaController | `transaksi.create`, `transaksi.store`, `transaksi.edit`, `transaksi.update`, `transaksi.destroy`, `adjustments.store`, `adjustments.destroy`, `saldo-override.update` |
| `/voucher` | VoucherController | `transaksi.create`, `transaksi.store` |
| `/aksesoris` | AksesorisController | `transaksi.create`, `transaksi.store` |
| `/pengeluaran` | PengeluaranController | - |
| `/laporan` | ReportController | `penjualan`, `stok` |

### Key Controller Patterns
- All controllers inject `DailySummaryService` via constructor
- After any create/update/delete → calls `summaryService->recalculateForDate()` or `recalculateRange()`
- Transaction creation calculates `total_modal`, `total_penjualan`, `laba` server-side

---

## 📊 DailySummaryService (Core Service)

### Methods
```php
recalculateForDate(Carbon $date): DailySummary    // Recalc single day (transactional)
recalculateRange(Carbon $start, Carbon $end): void // Recalc date range
getDashboardData(Carbon $date): array             // Full dashboard data for date
getSaldoAwalDompet(DompetPulsa $dompet, Carbon $date): float // Saldo awal for specific wallet
```

### Calculation Flow (per date)
1. **Pulsa**: Loop active dompets → get saldo awal (from prev day summary or calculate), topup, penjualan, laba/rugi
2. **Voucher**: Sum all voucher transactions for date
3. **Aksesoris**: Sum penjualan transactions for date
4. **Pengeluaran**: Group by kategori (operasional/gaji/pribadi)
5. **Totals**: Calculate laba kotor, total pengeluaran, sisa laba
6. **Save** to `daily_summaries` table (upsert)

---

## 📁 Important Files Reference

### Models
- `app/Models/DompetPulsa.php` - Complex accessors for balance calculations
- `app/Models/DompetPulsaTransaction.php` - Simple transaction model
- `app/Models/Voucher.php` - Product + computed attributes
- `app/Models/VoucherTransaction.php` - Sales record with computed totals
- `app/Models/Aksesoris.php` - Product with purchase/sales tracking
- `app/Models/AksesorisTransaction.php` - Purchase/sales with conditional fields
- `app/Models/Pengeluaran.php` - Expense with scopes
- `app/Models/DailySummary.php` - Computed totals via accessors
- `app/Models/DompetPulsaAdjustment.php` - Manual balance adjustments

### Controllers
- `app/Http/Controllers/DompetPulsaController.php` - Most complex (transactions, adjustments, saldo override)
- `app/Http/Controllers/VoucherController.php` - Standard CRUD + transactions
- `app/Http/Controllers/AksesorisController.php` - CRUD + transactions (pembelian/penjualan)
- `app/Http/Controllers/ReportController.php` - Aggregated reports
- `app/Http/Controllers/DashboardController.php` - Thin, delegates to service

### Service
- `app/Services/DailySummaryService.php` - **Core business logic**, all calculations here

### Requests (Validation)
- `DompetPulsaRequest`, `DompetPulsaTransactionRequest`
- `VoucherRequest`, `VoucherTransactionRequest`
- `AksesorisRequest`, `AksesorisTransactionRequest`
- `PengeluaranRequest`

### Views Structure
```
resources/views/
├── dashboard.blade.php
├── dompet-pulsa/
│   ├── index, create, edit, show
│   └── transactions/create, edit
├── voucher/
│   ├── index, create, edit, show
│   └── transactions/create
├── aksesoris/
│   ├── index, create, edit, show
│   └── transactions/create
├── pengeluaran/
│   ├── index, create, edit
├── reports/
│   ├── index, penjualan, stok
└── layouts/app.blade.php
```

---

## ⚠️ Known Gotchas / Debugging Tips

1. **Saldo Awal Calculation**: `getSaldoAwalDompet()` looks at yesterday's `DailySummary` first, falls back to calculating from transactions. If historical data is wrong, recalc range.

2. **DailySummary Recalculation**: Always triggered after mutations. If reports look wrong → run `DailySummaryService->recalculateRange()`.

3. **Saldo Delta (Override)**: `saldo_delta` on `DompetPulsa` is a **delta from formula**, not absolute value. `sisa_saldo_efektif = formula + delta`.

4. **Adjustments**: Stored in separate table `dompet_pulsa_adjustments`, affect `adjustment_sum` accessor.

4. **Voucher `nilai` column**: Removed in migration `2026_09_08_152513_remove_nilai_from_vouchers_table.php` - no longer used.

5. **Aksesoris Transaction**: `harga_jual`, `total_penjualan`, `laba` are **nullable** for `jenis=pembelian`.

6. **Pengeluaran Karyawan**: `karyawan_nama` only used when `kategori=gaji`.

7. **Date Filtering**: All transaction queries use `whereDate('tanggal', $date)` or `whereBetween('tanggal', [...])`.

---

## 🛠️ Common Debugging Commands

```bash
# Recalculate summaries for date range
php artisan tinker --execute "App\Services\DailySummaryService::recalculateRange(now()->subDays(30), now())"

# Check specific date summary
php artisan tinker --execute "App\Models\DailySummary::where('tanggal', '2026-09-19')->first()"

# Check dompet balances
php artisan tinker --execute "App\Models\DompetPulsa::with('transactions')->get()->each(fn(d) => dump(d->nama, d->sisa_saldo_efektif))"

# Run tests
php artisan test --compact

# Format code
vendor/bin/pint --dirty --format agent
```

---

## 🔐 Auth & Middleware
- All app routes protected by `auth` + `verified` middleware
- Standard Laravel Breeze auth scaffolding
- Profile management at `/profile`

---

## 📦 Dependencies (composer.json key packages)
- `laravel/framework` ^11.x
- `pestphp/pest` ^3.x (testing)
- `laravel/pint` (code style)
- `spatie/laravel-activitylog` (if used - check composer.json)

---

*Generated for AI reference - keep updated as codebase evolves*