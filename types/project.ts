export type ProjectStatus = 'good' | 'warning' | 'critical';
export type Division = 'Infrastructure' | 'Building';
export type IngestionStatus = 'pending' | 'processing' | 'success' | 'failed' | 'partial';

export interface Project {
  id: number;
  project_code: string;
  project_name: string;
  division: Division;
  owner: string | null;
  contract_value: string;
  planned_cost: string;
  actual_cost: string;
  planned_duration: number;
  actual_duration: number;
  progress_pct: string;
  project_year: number;
  cpi: string;
  spi: string;
  status: ProjectStatus;
  ingestion_file_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface ProjectListMeta {
  total: number;
  overbudget_count: number;
  delay_count: number;
  overbudget_pct: number;
  delay_pct: number;
  available_years: number[];
  active_year: number | null;
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

export interface FileUploadResult {
  file_id: number;
  file_name: string;
  status: IngestionStatus;
  total_rows: number;
  imported: number;
  skipped: number;
  errors: string[];
}

export interface UploadResponse {
  success: boolean;
  message: string;
  results: FileUploadResult[];
}

export interface IngestionFile {
  id: number;
  original_name: string;
  stored_path: string;
  disk: string;
  status: IngestionStatus;
  total_rows: number;
  imported_rows: number;
  skipped_rows: number;
  errors: string[] | null;
  processed_at: string | null;
  projects_count: number;
  created_at: string;
  updated_at: string;
}

export interface IngestionFileListResponse {
  data: IngestionFile[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export type DashboardFilters = {
  division: string;
  contractRange: string;
  year: string;
};

export type InsightLevel = 'info' | 'warning' | 'critical';

export interface InsightBullet {
  level: InsightLevel;
  text: string;
}

export interface InsightResponse {
  bullets: InsightBullet[];
  summary: {
    level: InsightLevel;
    text: string;
  };
}

export interface IngestionLog {
  id: number;
  file_name: string;
  total_rows: number;
  success_rows: number;
  failed_rows: number;
  status: 'SUCCESS' | 'FAILED' | 'PARTIAL' | 'PENDING' | 'PROCESSING';
  processed_at: string | null;
}

export interface IngestionLogResponse {
  data: IngestionLog[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}