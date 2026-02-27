import type { Metadata } from 'next';
import DashboardSummary from '@/components/dashboard/DashboardSummary';

export const metadata: Metadata = {
  title: 'Dashboard – CPIP',
};

export default function HomePage() {
  return (
    <div>
      <DashboardSummary />
    </div>
  );
}