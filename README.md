# CPIP Frontend – Setup Guide

## Tech Stack

- Next.js 14 (App Router)
- TypeScript
- Tailwind CSS
- Axios

---

## 1. Install Dependencies

```bash
npm install
```

---

## 2. Konfigurasi `.env.local`

File sudah dibuat, pastikan URL Laravel benar:

```env
NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000
```

---

## 3. Jalankan Dev Server

```bash
npm run dev
# → http://localhost:3000
```

Pastikan Laravel sudah berjalan di `:8000` sebelum menjalankan Next.js.

---

## 4. Struktur Folder

```
src/
├── app/                        ← Next.js App Router (halaman)
│   ├── layout.tsx              ← Root layout + Sidebar
│   ├── globals.css             ← Tailwind + custom class
│   ├── page.tsx                ← Halaman 1: Dashboard Summary
│   ├── projects/
│   │   ├── page.tsx            ← Halaman 3: Project List
│   │   └── [id]/page.tsx       ← Halaman 2: Project Detail
│   └── upload/
│       └── page.tsx            ← Halaman Upload Excel
│
├── components/                 ← Komponen UI
│   ├── layout/
│   │   └── Sidebar.tsx         ← Navigasi sidebar
│   ├── dashboard/
│   │   └── DashboardSummary.tsx
│   ├── projects/
│   │   ├── ProjectList.tsx
│   │   └── ProjectDetail.tsx
│   └── upload/
│       └── UploadExcel.tsx
│
├── lib/
│   ├── api.ts                  ← Semua pemanggilan API Laravel
│   └── utils.ts                ← Helper: format, warna KPI, status
│
└── types/
    └── project.ts              ← TypeScript types
```

---

## 5. Cara Kerja API Proxy

Request dari browser TIDAK langsung ke Laravel.
Next.js menjadi perantara via `next.config.js` rewrite:

```
Browser → GET /api/projects
       → Next.js rewrite
       → GET http://127.0.0.1:8000/api/projects (Laravel)
```

Keuntungan: tidak ada CORS issue, URL di frontend selalu `/api/...`.

---

## 6. Halaman yang Akan Dibangun

| Route            | Komponen         | Status  |
| ---------------- | ---------------- | ------- |
| `/`              | DashboardSummary | 🔜 Next |
| `/projects`      | ProjectList      | 🔜 Next |
| `/projects/[id]` | ProjectDetail    | 🔜 Next |
| `/upload`        | UploadExcel      | 🔜 Next |
