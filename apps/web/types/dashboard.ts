import axios from "axios";

// ─── Axios instance khusus Dashboard ─────────────────────────────────────────
// Sesuaikan baseURL dengan konfigurasi api instance yang sudah ada di project
const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Tambahkan Authorization token jika tersedia (konsisten dengan projectApi)
api.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = localStorage.getItem("token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// ─── Types ────────────────────────────────────────────────────────────────────

export interface DashboardProject {
  id: number;
  project_code: string;
  project_name: string;
  division: string | null;
  sbu: string | null;
  owner: string | null;
  profit_center: string | null;
  type_of_contract: string | null;
  contract_type: string | null;
  payment_method: string | null;
  partnership: string | null;
  partner_name: string | null;
  consultant_name: string | null;
  funding_source: string | null;
  location: string | null;
  contract_value: string;
  planned_cost: string;
  actual_cost: string;
  hpp: string | null;
  planned_duration: number | null;
  actual_duration: number | null;
  progress_pct: string;
  gross_profit_pct: string;
  cpi: string;
  spi: string;
  status: string;
  project_year: number;
  start_date: string | null;
  created_at: string;
  updated_at: string;
  ingestion_file_id: number | null;
  delivery_budget_status: string;
}

export interface DashboardSummaryData {
  total_projects: number;
  avg_cpi: number;
  avg_spi: number;
  overbudget_count: number;
  delay_count: number;
  overbudget_pct: number;
  delay_pct: number;
  by_division: Record<
    string,
    {
      total: number;
      avg_cpi: number;
      avg_spi: number;
      overbudget_count: number;
      delay_count: number;
    }
  >;
  status_breakdown: Record<string, number>;
  profitability: { name: string; pct: string }[];
  overrun: { name: string; pct: string }[];
}

export interface DashboardFilterOptions {
  division: string[];
  sbu: string[];
  owner: string[];
  contract_type: string[];
  payment_method: string[];
  partnership: string[];
  funding_source: string[];
  location: string[];
  year: number[];
  consultant: string[];
  profit_center: string[];
  type_of_contract: string[];
  partner_name: string[];
}

export interface DashboardSbuItem {
  label: string;
  value: number;
}

export interface DashboardHarsatTrend {
  years: string[];
  categories: { key: string; label: string; color: string }[];
  data: Record<string, number[]>;
}

export interface DashboardProjectsMeta {
  total: number;
  overbudget_count: number;
  delay_count: number;
  overbudget_pct: number;
  delay_pct: number;
  available_years: number[];
  active_year: number;
}

export interface DashboardApiResponse {
  generated_at: string;
  filters: unknown[];
  summary: DashboardSummaryData;
  projects: {
    data: DashboardProject[];
    meta: DashboardProjectsMeta;
  };
  filter_options: DashboardFilterOptions;
  sbu_distribution: DashboardSbuItem[];
  harsat_trend: DashboardHarsatTrend;
}
