'use client';

import { Info, Wallet, CalendarDays, FileText } from 'lucide-react';
import { formatKpi } from '@/lib/utils';
import type { SummaryResponse } from '@/types/project';

type Props = {
  data: SummaryResponse;
};

function MiniKpiCard({ label, value, sub, icon: Icon }: any) {
  return (
    <div className="bg-white border border-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-between" style={{ width: '254px', height: '120px' }}>
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <div className="p-1.5 bg-primary-blue rounded-md">
            <Icon size={14} className="text-white" />
          </div>
          <span className="text-[12px] font-semibold text-[#1B1C1F]">{label}</span>
        </div>
      </div>
      <div>
        <h3 className="text-[24px] font-bold text-[#1B1C1F]">{value}</h3>
        <p className="text-[11px] text-gray-400 font-medium">{sub}</p>
      </div>
    </div>
  );
}

export default function DivisionChart({ data }: Props) {
  const divisions = Object.entries(data.by_division);
  if (divisions.length === 0) return null;

  // Skala Chart
  const max = 1.25; // Dikunci ke 1.25 agar garis target 1.0 konsisten posisinya
  const yAxisTicks = [0, 0.25, 0.5, 0.75, 1.0, 1.25];

  return (
    <div className="bg-white" style={{ width: '1203px', height: '493px', padding: '18px 32px' }}>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-[18px] font-bold text-[#1B1C1F]">Division Performance Comparison</h2>
        
        {/* ── INDICATOR / LEGEND ── */}
        <div className="flex items-center gap-6">
          <div className="flex items-center gap-2">
            <div className="w-3 h-3 bg-primary-blue rounded-sm" />
            <span className="text-[12px] font-bold text-gray-600">CPI (Cost)</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-3 h-3 bg-[#3B82F6] rounded-sm" />
            <span className="text-[12px] font-bold text-gray-600">SPI (Schedule)</span>
          </div>
        </div>
      </div>

      {/* ── MAIN CONTENT WRAPPER ── */}
<div 
  className="flex items-start" 
  style={{ 
    width: '1139px', 
    height: '412px', 
    gap: '33px', // Jarak antar sisi kiri dan kanan tepat 33px
    opacity: 1 
  }}
>
  {/* ── SISI KIRI: DETAILED CHART AREA ── */}
  <div 
    className="bg-[#F8F9FC] border border-gray-100 rounded-xl relative flex items-end p-6 pt-12" 
    style={{ width: '553px', height: '412px' }} // Lebar simetris
  >
    {/* Y-Axis Ticks & Grid Lines */}
    <div className="absolute inset-0 flex flex-col justify-between p-6 py-12 pointer-events-none">
      {yAxisTicks.reverse().map((tick) => (
        <div key={tick} className="flex items-center w-full gap-2">
          <span className="text-[10px] text-gray-400 font-bold w-6 text-right">{tick.toFixed(2)}</span>
          <div className={`flex-1 h-px ${tick === 1.0 ? 'bg-orange-400 opacity-60' : 'bg-gray-100'}`} />
        </div>
      ))}
    </div>

    {/* Label Target Line */}
    <div 
      className="absolute right-6 text-[10px] font-bold text-orange-500 uppercase tracking-tighter z-20"
      style={{ bottom: `${(1.0 / max) * 310 + 48}px` }} 
    >
      Target 1.0
    </div>

    {/* Bar Group */}
    <div className="relative z-10 flex flex-1 items-end justify-around h-full">
      {divisions.map(([division, divData]) => (
        <div key={division} className="flex flex-col items-center gap-4 h-full justify-end w-28">
          <div className="flex gap-3 h-full items-end w-full px-1">
            {/* Bar CPI */}
            <div className="flex-1 flex flex-col items-center gap-2">
              <span className="text-[10px] font-bold text-primary-blue">{formatKpi(divData.avg_cpi)}</span>
              <div 
                className="w-full bg-linear-to-b from-primary-blue to-[#0D1B49] rounded-t-sm transition-all duration-1000 shadow-sm"
                style={{ height: `${(divData.avg_cpi / max) * 280}px` }}
              />
            </div>
            {/* Bar SPI */}
            <div className="flex-1 flex flex-col items-center gap-2">
              <span className="text-[10px] font-bold text-[#3B82F6]">{formatKpi(divData.avg_spi)}</span>
              <div 
                className="w-full bg-linear-to-b from-[#B9C4E6] to-[#152868] rounded-t-sm transition-all duration-1000 shadow-sm"
                style={{ height: `${(divData.avg_spi / max) * 280}px` }}
              />
            </div>
          </div>
          <span className="text-[10px] font-black text-gray-500 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-gray-100 shadow-sm">
            {division}
          </span>
        </div>
      ))}
    </div>
  </div>
  {/* ── SISI KANAN: CARDS & SUMMARY ── */}
  <div 
    className="flex flex-col" 
    style={{ 
      width: '553px', // Lebar simetris dengan sisi kiri (1139 - 33 gap / 2)
      height: '412px', 
      gap: '18px'
    }}
  >
    {/* Grid Kartu KPI - Menggunakan grid-cols-2 dengan w-full agar simetris */}
    <div className="grid grid-cols-2 gap-4.5 w-full">
      {divisions.map(([division, divData]) => (
        <div key={division} className="contents">
          <MiniKpiCard 
            label="CPI (Cost Index)" 
            value={formatKpi(divData.avg_cpi)} 
            sub={division} 
            icon={Wallet} 
            // Hapus width statis, gunakan w-full di dalam komponen MiniKpiCard
          />
          <MiniKpiCard 
            label="SPI (Schedule Index)" 
            value={formatKpi(divData.avg_spi)} 
            sub={division} 
            icon={CalendarDays} 
          />
        </div>
      ))}
    </div>

    {/* Summary Analysis Box */}
  <div 
    className="bg-white border border-gray-100 rounded-xl p-6 shadow-sm flex flex-col min-h-0"
    style={{ 
      width: '100%', // Akan mengikuti lebar parent (553px)
      height: '136px', // Sisa ruang setelah 2 kartu (120px) + gap (18px)
      flexGrow: 1, // Memastikan mengisi sisa tinggi hingga 412px
    }}
  >
    <div className="flex items-center gap-2 mb-3">
      <div className="p-2 bg-primary-blue rounded-md shrink-0">
        <FileText size={16} className="text-white" />
      </div>
      <span className="text-[14px] font-bold text-[#1B1C1F]">Summary Analysis</span>
    </div>
    
    <div className="flex-1 overflow-y-auto pr-2 custom-scrollbar">
      <p className="text-[13px] leading-relaxed text-gray-600">
        <span className="text-primary-blue font-extrabold text-[14px]">
          Building outperforms Infrastructure
        </span> dalam kontrol biaya (CPI). 
        Berdasarkan data performa saat ini, kedua divisi masih memiliki tantangan pada jadwal proyek di mana <span className="font-bold text-gray-800 underline decoration-red-200 decoration-2">Infrastructure</span> menunjukkan angka SPI yang lebih rendah.
      </p>
    </div>
  </div>
  </div>
</div>
    </div>
  );
}