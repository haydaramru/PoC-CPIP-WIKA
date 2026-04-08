"use client";

import { usePathname } from "next/navigation";
import Sidebar from "@/components/layout/Sidebar";
import DynamicHeader from "@/components/layout/DynamicHeader";

const NO_SHELL_PATHS = ["/login"];

export default function AppShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const noShell = NO_SHELL_PATHS.includes(pathname);

  if (noShell) return <>{children}</>;

  return (
    <>
      <Sidebar />
      <main className="ml-59.25 bg-white">
        <DynamicHeader />
        <div className="relative w-full">{children}</div>
      </main>
    </>
  );
}
