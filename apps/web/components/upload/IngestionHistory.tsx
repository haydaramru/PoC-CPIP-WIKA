'use client';

import { useEffect, useState } from 'react';
import { ingestionApi } from '@/lib/api';
import type { IngestionFile } from '@/types/project';

const STATUS_CONFIG = {
  success:    { label: 'Success',    dot: 'bg-green-500', text: 'text-green-700', bg: 'bg-green-50'  },
  partial:    { label: 'Partial',    dot: 'bg-yellow-500', text: 'text-yellow-700', bg: 'bg-yellow-50' },
  failed:     { label: 'Failed',     dot: 'bg-red-500',   text: 'text-red-700',   bg: 'bg-red-50'    },
  pending:    { label: 'Pending',    dot: 'bg-gray-400',  text: 'text-gray-500',  bg: 'bg-gray-50'   },
  processing: { label: 'Processing', dot: 'bg-blue-500',  text: 'text-blue-700',  bg: 'bg-blue-50'   },
} as const;

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { year: 'numeric', day: 'numeric', month: 'long' });
}

interface Props {
  refreshTrigger?: number;
}

export default function IngestionHistory({ refreshTrigger = 0 }: Props) {
  const [files,   setFiles]   = useState<IngestionFile[]>([]);
  const [loading, setLoading] = useState(true);
  const [error,   setError]   = useState('');

  useEffect(() => {
    setLoading(true);
    ingestionApi.list(50)
      .then(res => setFiles(res.data))
      .catch(() => setError('Gagal memuat riwayat upload.'))
      .finally(() => setLoading(false));
  }, [refreshTrigger]);

  if (loading) {
    return (
      <div className="border border-gray-200 rounded-xl bg-white px-6 py-8 flex items-center justify-center gap-3 text-gray-400">
        <div className="w-5 h-5 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin" />
        <span className="text-sm">Memuat riwayat...</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="border border-gray-200 rounded-xl bg-white px-6 py-5">
        <p className="text-sm text-red-500">{error}</p>
      </div>
    );
  }

  return (
    <div className="border border-gray-200 rounded-xl bg-white overflow-hidden">
      <div className="px-6 py-4 border-b border-gray-100">
        <h2 className="text-sm font-bold text-gray-700">Project History</h2>
      </div>

      {files.length === 0 ? (
        <div className="px-6 py-10 text-center text-sm text-gray-400">
          Belum ada file yang diupload.
        </div>
      ) : (
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100 text-left">
              <th className="px-6 py-3 text-xs font-semibold text-gray-400 w-10">#</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400">File Name</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400">Total Rows</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400">Success</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400">Failed</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400">Status</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400">Processed At</th>
              <th className="px-4 py-3 text-xs font-semibold text-gray-400"></th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {files.map((file, index) => {
              const statusCfg = STATUS_CONFIG[file.status] ?? STATUS_CONFIG.pending;

              return (
                <tr key={file.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4 text-gray-400 text-xs">{index + 1}</td>

                  <td className="px-4 py-4">
                    <span className="text-gray-800 font-medium truncate max-w-xs block" title={file.original_name}>
                      {file.original_name}
                    </span>
                  </td>

                  <td className="px-4 py-4 text-gray-600">{file.total_rows}</td>

                  <td className="px-4 py-4 text-green-600 font-medium">{file.imported_rows}</td>

                  <td className="px-4 py-4 text-red-500 font-medium">{file.skipped_rows}</td>

                  <td className="px-4 py-4">
                    <div className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${statusCfg.bg} ${statusCfg.text}`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${statusCfg.dot}`} />
                      {statusCfg.label}
                    </div>
                  </td>

                  <td className="px-4 py-4 text-gray-500 text-xs">
                    {formatDate(file.processed_at)}
                  </td>

                  <td className="px-4 py-4">
                    <a
                      href={ingestionApi.downloadUrl(file.id)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-xs text-blue-500 hover:text-blue-700 font-medium transition-colors"
                      title="Download file"
                    >
                      Download
                    </a>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      )}
    </div>
  );
}