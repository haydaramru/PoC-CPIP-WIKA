'use client';

import React from 'react';
import { DollarSign, Calendar } from 'lucide-react';

type Props = {
  label: string;
  value: number;
  min?: number;
  max?: number;
  type?: 'cost' | 'schedule';
};

export default function GaugeChart({
  label,
  value,
  min = 0,
  max = 1.2,
  type = 'cost',
}: Props) {
  const cx = 150;
  const cy = 145;
  const outerRadius = 110;
  const thickness = 36;
  const innerRadius = outerRadius - thickness;

  const START_DEG = 180;
  const ARC_SPAN = 180;
  const GAP = 3;

  const toRad = (deg: number) => (deg * Math.PI) / 180;
  const polarToXY = (deg: number, r: number) => ({
    x: cx + r * Math.cos(toRad(deg)),
    y: cy - r * Math.sin(toRad(deg)),
  });

  const describeDonutArc = (startDeg: number, spanDeg: number) => {
    const endDeg = startDeg - spanDeg;
    const outerStart = polarToXY(startDeg, outerRadius);
    const outerEnd = polarToXY(endDeg, outerRadius);
    const innerStart = polarToXY(startDeg, innerRadius);
    const innerEnd = polarToXY(endDeg, innerRadius);
    const largeArcFlag = spanDeg <= 180 ? '0' : '1';

    return `
      M ${outerStart.x} ${outerStart.y}
      A ${outerRadius} ${outerRadius} 0 ${largeArcFlag} 1 ${outerEnd.x} ${outerEnd.y}
      L ${innerEnd.x} ${innerEnd.y}
      A ${innerRadius} ${innerRadius} 0 ${largeArcFlag} 0 ${innerStart.x} ${innerStart.y}
      Z
    `;
  };

  const clamped = Math.min(Math.max(value, min), max);
  const pct = (clamped - min) / (max - min);
  const needleDeg = START_DEG - pct * ARC_SPAN;

  const segments = [
    { start: 0,   end: 0.6, color: '#D32F2F' },
    { start: 0.6, end: 1.0, color: '#F9A825' },
    { start: 1.0, end: 1.2, color: '#388E3C' },
  ];

  const ticks = [0, 0.4, 0.6, 0.8, 1.0, 1.2];
  const isGood = clamped >= 1.0;
  const statusColor = isGood ? '#388E3C' : '#D32F2F';
  const statusText = type === 'cost'
    ? isGood ? 'Cost Efficient' : 'Over Budget'
    : isGood ? 'Ahead of Schedule' : 'Behind Schedule';

  return (
    <div className="flex-1 h-101 px-15 py-16 border border-gray-200 bg-white rounded-lg flex flex-col items-center justify-between">
      <div style={{ display: 'flex', alignItems: 'center', gap: '10px', alignSelf: 'flex-start' }}>
        <div style={{ width: '32px', height: '32px', backgroundColor: '#1D4ED8', borderRadius: '6px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
          {type === 'cost' ? <DollarSign size={16} color="white" /> : <Calendar size={16} color="white" />}
        </div>
        <span style={{ fontSize: '14px', fontWeight: 700, color: '#374151' }}>{label}</span>
      </div>

      <svg viewBox="0 0 300 175" style={{ width: '100%', maxHeight: '180px' }}>
        {segments.map((seg, i) => {
          const startPct = (seg.start - min) / (max - min);
          const endPct = (seg.end - min) / (max - min);
          const span = (endPct - startPct) * ARC_SPAN;
          const startAngle = START_DEG - startPct * ARC_SPAN;
          return (
            <path key={i} d={describeDonutArc(startAngle - (i > 0 ? GAP / 2 : 0), span - (i < 2 ? GAP / 2 : 0))} fill={seg.color} />
          );
        })}

        {ticks.map((val) => {
          const p = (val - min) / (max - min);
          const deg = START_DEG - p * ARC_SPAN;
          const pos = polarToXY(deg, outerRadius + 16);
          return (
            <text key={val} x={pos.x} y={pos.y} fontSize="9" fill="#9CA3AF" textAnchor="middle" fontWeight="600" dominantBaseline="middle">
              {val.toFixed(1)}
            </text>
          );
        })}

        <line x1={cx} y1={cy} x2={polarToXY(needleDeg, outerRadius - 6).x} y2={polarToXY(needleDeg, outerRadius - 6).y} stroke="#111827" strokeWidth="3.5" strokeLinecap="round" />
        <circle cx={cx} cy={cy} r="9" fill="#111827" />
        <circle cx={cx} cy={cy} r="4" fill="white" />
      </svg>

      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
        <span style={{ fontSize: '64px', fontWeight: 900, color: statusColor, lineHeight: 1 }}>{clamped.toFixed(2)}</span>
        <span style={{ fontSize: '14px', fontWeight: 700, color: '#111827', marginTop: '4px' }}>{statusText}</span>
      </div>
    </div>
  );
}

// ─── Section Wrapper ──────────────────────────────────────────
export function VisualIndicatorSection() {
  return (
    <div className="w-full h-121.25 bg-white border border-gray-100 rounded-2xl px-8 py-4 flex flex-col gap-4.5">
      <h2 style={{ fontSize: '18px', fontWeight: 700, color: '#1B1C1F', letterSpacing: '0.08em', marginBottom: '18px' }}>
        Visual Indicator
      </h2>
      <div className="flex flex-1 gap-4.5">
        <GaugeChart label="Cost Performance Index" value={1.01} type="cost" />
        <GaugeChart label="Schedule Performance Index" value={1.05} type="schedule" />
      </div>
    </div>
  );
}