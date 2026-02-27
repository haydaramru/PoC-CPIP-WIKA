'use client';

import { LucideIcon, LayoutPanelLeft, Wallet, CalendarDays, BarChart3, Info, TrendingUp, TrendingDown, ChevronDown } from 'lucide-react';
import { formatKpi } from '@/lib/utils';
import type { SummaryResponse, DashboardFilters } from '@/types/project';

const DIVISIONS = [{ v: '', l: 'Division' }, { v: 'Infrastructure', l: 'Infrastructure' }, { v: 'Building', l: 'Building' }];
const CONTRACTS = [{ v: '', l: 'Contract Value' }, { v: '0-500', l: '< 500 M' }, { v: '500-999', l: '≥ 500 M' }];
const YEARS = [{ v: '', l: 'Year' }, { v: '2024', l: '2024' }, { v: '2025', l: '2025' }, { v: '2026', l: '2026' }];

type Props = {
  data: SummaryResponse;
  filters: DashboardFilters;
  onChange: (filters: DashboardFilters) => void;
};

export default function KpiCards({ data, filters, onChange }: Props) {
  const updateFilter = (key: keyof DashboardFilters, value: string) => {
    onChange({ ...filters, [key]: value });
  };

  return (
    <div className="flex flex-col bg-white" style={{ width: '1203px', padding: '18px 32px' }}>
      {/* ── OVERVIEW & FILTERS ────────────────────────────────────────── */}
      <div className="flex items-center justify-between mb-6" style={{ width: '1139px' }}>
        <h2 className="text-[18px] font-bold text-[#1B1C1F]">Overview</h2>
        
        <div className="flex items-center gap-3">
          <div 
            className="flex items-center" 
            style={{ 
              width: '292px', 
              height: '27px', 
              gap: '16px', // Jarak antar dropdown tetap 16px
              opacity: 1 
            }}
          >
            {/* Division: Lebih pendek */}
            <div className="w-21.25 h-full">
              <FilterSelect 
                value={filters.division} 
                options={DIVISIONS} 
                onChange={(v) => updateFilter('division', v)} 
              />
            </div>

            {/* Contract Value: Lebih lebar agar tidak terpotong */}
            <div className="w-30 h-full">
              <FilterSelect 
                value={filters.contractRange} 
                options={CONTRACTS} 
                onChange={(v) => updateFilter('contractRange', v)} 
              />
            </div>

            {/* Year: Paling pendek */}
            <div className="w-17.75 h-full">
              <FilterSelect 
                value={filters.year} 
                options={YEARS} 
                onChange={(v) => updateFilter('year', v)} 
              />
            </div>
          </div>

          {/* Reset Button (Di luar kontainer 292px agar tidak mengganggu layout dropdown) */}
          {(filters.division || filters.contractRange || filters.year) && (
            <button 
              onClick={() => onChange({ division: '', contractRange: '', year: '' })}
              className="text-[11px] text-blue-600 font-bold hover:underline ml-1"
            >
              Reset
            </button>
          )}
        </div>
      </div>

      {/* ── KPI CARDS ROW ─────────────────────────────────────────────── */}
      <div className="flex items-center justify-between" style={{ width: '1139px' }}>
        <KpiCard label="Total Projects" value={data.total_projects} trendValue={3} icon={LayoutPanelLeft} />
        <KpiCard label="Average CPI" value={formatKpi(data.avg_cpi)} trendValue={-0.05} icon={Wallet} />
        <KpiCard label="Average SPI" value={formatKpi(data.avg_spi)} trendValue={-0.02} icon={CalendarDays} />
        <KpiCard label="% Project Overbudget" value={`${data.overbudget_pct}%`} trendValue={4} icon={BarChart3} isPositiveGood={false} />
      </div>
    </div>
  );
}

// Sub-komponen Dropdown bergaya minimalis
function FilterSelect({ value, options, onChange }: { value: string, options: any[], onChange: (v: string) => void }) {
  return (
    <div className="relative flex-1 h-full">
      <select
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="appearance-none w-full h-full bg-white border border-gray-200 rounded-md px-2 pr-6 text-[11px] font-medium text-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer"
      >
        {options.map(opt => (
          <option key={opt.v} value={opt.v}>
            {opt.l}
          </option>
        ))}
      </select>
      <ChevronDown 
        size={10} 
        className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" 
      />
    </div>
  );
}

// Sub-komponen Kartu sesuai Gambar
function KpiCard({ label, value, trendValue, icon: Icon, isPositiveGood = true }: any) {
  const isPositive = trendValue >= 0;
  const isGoodTrend = isPositiveGood ? isPositive : !isPositive;
  const trendColor = isGoodTrend ? 'text-green-600' : 'text-red-600';
  const TrendIcon = isPositive ? TrendingUp : TrendingDown;

  return (
    <div className="flex flex-col bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-all" style={{ width: '274px', height: '147px' }}>
      <div className="flex items-start justify-between mb-2">
        {/* Container Icon & Label (238x26px) */}
        <div 
          className="flex items-center"
          style={{
            width: '238px',
            height: '26px',
            justifyContent: 'space-between',
            opacity: 1
          }}
        >
          <div className="flex items-center gap-3">
            {/* Icon Box yang disesuaikan dengan tinggi 26px */}
            <div className="flex items-center justify-center bg-primary-blue rounded-md w-6.5 h-6.5 shrink-0">
              <Icon size={14} className="text-white" />
            </div>
            
            {/* Label Teks */}
            <span className="text-[13px] font-semibold text-[#1B1C1F] truncate">
              {label}
            </span>
          </div>

          {/* Info Icon diposisikan di ujung kanan container 238px */}
          <Info size={14} className="text-[#1B1C1F] cursor-help shrink-0" />
        </div>
      </div>
      <div className="mt-1">
        <h2 className="text-[32px] font-bold text-[#1B1C1F] leading-tight">{value}</h2>
      </div>
      <div className="mt-auto flex items-center gap-1.5">
        <div className={`flex items-center gap-0.5 text-[13px] font-bold ${trendColor}`}>
          <TrendIcon size={14} />
          <span>{isPositive ? `+${trendValue}` : trendValue}</span>
        </div>
        <span className="text-[13px] text-[#1B1C1F]">vs Last Year</span>
      </div>
    </div>
  );
}