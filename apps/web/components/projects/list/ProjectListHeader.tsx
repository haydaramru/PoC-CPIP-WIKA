'use client';

import { Search, ChevronDown } from 'lucide-react';

type Props = {
  search: string;
  division: string;
  totalShown: number;
  totalAll: number;
  onSearchChange: (v: string) => void;
  onDivisionChange: (v: string) => void;
};

const DIVISIONS = [
  { value: '',               label: 'Division' },
  { value: 'Infrastructure', label: 'Infrastructure' },
  { value: 'Building',       label: 'Building' },
];

export default function ProjectListHeader({
  search, division, onSearchChange, onDivisionChange,
}: Props) {
  return (
    <div 
      className="flex items-center justify-between mb-6"
      style={{ 
        width: '1139px', 
        height: '37px', 
        opacity: 1 
      }}
    >
      {/* Search Bar - Sisi Kiri dengan Icon di Kanan */}
      {/* Search Bar Container */}
<div 
  className="relative flex items-center"
  style={{ 
    width: '783px', 
    height: '37px', 
    opacity: 1,
    rotate: '0deg'
  }}
>
  <input
    type="text"
    placeholder="Search project here"
    value={search}
    onChange={(e) => onSearchChange(e.target.value)}
    className="w-full h-full text-[13px] bg-white border-gray-200 placeholder:text-gray-400 focus:outline-none focus:border-gray-400 transition-all"
    style={{
      paddingTop: '8px',
      paddingBottom: '2px',
      paddingLeft: '16px',
      paddingRight: '40px', // Ruang ekstra di kanan untuk ikon Search
      borderRadius: '8px',
      borderWidth: '0.5px',
      borderStyle: 'solid'
    }}
  />
  <Search 
    className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" 
    size={16} 
  />
</div>

      <div className="flex items-center gap-3 h-full">
        {/* Division Filter - Sisi Kanan */}
        <div className="relative h-full">
          <select
            value={division}
            onChange={e => onDivisionChange(e.target.value)}
            className="appearance-none h-full pl-4 pr-10 bg-white border border-gray-200 rounded-lg text-[13px] text-gray-600 font-medium cursor-pointer focus:outline-none hover:bg-gray-50 transition-all"
          >
            {DIVISIONS.map(d => (
              <option key={d.value} value={d.value}>{d.label}</option>
            ))}
          </select>
          <ChevronDown 
            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" 
            size={14} 
          />
        </div>
      </div>
    </div>
  );
}