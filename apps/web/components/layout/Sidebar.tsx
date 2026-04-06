'use client';

import Link from 'next/link';
import { cloneElement, ReactElement } from 'react';
import { usePathname } from 'next/navigation';
import { 
  LayoutDashboard, 
  BarChart3, 
  Upload, 
  TableProperties,
  Bell, 
  Settings, 
  LogOut, 
  ChevronDown,
  ChevronLeft
} from 'lucide-react';

const menuGroups = [
  {
    title: 'General',
    items: [
      { href: '/', label: 'Dashboard', icon: <LayoutDashboard size={20} /> },
      { href: '/projects', label: 'Project List', icon: <BarChart3 size={20} /> },
      { href: '/upload', label: 'Data Ingestion', icon: <Upload size={20} /> },
      { href: '/aliases', label: 'Column Aliases', icon: <TableProperties size={20} /> },
    ]
  },
  {
    title: 'Others',
    items: [
      { href: '/notifications', label: 'Notifications', icon: <Bell size={20} /> },
      { href: '/settings', label: 'Settings', icon: <Settings size={20} /> },
      { href: '/logout', label: 'Log Out', icon: <LogOut size={20} /> },
    ]
  }
];

export default function Sidebar() {
  const pathname = usePathname();

  return (
    <aside 
      className="fixed top-0 left-0 h-screen flex flex-col bg-sidebar border-r border-gray-200 text-dark-gray antialiased"
      style={{ width: '237px' }}
    >
      
      <div 
        className="flex items-center justify-between border-b border-[#E9E9EA] shrink-0" 
        style={{ 
          height: '89px', 
          paddingTop: '24px', 
          paddingRight: '18px', 
          paddingBottom: '16px', 
          paddingLeft: '18px'
        }}
      >
        <div 
          className="bg-primary-blue shadow-sm"
          style={{ width: '73px', height: '49px', borderRadius: '8px' }}
        ></div>

        <button 
          className="hover:bg-gray-200 border border-gray-200 bg-white transition-colors flex items-center justify-center shrink-0"
          style={{ width: '20px', height: '20px', borderRadius: '4px' }}
        >
          <ChevronLeft size={12} className="text-[#1B1C1F]" />
        </button>
      </div>

      <div className="flex-1 px-3 py-6 space-y-7 overflow-y-auto">
        {menuGroups
          .filter((group) => group.title === 'General')
          .map((group) => (
            <div key={group.title}>
              <h3 className="px-4 text-[11px] font-semibold text-gray-400 tracking-wider mb-2">
                {group.title}
              </h3>
              <nav className="space-y-1">
                {group.items.map((item) => {
                  const isActive = pathname === item.href;
                  return (
                  <Link
                      key={item.href}
                      href={item.href}
                      className={`flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all ${
                        isActive
                          ? 'bg-primary-blue text-white shadow-lg shadow-blue-900/20 font-bold text-[14px] leading-[150%]' 
                          : 'text-[#1B1C1F] hover:bg-gray-200/60 font-medium text-[14px] leading-[150%]'
                      }`}
                      style={{ fontFamily: 'Inter, sans-serif' }}
                    >
                      <span className={isActive ? 'text-white' : 'text-[#1B1C1F]'}>
                        {item.icon}
                      </span>
                      {item.label}
                    </Link>
                  );
                })}
              </nav>
            </div>
          ))}
      </div>

      <div 
        className="mt-auto flex flex-col justify-between shrink-0"
        style={{ width: '100%', height: '237px' }}
      >                               
        <div className="px-4 pt-2">
          <h3 className="text-[11px] font-semibold text-gray-400 tracking-wider mb-3">
            Others
          </h3>
          <nav className="space-y-1">
            {menuGroups[1].items.map((item) => {
              const isActive = pathname === item.href;
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all ${
                    isActive
                      ? 'bg-primary-blue text-white'
                      : 'text-[#1B1C1F] hover:bg-gray-100'
                  }`}
                >
                  <span className={isActive ? 'text-white' : 'text-[#1B1C1F]'}>
                    {cloneElement(item.icon as ReactElement<{ size: number }>, { size: 18 })}
                  </span>
                  {item.label}
                </Link>
              );
            })}
          </nav>
        </div>

        <div 
          className="flex items-center justify-between border-t border-[#E9E9EA] group cursor-pointer bg-sidebar w-full shrink-0"
          style={{
            height: '76px',
            paddingTop: '16px',
            paddingRight: '18px',
            paddingBottom: '24px',
            paddingLeft: '18px',
            boxSizing: 'border-box'
          }}
        >
          <div className="flex items-center gap-2.5 overflow-hidden">
            <div className="w-9 h-9 rounded-full bg-primary-blue shrink-0 border border-gray-200 shadow-sm"></div>
            <div className="overflow-hidden text-left">
              <p className="text-[13px] font-bold text-dark-gray truncate leading-tight">
                Rista Mulia Putri
              </p>
              <p className="text-[11px] text-gray-500 truncate">
                ristamulia@gmail.com
              </p>
            </div>
          </div>
          <ChevronDown size={14} className="text-[#1B1C1F] bg-white shrink-0" />
        </div>
      </div>
    </aside>
  );
}
