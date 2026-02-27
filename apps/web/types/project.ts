// Tipe data project dari Laravel API

export type ProjectStatus = 'good' | 'warning' | 'critical';
export type Division = 'Infrastructure' | 'Building';

export interface Project {
  id: number;
  project_code: string;
  project_name: string;
  division: Division;
  owner: string | null;
  contract_value: string;   // Laravel decimal → string
  planned_cost: string;
  actual_cost: string;
  planned_duration: number;
  actual_duration: number;
  progress_pct: string;
  cpi: string;              // decimal:4 dari Laravel
  spi: string;
  status: ProjectStatus;
  created_at: string;
  updated_at: string;
}

export interface ProjectListMeta {
  total: number;
  overbudget_count: number;
  delay_count: number;
  overbudget_pct: number;
  delay_pct: number;
}

export interface ProjectListResponse {
  data: Project[];
  meta: ProjectListMeta;
}

export interface DivisionSummary {
  total: number;
  avg_cpi: number;
  avg_spi: number;
  overbudget_count: number;
  delay_count: number;
}

export interface SummaryResponse {
  total_projects: number;
  avg_cpi: number;
  avg_spi: number;
  overbudget_count: number;
  delay_count: number;
  overbudget_pct: number;
  delay_pct: number;
  by_division: Record<Division, DivisionSummary>;
  status_breakdown: Record<ProjectStatus, number>;
}

export interface UploadResponse {
  success: boolean;
  message: string;
  imported: number;
  skipped: number;
  errors: string[];
}

export type DashboardFilters = {
  division: string;
  contractRange: string;
  year: string;
};