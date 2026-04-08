// app/layout.tsx
import './globals.css';
import { SidebarProvider } from '@/components/layout/SidebarContext';
import AuthGuard from '@/components/auth/AuthGuard';
import LayoutShell from '@/components/layout/LayoutShell';

export const metadata = {
  title: "Project Performance Dashboard – CPIP",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body className="min-h-screen bg-white">
        <SidebarProvider>
          <AuthGuard>
            <LayoutShell>{children}</LayoutShell>
          </AuthGuard>
        </SidebarProvider>
      </body>
    </html>
  );
}
