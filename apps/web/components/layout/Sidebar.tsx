// 'use client';

// import Link from 'next/link';
// import { usePathname } from 'next/navigation';

// const navItems = [
//   {
//     href: '/',
//     label: 'Dashboard',
//     icon: (
//       <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
//           d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
//       </svg>
//     ),
//   },
//   {
//     href: '/projects',
//     label: 'Project List',
//     icon: (
//       <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
//           d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
//       </svg>
//     ),
//   },
//   {
//     href: '/upload',
//     label: 'Upload Excel',
//     icon: (
//       <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
//         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
//           d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
//       </svg>
//     ),
//   },
// ];

// export default function Sidebar() {
//   const pathname = usePathname();

//   return (
//     <aside className="fixed top-0 left-0 h-full w-64 sidebar text-white flex flex-col">
//       {/* Logo */}
//       <div className="px-6 py-5 border-b border-blue-800">
//         <p className="text-xs text-blue-300 uppercase tracking-widest">Trisya Media Teknologi</p>
//         <h1 className="text-lg font-bold mt-0.5">CPIP</h1>
//         <p className="text-xs text-blue-400">Project Control Intelligence</p>
//       </div>

//       {/* Navigation */}
//       <nav className="flex-1 px-4 py-6 space-y-1">
//         {navItems.map((item) => {
//           const isActive = pathname === item.href ||
//             (item.href !== '/' && pathname.startsWith(item.href));

//           return (
//             <Link
//               key={item.href}
//               href={item.href}
//               className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
//                 isActive
//                   ? 'bg-blue-700 text-white'
//                   : 'text-blue-200 hover:bg-blue-800 hover:text-white'
//               }`}
//             >
//               {item.icon}
//               {item.label}
//             </Link>
//           );
//         })}
//       </nav>

//       {/* Footer */}
//       <div className="px-6 py-4 border-t border-blue-800 text-xs text-blue-400">
//         Divisi Infrastructure 2 & Building
//       </div>
//     </aside>
//   );
// }
'use client';

import Link from 'next/link';
import { cloneElement, ReactElement } from 'react';
import { usePathname } from 'next/navigation';
import { 
  LayoutDashboard, 
  BarChart3, 
  Upload, 
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
      { href: '/projects', label: 'Projects', icon: <BarChart3 size={20} /> },
      { href: '/upload', label: 'Upload Project', icon: <Upload size={20} /> },
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
      
      {/* 1. TOP SECTION: Logo & Toggle */}
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
        {/* Logo */}
        <div 
          className="bg-primary-blue shadow-sm"
          style={{ width: '73px', height: '49px', borderRadius: '8px' }}
        ></div>

        {/* Toggle Button */}
        <button 
          className="hover:bg-gray-200 border border-gray-200 bg-white transition-colors flex items-center justify-center shrink-0"
          style={{ width: '20px', height: '20px', borderRadius: '4px' }}
        >
          <ChevronLeft size={12} className="text-[#1B1C1F]" />
        </button>
      </div>

      {/* 2. NAVIGATION CONTENT (Hanya General) */}
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

      {/* 3. BOTTOM SECTION: Others + User Info */}
      <div 
        className="mt-auto flex flex-col justify-between shrink-0"
        style={{ width: '100%', height: '237px' }}
      >                               
        {/* Menu Others */}
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

        {/* User Info Section (Garis border-t sekarang akan pas ke pinggir) */}
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