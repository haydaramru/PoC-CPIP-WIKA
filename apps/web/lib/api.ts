import axios from 'axios';
import type {
  ProjectListResponse,
  SummaryResponse,
  Project,
  UploadResponse,
} from '@/types/project';

// Axios instance — base URL di-handle oleh next.config.js rewrite
// Jadi semua request ke /api/... otomatis di-proxy ke Laravel
const api = axios.create({
  baseURL: '/api',
  headers: { 'Accept': 'application/json' },
});

// ============================================================
// Projects
// ============================================================

export type ProjectFilters = {
  division?: string;
  status?: string;
  sort_by?: 'cpi' | 'spi' | 'contract_value' | 'project_name';
  sort_dir?: 'asc' | 'desc';
  min_contract?: number;
  max_contract?: number;
};

export const projectApi = {
  /** GET /api/projects — list dengan filter & sort */
  list: (filters: ProjectFilters = {}): Promise<ProjectListResponse> =>
    api.get('/projects', { params: filters }).then(r => r.data),

  /** GET /api/projects/summary — data agregat dashboard */
  summary: (): Promise<SummaryResponse> =>
    api.get('/projects/summary').then(r => r.data),

  /** GET /api/projects/{id} */
  detail: (id: number): Promise<{ data: Project }> =>
    api.get(`/projects/${id}`).then(r => r.data),

  /** POST /api/projects/upload — upload file Excel */
  upload: (file: File): Promise<UploadResponse> => {
    const form = new FormData();
    form.append('file', file);
    return api.post('/projects/upload', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }).then(r => r.data);
  },

  /** DELETE /api/projects/{id} */
  delete: (id: number): Promise<{ message: string }> =>
    api.delete(`/projects/${id}`).then(r => r.data),
};

export default api;