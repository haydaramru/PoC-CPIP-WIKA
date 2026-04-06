'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { projectApi } from '@/lib/api';
import type { SummaryResponse, Project } from '@/types/project';
import DashboardHeader from '@/components/layout/DynamicHeader';
import KpiCards from '@/components/dashboard/KpiCards';
import DivisionChart from '@/components/dashboard/DivisionChart';
import RiskProjectTable from '@/components/dashboard/RiskProjectTable';
import type { DashboardFilters } from '@/types/project';

export default function DashboardSummary() {
  const router = useRouter();

  const [summary,  setSummary]  = useState<SummaryResponse | null>(null);
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading,  setLoading]  = useState(true);
  const [error,    setError]    = useState('');

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
        setSummary(summaryData);
        setProjects(listData.data);
      })
      .catch(() => setError('Gagal memuat data. Pastikan Laravel server berjalan.'))
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

  if (error) {
    return (
      <div className="card border border-red-200 bg-red-50 text-red-700 text-sm p-5">
        {error}
      </div>
    );
  }

  if (!summary || summary.total_projects === 0) {
    return (
      <div className="card text-center py-16 space-y-3">
        <p className="text-gray-400 text-lg">Belum ada data project</p>
        <button onClick={() => router.push('/upload')} className="btn-primary">
          Upload Excel Sekarang
        </button>
      </div>
    );
  }

  return (
    <div className="bg-[#F9FAFB] min-h-screen">
      <KpiCards 
        data={summary} 
        filters={filters} 
        onChange={setFilters} 
      />
      
        <DivisionChart data={summary} />
        <RiskProjectTable projects={filteredProjects} />
    </div>
  );
}