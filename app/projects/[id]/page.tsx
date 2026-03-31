import type { Metadata } from 'next';
import ProjectDetail from '@/components/projects/ProjectDetail';

export const metadata: Metadata = {
  title: 'Project Detail – CPIP',
};

export default async function ProjectDetailPage({ 
  params 
}: { 
  params: Promise<{ id: string }>
}) {
  // Tunggu (await) params sebelum mengakses propertinya
  const resolvedParams = await params;
  const id = Number(resolvedParams.id);

  if (isNaN(id)) {
    return (
      <div className="card border border-red-200 bg-red-50 text-red-700 text-sm p-4">
        ID project tidak valid.
      </div>
    );
  }

  return <ProjectDetail id={id} />;
}