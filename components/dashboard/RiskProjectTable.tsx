'use client';

import { useState, useMemo } from 'react';
import { Search, ChevronDown, ArrowUpDown } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { formatCurrency, formatKpi, kpiColor } from '@/lib/utils';
import type { Project } from '@/types/project';

type Props = {
  projects: Project[];
};

export default function RiskProjectTable({ projects }: Props) {
  const router = useRouter();
  
  const [searchTerm, setSearchTerm] = useState('');
  const [divisionFilter, setDivisionFilter] = useState('All');
  const [sortConfig, setSortConfig] = useState<{ key: 'cpi' | 'spi'; direction: 'asc' | 'desc' } | null>({
    key: 'cpi',
    direction: 'asc' 
  });

  const filteredRiskProjects = useMemo(() => {
    return projects
      .filter(p => p.status !== 'good')
      .filter(p => divisionFilter === 'All' || p.division === divisionFilter)
      .filter(p => p.project_name.toLowerCase().includes(searchTerm.toLowerCase()))
      .sort((a, b) => {
        if (!sortConfig) return 0;
        const valA = parseFloat(a[sortConfig.key]);
        const valB = parseFloat(b[sortConfig.key]);
        return sortConfig.direction === 'asc' ? valA - valB : valB - valA;
      });
  }, [projects, searchTerm, divisionFilter, sortConfig]);

  const handleSort = (key: 'cpi' | 'spi') => {
    setSortConfig(prev => ({
      key,
      direction: prev?.key === key && prev.direction === 'asc' ? 'desc' : 'asc'
    }));
  };

  return (
    <div className="flex flex-col gap-6 bg-white" style={{ width: '1203px', padding: '24px 32px' }}>
      
      <div className="flex items-center justify-between" style={{ width: '1139px', height: '38px' }}>
        <h2 className="text-[22px] font-bold text-[#1B1C1F] tracking-tight">
          Risk Project List
        </h2>
        
        <div className="flex items-center gap-3 h-full">
          <div className="relative h-full">
            <input 
              type="text" 
              placeholder="Search project here"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-4 pr-10 h-full bg-white border border-gray-200 rounded-lg text-[13px] w-85 focus:outline-none focus:border-gray-400 placeholder:text-gray-400 transition-all"
            />
            <Search className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" size={16} />
          </div>

          <div className="relative h-full">
            <select 
              value={divisionFilter}
              onChange={(e) => setDivisionFilter(e.target.value)}
              className="appearance-none pl-4 pr-10 h-full bg-white border border-gray-200 rounded-lg text-[13px] text-[#1B1C1F] font-medium cursor-pointer focus:outline-none hover:bg-gray-50 transition-all"
            >
              <option value="All">Division</option>
              <option value="Infrastructure">Infrastructure</option>
              <option value="Building">Building</option>
            </select>
            <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" size={14} />
          </div>

          <button 
            onClick={() => router.push('/projects')}
            className="text-[14px] font-semibold text-primary-blue hover:text-blue-800 transition-colors ml-2"
          >
            View All
          </button>
        </div>
      </div>

      <div 
        className="overflow-hidden bg-white border border-gray-100 rounded-xl shadow-sm" 
        style={{ width: '1139px', minHeight: '267px' }}
      >
        <table className="w-full border-collapse">
          <thead>
            <tr className="bg-[#F9FAFB] border-b border-gray-100">
              <th className="px-6 py-4 text-left text-[12px] font-bold text-gray-500 uppercase tracking-wider w-15">#</th>
              <th className="px-4 py-4 text-left text-[12px] font-bold text-gray-500 uppercase tracking-wider">Project Name</th>
              <th className="px-4 py-4 text-left text-[12px] font-bold text-gray-500 uppercase tracking-wider">Division</th>
              <th className="px-4 py-4 text-[12px] font-bold text-gray-500 uppercase tracking-wider text-right">Contract Value</th>
              
              <th className="px-4 py-4 text-left text-[12px] font-bold text-gray-500 uppercase tracking-wider">
                <div 
                  className="flex items-center gap-1 cursor-pointer hover:text-gray-800 transition-colors justify-end"
                  onClick={() => handleSort('cpi')}
                >
                  CPI <ArrowUpDown size={14} className="text-gray-400" />
                </div>
              </th>

              <th className="px-4 py-4 text-left text-[12px] font-bold text-gray-500 uppercase tracking-wider">
                <div 
                  className="flex items-center gap-1 cursor-pointer hover:text-gray-800 transition-colors justify-end"
                  onClick={() => handleSort('spi')}
                >
                  SPI <ArrowUpDown size={14} className="text-gray-400" />
                </div>
              </th>
              
              <th className="px-8 py-4 text-left text-[12px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {filteredRiskProjects.map((project, index) => (
              <tr 
                key={project.id} 
                className="hover:bg-gray-50/50 transition-colors cursor-pointer"
                onClick={() => router.push(`/projects/${project.id}`)}
              >
                <td className="px-6 py-5 text-[14px] text-gray-600 font-medium">{index + 1}</td>
                <td className="px-4 py-5 text-[14px] font-semibold text-[#1B1C1F]">
                  {project.project_name}
                </td>
                <td className="px-4 py-5 text-[14px] text-gray-700">
                  {project.division}
                </td>
                <td className="px-4 py-5 text-[14px] text-gray-700 font-medium text-right">
                  {formatCurrency(project.contract_value)}
                </td>
                
                <td className={`px-4 py-5 text-[14px] font-bold text-right ${(project.cpi)}`}>
                  {formatKpi(project.cpi)}
                </td>
                <td className={`px-4 py-5 text-[14px] font-bold text-right ${(project.spi)}`}>
                  {formatKpi(project.spi)}
                </td>

                <td className="px-8 py-5">
                  {project.status === 'critical' ? (
                    <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#FFF1F1] border border-[#FFE4E4] w-fit">
                      <div className="w-2 h-2 rounded-full bg-[#C53030]" />
                      <span className="text-[#C53030] text-[12px] font-bold">Critical</span>
                    </div>
                  ) : (
                    <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#FFF9E6] border border-[#FEF3C7] w-fit">
                      <div className="w-2 h-2 rounded-full bg-[#D97706]" />
                      <span className="text-[#D97706] text-[12px] font-bold">At Risk</span>
                    </div>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {filteredRiskProjects.length === 0 && (
          <div className="py-12 text-center text-gray-400 text-[14px]">
            No projects found.
          </div>
        )}
      </div>
    </div>
  );
}