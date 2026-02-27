'use client';

import React from 'react';
import type { Project } from '@/types/project';
import { Info, AlertTriangle, AlertCircle } from 'lucide-react';

type Props = {
  project: Project;
  allProjects: Project[];
};

type Insight = {
  text: string;
  level: 'info' | 'warning' | 'critical';
};

function generateInsights(project: Project, allProjects: Project[]): {
  bullets: Insight[];
  summary: { text: string; level: 'info' | 'warning' | 'critical' };
} {
  const bullets: Insight[] = [];

  const cpi         = parseFloat(project.cpi);
  const spi         = parseFloat(project.spi);
  const plannedCost = parseFloat(project.planned_cost);
  const actualCost  = parseFloat(project.actual_cost);
  const delay       = project.actual_duration - project.planned_duration;
  const overrunPct  = ((actualCost - plannedCost) / plannedCost) * 100;

  // ── Cost insight ─────────────────────────────────────────
  if (cpi >= 1) {
    bullets.push({
      text: `Positive cost performance: CPI ${cpi.toFixed(2)} indicates the project is under budget and cost-efficient.`,
      level: 'info',
    });
  } else {
    bullets.push({
      text: `Cost overrun detected: CPI ${cpi.toFixed(2)} indicates the project is ${Math.abs(overrunPct).toFixed(1)}% over planned budget.`,
      level: cpi < 0.9 ? 'critical' : 'warning',
    });
  }

  // ── Schedule insight ──────────────────────────────────────
  if (spi >= 1) {
    bullets.push({
      text: `Strong schedule performance: SPI ${spi.toFixed(2)} indicates the project is ahead of schedule compared to the baseline plan.`,
      level: 'info',
    });
  } else if (delay > 0) {
    bullets.push({
      text: `Schedule delay: SPI ${spi.toFixed(2)} indicates the project is ${delay} month${delay > 1 ? 's' : ''} behind the planned timeline.`,
      level: delay > 3 ? 'critical' : 'warning',
    });
  } else {
    bullets.push({
      text: `Schedule on track: SPI ${spi.toFixed(2)} indicates the project is progressing as planned.`,
      level: 'info',
    });
  }

  // ── Division comparison ───────────────────────────────────
  const sameDivision = allProjects.filter(
    p => p.division === project.division && p.id !== project.id,
  );

  if (sameDivision.length > 0) {
    const avgCpi = sameDivision.reduce((s, p) => s + parseFloat(p.cpi), 0) / sameDivision.length;
    const cpiDiff = cpi - avgCpi;

    if (Math.abs(cpiDiff) > 0.05) {
      bullets.push({
        text: `Division comparison: This project's CPI is ${Math.abs(cpiDiff * 100).toFixed(1)}% ${cpiDiff > 0 ? 'above' : 'below'} the ${project.division} division average (avg CPI: ${avgCpi.toFixed(2)}).`,
        level: cpiDiff < 0 ? 'warning' : 'info',
      });
    }

    const alsoOverbudget = sameDivision.filter(p => parseFloat(p.cpi) < 1).length;
    if (alsoOverbudget > 0 && cpi < 1) {
      bullets.push({
        text: `Systemic risk: ${alsoOverbudget} of ${sameDivision.length} other ${project.division} projects are also over budget, suggesting a division-wide pattern.`,
        level: 'warning',
      });
    }
  }

  // ── Summary sentence ──────────────────────────────────────
  let summary: { text: string; level: 'info' | 'warning' | 'critical' };

  if (cpi >= 1 && spi >= 1) {
    summary = {
      text: 'Overall project health is strong. The project is performing above plan with opportunities for scope optimization if the trend continues.',
      level: 'info',
    };
  } else if (cpi < 0.9 && spi < 0.9) {
    summary = {
      text: 'Overall project health is critical. Both cost and schedule are significantly off-track — immediate escalation and a full review are recommended.',
      level: 'critical',
    };
  } else if (cpi < 1 || spi < 1) {
    summary = {
      text: 'Overall project health is at risk. One or more performance indicators are below target — proactive corrective actions are advised.',
      level: 'warning',
    };
  } else {
    summary = {
      text: 'Overall project health is on track. Continue monitoring for any emerging deviations.',
      level: 'info',
    };
  }

  return { bullets, summary };
}

export default function InsightBox({ project, allProjects }: Props) {
  const { bullets, summary } = generateInsights(project, allProjects);

  const summaryColor = {
    info:     'text-green-700',
    warning:  'text-yellow-700',
    critical: 'text-red-700',
  }[summary.level];

  // Split summary into bold lead + rest
  const summaryParts = summary.text.split('. ');
  const summaryLead  = summaryParts[0] + '.';
  const summaryRest  = summaryParts.slice(1).join('. ');

  return (
    <div style={{
        width: '1203px',
        height: '201px',
        padding: '18px 32px',
        gap: '18px',
        boxSizing: 'border-box',}}
        >
      <h2 className="text-lg font-bold text-[#1B1C1F] mb-3">
        Key Insight
      </h2>

      <div className="border border-gray-200 rounded-xl bg-white px-6 py-5">
        {/* Bullet points */}
        <ul className="space-y-1.5 mb-5">
          {bullets.map((insight, i) => (
            <li key={i} className="flex items-start gap-2 text-sm text-gray-700 leading-relaxed">
              <span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0" />
              {insight.text}
            </li>
          ))}
        </ul>

        {/* Divider */}
        <div className="border-t border-gray-100 mb-4" />

        {/* Summary sentence */}
        <p className="text-sm text-gray-700 leading-relaxed">
          <span className={`font-semibold ${summaryColor}`}>{summaryLead}</span>
          {summaryRest ? ` ${summaryRest}` : ''}
        </p>
      </div>
    </div>
  );
}