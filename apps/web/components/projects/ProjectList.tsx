
'use client';

import { useState, useEffect, useMemo } from 'react';
import { projectApi } from '@/lib/api';
import type { Project } from '@/types/project';
import ProjectListHeader from '@/components/projects/list/ProjectListHeader';
import ProjectTable      from '@/components/projects/list/ProjectTable';

type SortField = 'cpi' | 'spi' | 'contract_value' | 'project_name';
type SortDir   = 'asc' | 'desc';

export default function ProjectList() {
  const [projects,       setProjects]       = useState<Project[]>([]);
  const [loading,        setLoading]        = useState(true);
  const [error,          setError]          = useState('');
  const [availableYears, setAvailableYears] = useState<number[]>([]);

  const [activeYear, setActiveYear] = useState<number | null>(null);
  const [search,     setSearch]     = useState('');
  const [division,   setDivision]   = useState('');
  const [sortBy,     setSortBy]     = useState<SortField>('cpi');
  const [sortDir,    setSortDir]    = useState<SortDir>('asc');

  useEffect(() => {
    setLoading(true);

    const yearParam = division ? undefined : (activeYear ?? undefined);

    projectApi.list({ year: yearParam })
      .then(res => {
        setProjects(res.data);
        setAvailableYears(res.meta.available_years ?? []);
      })
      .catch(() => setError('Gagal memuat data. Pastikan Laravel server berjalan.'))
      .finally(() => setLoading(false));
  }, [activeYear, division]);  // re-fetch saat year atau division berubah

  function handleSort(field: SortField) {
    if (sortBy === field) {
      setSortDir(d => d === 'asc' ? 'desc' : 'asc');
    } else {
      setSortBy(field);
      setSortDir('asc');
    }
  }

  const displayedProjects = useMemo(() => {
    let result = [...projects];

    if (search) {
      const q = search.toLowerCase();
      result = result.filter(p =>
        p.project_name.toLowerCase().includes(q) ||
        p.project_code.toLowerCase().includes(q)
      );
    }

    if (division) {
      result = result.filter(p => p.division === division);
    }

    result.sort((a, b) => {
      let valA: number | string;
      let valB: number | string;

      if (sortBy === 'cpi' || sortBy === 'spi' || sortBy === 'contract_value') {
        valA = parseFloat(String(a[sortBy]));
        valB = parseFloat(String(b[sortBy]));
      } else {
        valA = a.project_name.toLowerCase();
        valB = b.project_name.toLowerCase();
      }

      if (valA < valB) return sortDir === 'asc' ? -1 : 1;
      if (valA > valB) return sortDir === 'asc' ?  1 : -1;
      return 0;
    });

    return result;
  }, [projects, search, division, sortBy, sortDir]);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-24 gap-3 text-gray-400">
        <div className="w-6 h-6 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
        Memuat data...
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

  return (
    <div
      className="flex flex-col bg-white"
      style={{ width: '1203px', height: '903px', padding: '18px 32px', gap: '18px', opacity: 1 }}
    >
      <ProjectListHeader
        search={search}
        division={division}
        totalShown={displayedProjects.length}
        totalAll={projects.length}
        availableYears={availableYears}
        activeYear={division ? null : activeYear}
        onSearchChange={setSearch}
        onDivisionChange={(val) => {
          setDivision(val);
          if (val) setActiveYear(null);
        }}
        onYearChange={setActiveYear}
      />
      <ProjectTable
        projects={displayedProjects}
        sortBy={sortBy}
        sortDir={sortDir}
        onSort={handleSort}
      />
    </div>
  );
}