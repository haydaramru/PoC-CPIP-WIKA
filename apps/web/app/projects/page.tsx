import type { Metadata } from 'next';
import ProjectList from '@/components/projects/ProjectList';

export const metadata: Metadata = {
  title: 'Project List – CPIP',
};

export default function ProjectsPage() {
  return (
    <div>
      <ProjectList />
    </div>
  );
}