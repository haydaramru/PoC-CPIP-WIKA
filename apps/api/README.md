# CPIP Backend – Setup Guide

## Tech Stack

- Laravel 12
- PostgreSQL
- phpoffice/phpspreadsheet (parsing Excel)

---

## 1. Install Dependencies

```bash
composer require phpoffice/phpspreadsheet
```

---

## 2. Konfigurasi `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cpip_db
DB_USERNAME=postgres
DB_PASSWORD=your_password

FRONTEND_URL=http://localhost:3000
```

---

## 3. Setup Database

```bash
# Buat database di PostgreSQL terlebih dahulu
createdb poc_cpip_db

# Jalankan migration
php artisan migrate

# Seed dummy data (5 project dari brief)
php artisan db:seed
```

---

## 4. Jalankan Server

```bash
php artisan serve
# → http://127.0.0.1:8000
```

---

## 5. API Endpoints

| Method | URL                     | Fungsi         |
| ------ | ----------------------- | -------------- |
| GET    | `/api/projects`         | List project   |
| GET    | `/api/projects/summary` | Data dashboard |
| GET    | `/api/projects/{id}`    | Detail project |
| POST   | `/api/projects`         | Create manual  |
| POST   | `/api/projects/upload`  | Upload Excel   |
| PUT    | `/api/projects/{id}`    | Update project |
| DELETE | `/api/projects/{id}`    | Hapus project  |

### Query Params (GET /api/projects)

```
?division=Infrastructure
?sort_by=cpi&sort_dir=asc
?min_contract=500
?status=critical
```

---

## 6. Format Excel Upload

File: `.xlsx` atau `.xls`, max 5MB.

Baris pertama **wajib** header (nama kolom):

| Kolom            | Wajib | Keterangan                       |
| ---------------- | ----- | -------------------------------- |
| project_code     | ✅    | Unik, max 20 karakter            |
| project_name     | ✅    | Nama project                     |
| division         | ✅    | `Infrastructure` atau `Building` |
| owner            | ❌    | Pemilik project                  |
| contract_value   | ✅    | Nilai kontrak (Juta)             |
| planned_cost     | ✅    | Rencana biaya (Juta)             |
| actual_cost      | ✅    | Biaya aktual (Juta)              |
| planned_duration | ✅    | Durasi rencana (bulan)           |
| actual_duration  | ✅    | Durasi aktual (bulan)            |
| progress_pct     | ❌    | Progress % (default 100)         |

> ⚠️ Jika `project_code` sudah ada di DB → data akan di-**update** (bukan duplikat).

---

## 7. KPI Formula

```
CPI = planned_cost / actual_cost
SPI = planned_duration / actual_duration

Status:
  good     → CPI >= 1 DAN SPI >= 1        (hijau)
  warning  → salah satu < 1               (kuning)
  critical → CPI < 0.9 ATAU SPI < 0.9    (merah)
```

KPI **dihitung otomatis** setiap kali data disimpan (insert/update).

---

## 8. Struktur File

```
app/
├── Http/
│   ├── Controllers/
│   │   └── ProjectController.php      ← semua endpoint
│   └── Requests/
│       ├── ProjectRequest.php         ← validasi create/update
│       └── UploadExcelRequest.php     ← validasi upload
├── Imports/
│   └── ProjectImport.php              ← parsing Excel → DB
├── Models/
│   └── Project.php                    ← model + auto-calculate KPI
└── Services/
    └── KpiCalculatorService.php       ← formula CPI, SPI, status

database/
├── migrations/
│   └── ..._create_projects_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── ProjectSeeder.php              ← 5 dummy project

routes/
└── api.php
```
