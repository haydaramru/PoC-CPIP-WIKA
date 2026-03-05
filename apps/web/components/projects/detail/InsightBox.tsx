'use client';

import { useEffect, useState } from 'react';
import { projectApi } from '@/lib/api';
import type { Project, InsightResponse, InsightLevel } from '@/types/project';

type Props = { project: Project };

const SUMMARY_COLOR: Record<InsightLevel, string> = {
  info:     'text-green-700',
  warning:  'text-yellow-700',
  critical: 'text-red-700',
};

export function InsightBox({ project }: Props) {
  const [data,    setData]    = useState<InsightResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error,   setError]   = useState('');

  useEffect(() => {
    setLoading(true);
    projectApi.insight(project.id)
      .then(res => setData(res))
      .catch(() => setError('Gagal memuat insight.'))
      .finally(() => setLoading(false));
  }, [project.id]);

  if (loading) {
    return (
      <div style={{ width: '1203px', padding: '18px 32px', boxSizing: 'border-box' }}>
        <h2 className="text-lg font-bold text-[#1B1C1F] mb-3">Key Insight</h2>
        <div className="border border-gray-200 rounded-xl bg-white px-6 py-8 flex items-center gap-3 text-gray-400">
          <div className="w-4 h-4 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin shrink-0" />
          <span className="text-sm">Memuat insight...</span>
        </div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div style={{ width: '1203px', padding: '18px 32px', boxSizing: 'border-box' }}>
        <h2 className="text-lg font-bold text-[#1B1C1F] mb-3">Key Insight</h2>
        <div className="border border-gray-200 rounded-xl bg-white px-6 py-5">
          <p className="text-sm text-red-500">{error || 'Insight tidak tersedia.'}</p>
        </div>
      </div>
    );
  }

  const summaryParts = data.summary.text.split('. ');
  const summaryLead  = summaryParts[0] + '.';
  const summaryRest  = summaryParts.slice(1).join('. ');
  const summaryColor = SUMMARY_COLOR[data.summary.level];

  return (
    <div
      style={{
        width: '1203px',
        padding: '18px 32px',
        gap: '18px',
        boxSizing: 'border-box',
      }}
    >
      <h2 className="text-lg font-bold text-[#1B1C1F] mb-3">
        Key Insight
      </h2>

      <div className="border border-gray-200 rounded-xl bg-white px-6 py-5">
        <ul className="space-y-1.5 mb-5">
          {data.bullets.map((bullet, i) => (
            <li key={i} className="flex items-start gap-2 text-sm text-gray-700 leading-relaxed">
              <span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0" />
              {bullet.text}
            </li>
          ))}
        </ul>

        <div className="border-t border-gray-100 mb-4" />

        <p className="text-sm text-gray-700 leading-relaxed">
          <span className={`font-semibold ${summaryColor}`}>{summaryLead}</span>
          {summaryRest ? ` ${summaryRest}` : ''}
        </p>
      </div>
    </div>
  );
}

export default InsightBox;