import type { ProjectStatus } from '@/types/project';

/**
 * Parse nilai dari Laravel yang bisa string atau number.
 * Laravel decimal field selalu return string, jadi helper ini
 * dipakai di banyak komponen sebelum kalkulasi.
 */
export function toNum(value: string | number): number {
  return typeof value === 'string' ? parseFloat(value) : value;
}

/**
 * Format angka (dalam Juta) jadi tampilan currency.
 * >= 1000 M → Triliun, sisanya → Miliar
 */
export function formatCurrency(valueInMillion: string | number): string {
  const val = toNum(valueInMillion);
  if (val >= 1000) return `Rp ${(val / 1000).toFixed(2)} T`;
  return `Rp ${val.toFixed(0)} M`;
}

/**
 * Format nilai KPI jadi 2 desimal.
 */
export function formatKpi(value: string | number): string {
  return toNum(value).toFixed(2);
}

/**
 * Tailwind text color berdasarkan nilai KPI.
 * Hanya warna — bold/semibold diatur oleh komponen masing-masing.
 */
export function kpiColor(value: string | number): string {
  const val = toNum(value);
  if (val >= 1)   return 'text-green-600';
  if (val >= 0.9) return 'text-yellow-600';
  return 'text-red-600';
}

/**
 * Intent level berdasarkan nilai KPI.
 * Dipakai komponen yang butuh lebih dari sekadar warna (misal border, bg).
 */
export function kpiIntent(value: string | number): 'good' | 'warning' | 'critical' {
  const val = toNum(value);
  if (val >= 1)   return 'good';
  if (val >= 0.9) return 'warning';
  return 'critical';
}