'use client';

import { useState, useRef, DragEvent, ChangeEvent } from 'react';
import { projectApi } from '@/lib/api';
import type { UploadResponse, FileUploadResult } from '@/types/project';
import IngestionHistory from '@/components/upload/IngestionHistory';

type UploadState = 'idle' | 'dragging' | 'uploading' | 'done' | 'error';

interface SelectedFile {
  file: File;
  id: string;
  validationError: string | null;
}

const COLUMNS = [
  ['project_code',     'Wajib'],
  ['project_name',     'Wajib'],
  ['division',         'Wajib · Infrastructure / Building'],
  ['contract_value',   'Wajib · dalam Miliar (M)'],
  ['planned_cost',     'Wajib'],
  ['actual_cost',      'Wajib'],
  ['planned_duration', 'Wajib · dalam bulan'],
  ['actual_duration',  'Wajib'],
  ['owner',            'Opsional'],
  ['progress_pct',     'Opsional · default 100'],
] as const;

const STATUS_CONFIG = {
  success:    { label: 'Berhasil',   dot: 'bg-green-500',  text: 'text-green-700',  bg: 'bg-green-50'  },
  partial:    { label: 'Sebagian',   dot: 'bg-yellow-500', text: 'text-yellow-700', bg: 'bg-yellow-50' },
  failed:     { label: 'Gagal',      dot: 'bg-red-500',    text: 'text-red-700',    bg: 'bg-red-50'    },
  pending:    { label: 'Menunggu',   dot: 'bg-gray-400',   text: 'text-gray-500',   bg: 'bg-gray-50'   },
  processing: { label: 'Memproses', dot: 'bg-blue-500',   text: 'text-blue-700',   bg: 'bg-blue-50'   },
} as const;

function validateFile(file: File): string | null {
  const allowed = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
  ];
  if (!allowed.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i))
    return 'Format harus .xlsx atau .xls';
  if (file.size > 5 * 1024 * 1024)
    return 'Ukuran maksimal 5MB';
  return null;
}

function formatSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function uniqueId(): string {
  return Math.random().toString(36).slice(2);
}

export default function UploadExcel() {
  const [uploadState,    setUploadState]    = useState<UploadState>('idle');
  const [selectedFiles,  setSelectedFiles]  = useState<SelectedFile[]>([]);
  const [uploadResponse, setUploadResponse] = useState<UploadResponse | null>(null);
  const [globalError,    setGlobalError]    = useState('');
  const [historyRefresh, setHistoryRefresh] = useState(0); // trigger refresh tabel
  const inputRef = useRef<HTMLInputElement>(null);

  const isDragging  = uploadState === 'dragging';
  const isUploading = uploadState === 'uploading';
  const isDone      = uploadState === 'done';
  const hasValidFiles   = selectedFiles.some(f => !f.validationError);
  const hasInvalidFiles = selectedFiles.some(f => f.validationError);
  const validCount      = selectedFiles.filter(f => !f.validationError).length;
  const invalidCount    = selectedFiles.filter(f => f.validationError).length;

  function addFiles(newFiles: File[]) {
    const mapped: SelectedFile[] = newFiles.map(file => ({
      file,
      id: uniqueId(),
      validationError: validateFile(file),
    }));
    setSelectedFiles(prev => {
      const existingNames = new Set(prev.map(f => f.file.name));
      const deduped = mapped.filter(f => !existingNames.has(f.file.name));
      return [...prev, ...deduped];
    });
    setUploadState('idle');
    setUploadResponse(null);
    setGlobalError('');
  }

  function removeFile(id: string) {
    setSelectedFiles(prev => prev.filter(f => f.id !== id));
  }

  const onDragOver  = (e: DragEvent<HTMLDivElement>) => { e.preventDefault(); setUploadState('dragging'); };
  const onDragLeave = () => { if (uploadState === 'dragging') setUploadState('idle'); };
  const onDrop      = (e: DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    const files = Array.from(e.dataTransfer.files);
    if (files.length) addFiles(files); else setUploadState('idle');
  };
  const onInputChange = (e: ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files ?? []);
    if (files.length) addFiles(files);
    e.target.value = '';
  };

  async function handleUpload() {
    const validFiles = selectedFiles.filter(f => !f.validationError).map(f => f.file);
    if (!validFiles.length) return;

    setUploadState('uploading');
    setUploadResponse(null);
    setGlobalError('');

    try {
      const res = await projectApi.upload(validFiles);
      setUploadResponse(res);
      setUploadState('done');
      setHistoryRefresh(n => n + 1);
    } catch (err: any) {
    const responseData = err?.responseData;

    if (responseData?.results) {
      setUploadResponse(responseData);
      setUploadState('done');
    } else {
      setGlobalError(err?.message ?? 'Terjadi kesalahan saat upload.');
      setUploadState('error');
    }

    setHistoryRefresh(n => n + 1);
  }
  }

  function handleReset() {
    setSelectedFiles([]);
    setUploadResponse(null);
    setGlobalError('');
    setUploadState('idle');
    if (inputRef.current) inputRef.current.value = '';
  }

  function getResultForFile(fileName: string): FileUploadResult | undefined {
    const baseName = fileName.split(/[\\/]/).pop() ?? fileName;
    return uploadResponse?.results?.find(r => {
      const resultBase = r.file_name.split(/[\\/]/).pop() ?? r.file_name;
      return resultBase === baseName;
  });
}

  return (
    <div
      className="bg-[#F9FAFB] flex flex-col items-center"
      style={{ width: '1203px', minHeight: '1024px', padding: '40px', boxSizing: 'border-box' }}
    >
      <div className="w-full max-w-4xl space-y-6">

        {/* Header */}
        <header className="mb-2 text-center">
          <h1 className="text-2xl font-bold text-gray-800">Import Project Data</h1>
          <p className="text-gray-500 mt-1 text-sm">
            Upload satu atau lebih file Excel untuk mengimport data project sekaligus
          </p>
        </header>

        <div
          onDragOver={onDragOver}
          onDragLeave={onDragLeave}
          onDrop={onDrop}
          onClick={() => !isUploading && inputRef.current?.click()}
          className={`
            border-2 border-dashed rounded-xl text-center transition-colors
            ${isUploading ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'}
            ${isDragging ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 bg-white'}
            ${selectedFiles.length > 0 ? 'py-8 px-10' : 'py-20 px-10'}
          `}
        >
          <input ref={inputRef} type="file" accept=".xlsx,.xls" multiple className="hidden" onChange={onInputChange} />

          <div className="flex justify-center mb-3">
            <div className={`w-12 h-12 rounded-full flex items-center justify-center transition-colors ${isDragging ? 'bg-blue-100' : 'bg-gray-100'}`}>
              <svg className={`w-6 h-6 transition-colors ${isDragging ? 'text-blue-500' : 'text-gray-400'}`}
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
            </div>
          </div>

          <p className="font-semibold text-gray-700 text-sm">
            {isDragging ? 'Lepaskan file di sini'
              : selectedFiles.length > 0 ? 'Klik atau drag untuk tambah file lagi'
              : 'Drag & drop file Excel di sini'}
          </p>
          <p className="text-xs text-gray-400 mt-1">
            Multiple file didukung · .xlsx / .xls · maks 5MB per file
          </p>
        </div>

        {selectedFiles.length > 0 && (
          <div className="border border-gray-200 rounded-xl bg-white overflow-hidden divide-y divide-gray-100">
            <div className="px-5 py-3 flex items-center justify-between bg-gray-50">
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                {selectedFiles.length} file dipilih
                {hasInvalidFiles && (
                  <span className="ml-2 text-red-500 font-medium">· {invalidCount} tidak valid</span>
                )}
              </span>
              {!isUploading && !isDone && (
                <button onClick={(e) => { e.stopPropagation(); handleReset(); }}
                  className="text-xs text-gray-400 hover:text-red-500 transition-colors">
                  Hapus semua
                </button>
              )}
            </div>

            {selectedFiles.map((sf) => {
              const result    = getResultForFile(sf.file.name);
              const hasResult = !!result;
              const statusCfg = hasResult
                ? STATUS_CONFIG[result.status]
                : sf.validationError ? STATUS_CONFIG['failed']
                : isUploading ? STATUS_CONFIG['processing']
                : STATUS_CONFIG['pending'];

              return (
                <div key={sf.id} className="px-5 py-3.5">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center shrink-0">
                      <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-800 truncate">{sf.file.name}</p>
                      <p className="text-xs text-gray-400 mt-0.5">
                        {formatSize(sf.file.size)}
                        {hasResult && (
                          <span className="ml-2">
                            · {result.imported} imported
                            {result.skipped > 0 && `, ${result.skipped} dilewati`}
                          </span>
                        )}
                        {sf.validationError && !hasResult && (
                          <span className="ml-2 text-red-500">{sf.validationError}</span>
                        )}
                      </p>
                    </div>
                    <div className={`flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${statusCfg.bg} ${statusCfg.text}`}>
                      {isUploading && !hasResult
                        ? <div className="w-3 h-3 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
                        : <span className={`w-1.5 h-1.5 rounded-full ${statusCfg.dot}`} />
                      }
                      {statusCfg.label}
                    </div>
                    {!isUploading && !isDone && (
                      <button onClick={() => removeFile(sf.id)}
                        className="w-6 h-6 flex items-center justify-center text-gray-300 hover:text-red-400 transition-colors shrink-0">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    )}
                  </div>
                  {hasResult && result.errors.length > 0 && (
                    <div className="mt-3 ml-11 space-y-1">
                      {result.errors.map((err, i) => (
                        <div key={i} className="flex items-start gap-2 text-xs text-gray-500">
                          <span className="mt-1 w-1.5 h-1.5 rounded-full bg-yellow-400 shrink-0" />
                          {err}
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        )}

        {hasValidFiles && !isUploading && !isDone && (
          <div className="flex gap-3">
            <button onClick={handleUpload} className="btn-primary flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              Upload {validCount} File{validCount > 1 ? 's' : ''}
            </button>
            <button onClick={handleReset} className="btn-outline">Batal</button>
          </div>
        )}

        {isUploading && (
          <div className="border border-gray-200 rounded-xl bg-white px-6 py-4 flex items-center gap-4">
            <div className="w-6 h-6 border-[3px] border-blue-600 border-t-transparent rounded-full animate-spin shrink-0" />
            <div>
              <p className="font-semibold text-gray-800 text-sm">Memproses file...</p>
              <p className="text-xs text-gray-400 mt-0.5">
                Mengupload {validCount} file secara berurutan, mohon tunggu
              </p>
            </div>
          </div>
        )}

        {isDone && uploadResponse && (
          <div className="border border-gray-200 rounded-xl bg-white px-6 py-5 space-y-4">
            <div className="flex items-center gap-2">
              <div className={`w-5 h-5 rounded-full flex items-center justify-center shrink-0 ${uploadResponse.success ? 'bg-green-100' : 'bg-red-100'}`}>
                {uploadResponse.success ? (
                  <svg className="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clipRule="evenodd" />
                  </svg>
                ) : (
                  <svg className="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                )}
              </div>
              <p className="font-semibold text-gray-800 text-sm">{uploadResponse.message}</p>
            </div>
            <div className="flex gap-8">
              <div>
                <p className="text-2xl font-bold text-green-600">
                  {uploadResponse.results?.reduce((s, r) => s + r.imported, 0) ?? 0}
                </p>
                <p className="text-xs text-gray-400 mt-0.5">Total berhasil diimport</p>
              </div>
              {(uploadResponse.results?.reduce((s, r) => s + r.skipped, 0) ?? 0) > 0 && (
                <div>
                  <p className="text-2xl font-bold text-yellow-500">
                    {uploadResponse.results?.reduce((s, r) => s + r.skipped, 0)}
                  </p>
                  <p className="text-xs text-gray-400 mt-0.5">Total dilewati</p>
                </div>
              )}
              <div>
                <p className="text-2xl font-bold text-gray-700">{uploadResponse.results?.length ?? 0}</p>
                <p className="text-xs text-gray-400 mt-0.5">File diproses</p>
              </div>
            </div>
            <button onClick={handleReset} className="btn-outline text-sm">Upload File Lain</button>
          </div>
        )}

        {uploadState === 'error' && globalError && (
          <div className="border border-gray-200 rounded-xl bg-white px-6 py-5 space-y-3">
            <div className="flex items-center gap-2">
              <div className="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg className="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </div>
              <p className="font-semibold text-gray-800 text-sm">Upload gagal</p>
            </div>
            <p className="text-sm text-gray-500">{globalError}</p>
            <button onClick={handleReset} className="btn-outline text-sm">Coba Lagi</button>
          </div>
        )}

        <div className="border border-gray-200 rounded-xl bg-white px-6 py-5">
          <p className="text-sm font-bold text-gray-700 mb-3">Format kolom yang diperlukan</p>
          <div className="grid grid-cols-2 gap-x-8 gap-y-1.5">
            {COLUMNS.map(([col, note]) => (
              <div key={col} className="flex items-baseline gap-2">
                <span className="font-mono text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded shrink-0">{col}</span>
                <span className="text-xs text-gray-400">{note}</span>
              </div>
            ))}
          </div>
        </div>

        <IngestionHistory refreshTrigger={historyRefresh} />

      </div>
    </div>
  );
}