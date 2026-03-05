'use client';

import { Info, Wallet, CalendarDays, FileText } from 'lucide-react';
import { useState, useRef } from 'react';
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

const CHART_H    = 280;
const MAX        = 1.0;
const Y_TICKS    = [0, 0.2, 0.4, 0.6, 0.8, 1.0];

interface Tooltip {
  visible: boolean;
  x: number;
  y: number;
  label: string;
  value: string;
  color: string;
}

export default function DivisionChart({ data }: Props) {
  const divisions = Object.entries(data.by_division);
  const chartRef  = useRef<HTMLDivElement>(null);
  const [tooltip, setTooltip] = useState<Tooltip>({
    visible: false, x: 0, y: 0, label: '', value: '', color: '',
  });

  if (divisions.length === 0) return null;

  function showTooltip(e: React.MouseEvent, label: string, value: string, color: string) {
    const rect = chartRef.current?.getBoundingClientRect();
    if (!rect) return;
    setTooltip({ visible: true, x: e.clientX - rect.left, y: e.clientY - rect.top, label, value, color });
  }

  function moveTooltip(e: React.MouseEvent, label: string, value: string, color: string) {
    const rect = chartRef.current?.getBoundingClientRect();
    if (!rect) return;
    setTooltip(t => ({ ...t, x: e.clientX - rect.left, y: e.clientY - rect.top }));
  }

  function hideTooltip() {
    setTooltip(t => ({ ...t, visible: false }));
  }


  return (
    <div className="bg-white" style={{ width: '1203px', height: '493px', padding: '18px 32px' }}>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-[18px] font-bold text-[#1B1C1F]">Division Performance Comparison</h2>
      </div>

<div className="flex items-start" 
  style={{ 
    width: '1139px', 
    height: '412px', 
    gap: '33px', 
    opacity: 1 
  }}
>
<div className="flex flex-col items-center gap-3" style={{ width: '553px' }}>

  <div className="flex items-center gap-6">
    <div className="flex items-center gap-2">
      <div className="w-3 h-3 rounded-full bg-[#1D3A8A]" />
      <span className="text-[12px] font-semibold text-gray-500">Cost Performance Index</span>
    </div>
    <div className="flex items-center gap-2">
      <div className="w-3 h-3 rounded-full bg-[#A8BADA]" />
      <span className="text-[12px] font-semibold text-gray-500">Schedule Performance Index</span>
    </div>
  </div>

  <div
    ref={chartRef}
    className="relative bg-white border border-gray-100 rounded-2xl"
    style={{ width: '553px', height: '380px', padding: '24px 28px 20px 16px' }}
  >
    {tooltip.visible && (
      <div
        className="absolute z-50 pointer-events-none"
        style={{ left: tooltip.x + 14, top: tooltip.y - 56 }}
      >
        <div className="bg-white border border-gray-200 rounded-xl shadow-lg px-4 py-3 min-w-[170px]">
          <div className="flex items-center gap-2 mb-1.5">
            <div className="w-2 h-2 rounded-full shrink-0" style={{ backgroundColor: tooltip.color }} />
            <span className="text-[11px] text-gray-400 font-medium">{tooltip.label}</span>
          </div>
          <span className="text-[20px] font-bold text-gray-900 leading-none">{tooltip.value}</span>
        </div>
      </div>
    )}

    <div className="flex gap-2 h-full">

      <div className="relative shrink-0" style={{ width: '28px', height: `${CHART_H}px` }}>
        {Y_TICKS.map((tick) => (
          <span
            key={tick}
            className="absolute text-[11px] text-gray-400 font-medium leading-none"
            style={{
              bottom: `${(tick / MAX) * 100}%`,
              right: 0,
              transform: 'translateY(50%)',
            }}
          >
            {tick === 0 ? '0' : tick.toFixed(1).replace(/\.0$/, '')}
          </span>
        ))}
      </div>

      <div className="flex-1 flex flex-col">

        <div className="relative" style={{ height: `${CHART_H}px` }}>

          <div className="absolute inset-0 pointer-events-none">
            {Y_TICKS.map((tick) => (
              <div
                key={tick}
                className="absolute w-full"
                style={{
                  bottom: `${(tick / MAX) * 100}%`,
                  borderTop: `1px solid ${tick === 0 ? '#D1D5DB' : '#F3F4F6'}`,
                }}
              />
            ))}
          </div>

          <div className="absolute inset-0 flex items-end justify-around px-6">
            {divisions.map(([division, divData]) => (
              <div key={division} className="flex items-end gap-3">

                <div
                  className="relative cursor-pointer"
                  style={{ width: '52px', height: `${(divData.avg_cpi / MAX) * CHART_H}px` }}
                  onMouseEnter={e => showTooltip(e, 'Cost Performance Index', formatKpi(divData.avg_cpi), '#1D3A8A')}
                  onMouseMove={e  => moveTooltip(e,  'Cost Performance Index', formatKpi(divData.avg_cpi), '#1D3A8A')}
                  onMouseLeave={hideTooltip}
                >
                  <div
                    className="w-full h-full transition-all duration-1000 hover:opacity-90"
                    style={{
                      backgroundImage: 'linear-gradient(to bottom, #2D52C4, #0D1B49)',
                      borderRadius: '6px 6px 0 0',
                    }}
                  />
                </div>

                <div
                  className="relative cursor-pointer"
                  style={{ width: '52px', height: `${(divData.avg_spi / MAX) * CHART_H}px` }}
                  onMouseEnter={e => showTooltip(e, 'Schedule Performance Index', formatKpi(divData.avg_spi), '#A8BADA')}
                  onMouseMove={e  => moveTooltip(e,  'Schedule Performance Index', formatKpi(divData.avg_spi), '#A8BADA')}
                  onMouseLeave={hideTooltip}
                >
                  <div
                    className="w-full h-full transition-all duration-1000 hover:opacity-90"
                    style={{
                      backgroundImage: 'linear-gradient(to bottom, #C5D0E8 0%, #8FA3CC 50%, #152868 100%)',
                      borderRadius: '6px 6px 0 0',
                    }}
                  />
                </div>

              </div>
            ))}
          </div>

        </div>

        <div className="flex justify-around px-6 pt-3">
          {divisions.map(([division]) => (
            <span key={division} className="text-[13px] font-medium text-gray-500">
              {division}
            </span>
          ))}
        </div>

      </div>
    </div>
  </div>

</div>
  
  <div 
    className="flex flex-col" 
    style={{ 
      width: '553px', 
      height: '412px', 
      gap: '18px'
    }}
  >
    <div className="grid grid-cols-2 gap-4.5 w-full">
      {divisions.map(([division, divData]) => (
        <div key={division} className="contents">
          <MiniKpiCard 
            label="CPI (Cost Index)" 
            value={formatKpi(divData.avg_cpi)} 
            sub={division} 
            icon={Wallet} 
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

  <div 
    className="bg-white border border-gray-100 rounded-xl p-6 shadow-sm flex flex-col min-h-0"
    style={{ 
      width: '100%', 
      height: '136px', 
      flexGrow: 1, 
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