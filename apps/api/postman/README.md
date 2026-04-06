# Postman

File yang bisa di-import:

- `postman/CPIP API.postman_collection.json`
- `postman/CPIP Local.postman_environment.json`

Cara pakai singkat:

1. Import collection dan environment ke Postman.
2. Pilih environment `CPIP Local`.
3. Isi variable `token` dengan Sanctum token yang valid.
4. Sesuaikan `projectId`, `periodId`, `riskId`, dan variable lain sesuai data di database.

Catatan:

- Semua endpoint di `/api` saat ini diproteksi `auth:sanctum`.
- Collection ini belum menyertakan endpoint login karena route auth/token generator belum tersedia di API.
- Request upload memakai key `files` dan tipe `form-data`.
