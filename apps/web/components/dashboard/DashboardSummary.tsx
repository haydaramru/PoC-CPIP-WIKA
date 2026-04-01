'use client';

import { useState, useEffect } from 'react';
import { projectApi } from '@/lib/api';
import type { SummaryResponse, Project, Division } from '@/types/project';
import type { DashboardFilters } from '@/types/project';
import QuickFilterPreview from '@/components/dashboard/QuickFilterPreview';
import KpiCards from '@/components/dashboard/KpiCards';
import DivisionChart from '@/components/dashboard/DivisionChart';
import TrendHarsatUtama from '@/components/dashboard/TrendHarsatUtama';
import SebaranSBUChart from '@/components/dashboard/SebaranSBUChart';
import ParetoTables from '@/components/dashboard/ParetoTables';
import RiskProjectTable from '@/components/dashboard/RiskProjectTable';

// ── Mock data for demo when backend is empty ──
const MOCK_SUMMARY: SummaryResponse = {
  total_projects: 210,
  avg_cpi: 0.94,
  avg_spi: 0.89,
  overbudget_count: 18,
  delay_count: 22,
  overbudget_pct: 8.6,
  delay_pct: 10.5,
  by_division: {
    Infrastructure: { total: 120, avg_cpi: 0.92, avg_spi: 0.87, overbudget_count: 12, delay_count: 15 },
    Building:       { total: 90,  avg_cpi: 0.98, avg_spi: 0.91, overbudget_count: 6,  delay_count: 7  },
  },
  status_breakdown: { good: 150, warning: 42, critical: 18 },
};

const MOCK_PROJECTS: Project[] = [
  { id: 1, project_code: 'P001', project_name: 'Tol Semarang Seksi 3',      division: 'Infrastructure', owner: 'BUMN',    contract_value: '850',  planned_cost: '800',  actual_cost: '970',  planned_duration: 24, actual_duration: 30, progress_pct: '75', project_year: 2024, cpi: '0.82', spi: '0.75', status: 'critical', ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 2, project_code: 'P002', project_name: 'Bendungan Citarum',          division: 'Infrastructure', owner: 'Swasta',  contract_value: '620',  planned_cost: '600',  actual_cost: '630',  planned_duration: 18, actual_duration: 17, progress_pct: '90', project_year: 2024, cpi: '0.95', spi: '1.02', status: 'warning',  ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 3, project_code: 'P003', project_name: 'RS Regional Surabaya',       division: 'Building',       owner: 'BUMN',    contract_value: '450',  planned_cost: '430',  actual_cost: '425',  planned_duration: 12, actual_duration: 11, progress_pct: '95', project_year: 2025, cpi: '1.01', spi: '1.05', status: 'good',     ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 4, project_code: 'P004', project_name: 'Gedung Perkantoran BUMN',    division: 'Building',       owner: 'BUMN',    contract_value: '300',  planned_cost: '280',  actual_cost: '290',  planned_duration: 15, actual_duration: 16, progress_pct: '80', project_year: 2025, cpi: '0.97', spi: '0.89', status: 'warning',  ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 5, project_code: 'P005', project_name: 'Jembatan Kalimantan',        division: 'Infrastructure', owner: 'Swasta',  contract_value: '700',  planned_cost: '680',  actual_cost: '840',  planned_duration: 20, actual_duration: 25, progress_pct: '60', project_year: 2024, cpi: '0.81', spi: '0.76', status: 'critical', ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 6, project_code: 'P006', project_name: 'Jembatan Kalimantan',        division: 'Infrastructure', owner: 'Swasta',  contract_value: '700',  planned_cost: '680',  actual_cost: '840',  planned_duration: 20, actual_duration: 25, progress_pct: '60', project_year: 2024, cpi: '0.81', spi: '0.76', status: 'critical', ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 7, project_code: 'P007', project_name: 'Jembatan Kalimantan',        division: 'Infrastructure', owner: 'Swasta',  contract_value: '700',  planned_cost: '680',  actual_cost: '840',  planned_duration: 20, actual_duration: 25, progress_pct: '60', project_year: 2024, cpi: '0.81', spi: '0.76', status: 'critical', ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 8, project_code: 'P008', project_name: 'Jembatan Kalimantan',        division: 'Infrastructure', owner: 'Swasta',  contract_value: '700',  planned_cost: '680',  actual_cost: '840',  planned_duration: 20, actual_duration: 25, progress_pct: '60', project_year: 2024, cpi: '0.81', spi: '0.76', status: 'critical', ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 9, project_code: 'P009', project_name: 'RS Kasih Ibu Extension',     division: 'Building',       owner: 'Swasta',  contract_value: '380',  planned_cost: '350',  actual_cost: '360',  planned_duration: 10, actual_duration: 10, progress_pct: '88', project_year: 2025, cpi: '0.97', spi: '1.00', status: 'warning',  ingestion_file_id: null, created_at: '', updated_at: '' },
  { id: 10, project_code: 'P010', project_name: 'Flyover Makassar',          division: 'Infrastructure', owner: 'BUMN',    contract_value: '520',  planned_cost: '500',  actual_cost: '510',  planned_duration: 16, actual_duration: 17, progress_pct: '70', project_year: 2024, cpi: '0.98', spi: '0.94', status: 'warning',  ingestion_file_id: null, created_at: '', updated_at: '' },
];

export default function DashboardSummary() {
  const [summary,  setSummary]  = useState<SummaryResponse | null>(null);
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading,  setLoading]  = useState(true);

  const [filters, setFilters] = useState<DashboardFilters>({
    division: '',
    contractRange: '',
    year: '',
  });

  useEffect(() => {
    Promise.all([
      projectApi.summary(),
      projectApi.list(),
    ])
      .then(([summaryData, listData]) => {
        // Use real data if available, fallback to mock
        if (summaryData.total_projects > 0) {
          setSummary(summaryData);
          setProjects(listData.data);
        } else {
          setSummary(MOCK_SUMMARY);
          setProjects(MOCK_PROJECTS);
        }
      })
      .catch(() => {
        // API unreachable — use mock data so UI is still visible
        setSummary(MOCK_SUMMARY);
        setProjects(MOCK_PROJECTS);
      })
      .finally(() => setLoading(false));
  }, []);

  const filteredProjects = projects.filter(p => {
    if (filters.division && p.division !== filters.division) return false;
    if (filters.contractRange) {
      const val = parseFloat(p.contract_value);
      if (filters.contractRange === '0-500'   && val >= 500) return false;
      if (filters.contractRange === '500-999' && val < 500)  return false;
    }
    return true;
  });

  if (loading) {
    return (
      <div className="flex items-center justify-center py-24 gap-3 text-gray-400">
        <div className="w-6 h-6 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
        Memuat data dashboard...
      </div>
    );
  }

  if (!summary) return null;

  return (
    <div className="bg-[#F9FAFB] min-h-screen">
      <QuickFilterPreview />
      <KpiCards
        data={summary}
        filters={filters}
        onChange={setFilters}
      />
      <DivisionChart data={summary} />

      {/* Trend Harga Utama + Sebaran SBU side by side */}
      <div className="bg-white flex gap-8 w-full" style={{ padding: '18px 32px' }}>
        <TrendHarsatUtama />
        <SebaranSBUChart />
      </div>

      <ParetoTables />
      <RiskProjectTable projects={filteredProjects} />
    </div>
  );
}
