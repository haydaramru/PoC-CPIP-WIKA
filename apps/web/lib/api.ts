import axios from 'axios';
import type {
  ProjectListResponse,
  SummaryResponse,
  Project,
  UploadResponse,
  IngestionFileListResponse,
  InsightResponse,
  IngestionLogResponse,
} from '@/types/project';

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
});

// ============================================================
// Projects
// ============================================================

export type ProjectFilters = {
  division?: string;
  status?: string;
  year?: number;
  sort_by?: 'cpi' | 'spi' | 'contract_value' | 'project_name';
  sort_dir?: 'asc' | 'desc';
  min_contract?: number;
  max_contract?: number;
};

export const projectApi = {
  list: (filters: ProjectFilters = {}): Promise<ProjectListResponse> =>
    api.get('/projects', { params: filters }).then(r => r.data),

  summary: (): Promise<SummaryResponse> =>
    api.get('/projects/summary').then(r => r.data),

  detail: (id: number): Promise<{ data: Project }> =>
    api.get(`/projects/${id}`).then(r => r.data),

  insight: (id: number): Promise<InsightResponse> =>
    api.get(`/projects/${id}/insight`).then(r => r.data),

  upload: async (files: File | File[]): Promise<UploadResponse> => {
  const form = new FormData();
  const fileArray = Array.isArray(files) ? files : [files];
  fileArray.forEach(file => form.append('files[]', file));

  const laravelUrl = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000';
  const res = await fetch(`${laravelUrl}/api/projects/upload`, {
    method: 'POST',
    headers: { Accept: 'application/json' },
    body: form,
  });

  const data = await res.json();

  if (!res.ok) {
    const err = new Error(data?.message ?? 'Upload gagal') as any;
    err.responseData = data;
    throw err;
  }

  return data;
},

  delete: (id: number): Promise<{ message: string }> =>
    api.delete(`/projects/${id}`).then(r => r.data),
};
export const ingestionApi = {
  list: (perPage = 15): Promise<IngestionFileListResponse> =>
    api.get('/ingestion-files', { params: { per_page: perPage } }).then(r => r.data),

  downloadUrl: (id: number): string => `/api/ingestion-files/${id}/download`,

  reprocess: (id: number): Promise<UploadResponse> =>
    api.post(`/ingestion-files/${id}/reprocess`).then(r => r.data),

  ingestionLog: (perPage = 15): Promise<IngestionLogResponse> =>
    api.get('/ingestion-log', { params: { per_page: perPage } }).then(r => r.data),
};

export default api;