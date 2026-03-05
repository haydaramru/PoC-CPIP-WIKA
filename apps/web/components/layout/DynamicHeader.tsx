"use client";

import { usePathname } from "next/navigation";
import { Settings, Bell, ChevronDown } from "lucide-react";

export default function DynamicHeader() {
  const pathname = usePathname();

  const headerMap: Record<string, { title: string; sub?: string }> = {
    "/": {
      title: "Past Project Performance Dashboard",
      sub: "Trisya Media Teknologi · Divisi Infrastructure 2 & Building",
    },
    "/projects": {
      title: "Project Management",
      sub: "List of all active projects",
    },
    "/upload": { title: "Upload Project", sub: "Import Excel files" },
  };

  const current = headerMap[pathname] || { title: "Dashboard" };

  return (
    <header
      className="flex items-center justify-between border-b border-[#E9E9EA] bg-white sticky top-0 z-10"
      style={{ width: "1203px", height: "89px", padding: "0 32px" }}
    >
      <div className="flex flex-col">
        <h1 className="text-[#1B1C1F] font-bold text-[20px] leading-tight">
          {current.title}
        </h1>
      </div>

      <div className="flex items-center gap-5">
        <button className="text-[#1B1C1F] hover:text-gray-600">
          <Settings size={20} />
        </button>
        <button className="text-[#1B1C1F] hover:text-gray-600 relative">
          <Bell size={20} />
          <span className="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>

        <div className="flex items-center gap-2 pl-2 cursor-pointer group">
          <div className="w-9 h-9 bg-primary-blue rounded-full flex items-center justify-center text-white font-bold text-sm">
            JD
          </div>
          <ChevronDown
            size={16}
            className="text-[#1B1C1F] group-hover:text-gray-600"
          />
        </div>
      </div>
    </header>
  );
}
