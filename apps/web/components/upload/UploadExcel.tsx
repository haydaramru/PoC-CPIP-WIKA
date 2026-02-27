'use client';

import { useState, useRef, DragEvent, ChangeEvent } from 'react';
import { projectApi } from '@/lib/api';
import type { UploadResponse } from '@/types/project';

type UploadState = 'idle' | 'dragging' | 'uploading' | 'success' | 'error';

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

export default function UploadExcel() {
  const [uploadState, setUploadState] = useState<UploadState>('idle');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [result, setResult]             = useState<UploadResponse | null>(null);
  const [errorMsg, setErrorMsg]         = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  function validateFile(file: File): string | null {
    const allowed = [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-excel',
    ];
    if (!allowed.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i))
      return 'Format file harus .xlsx atau .xls';
    if (file.size > 5 * 1024 * 1024)
      return 'Ukuran file maksimal 5MB';
    return null;
  }

  function handleFileSelect(file: File) {
    const err = validateFile(file);
    if (err) { setErrorMsg(err); setUploadState('error'); setSelectedFile(null); return; }
    setSelectedFile(file);
    setUploadState('idle');
    setResult(null);
    setErrorMsg('');
  }

  const onDragOver  = (e: DragEvent<HTMLDivElement>) => { e.preventDefault(); setUploadState('dragging'); };
  const onDragLeave = () => setUploadState('idle');
  const onDrop      = (e: DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file) handleFileSelect(file); else setUploadState('idle');
  };
  const onInputChange = (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) handleFileSelect(file);
  };

  async function handleUpload() {
    if (!selectedFile) return;
    setUploadState('uploading');
    setResult(null);
    setErrorMsg('');
    try {
      const res = await projectApi.upload(selectedFile);
      setResult(res);
      setUploadState(res.success ? 'success' : 'error');
    } catch (err: any) {
      setErrorMsg(err?.response?.data?.message ?? 'Terjadi kesalahan saat upload.');
      setUploadState('error');
    }
  }

  function handleReset() {
    setSelectedFile(null);
    setResult(null);
    setErrorMsg('');
    setUploadState('idle');
    if (inputRef.current) inputRef.current.value = '';
  }

  const isDragging = uploadState === 'dragging';

  return (
    <div 
      className="bg-[#F9FAFB] flex flex-col items-center" // Background abu muda agar card putih menonjol
      style={{
        width: '1203px',
        height: '1024px',
        padding: '40px',
        boxSizing: 'border-box',
      }}
    >
      {/* Kontainer Utama Konten (Maksimum lebar agar tidak terlalu melar) */}
      <div className="w-full max-w-4xl space-y-6">
        
        <header className="mb-8">
          <h1 className="text-2xl font-bold text-gray-800 text-center">Import Project Data</h1>
          <p className="text-gray-500 text-center mt-1">Gunakan file Excel untuk mengupload banyak data sekaligus</p>
        </header>

      {/* ── Drop Zone ─────────────────────────────────────── */}
      <div
        onDragOver={onDragOver}
        onDragLeave={onDragLeave}
        onDrop={onDrop}
        onClick={() => inputRef.current?.click()}
        className={`
          cursor-pointer border-2 border-dashed rounded-xl text-center py-32 px-16 transition-colors
          ${isDragging
            ? 'border-blue-400 bg-blue-50'
            : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 bg-white'}
        `}
      >
        <input ref={inputRef} type="file" accept=".xlsx,.xls" className="hidden" onChange={onInputChange} />

        {/* Icon */}
        <div className="flex justify-center mb-4">
          <div className={`w-14 h-14 rounded-full flex items-center justify-center transition-colors ${isDragging ? 'bg-blue-100' : 'bg-gray-100'}`}>
            <svg className={`w-7 h-7 transition-colors ${isDragging ? 'text-blue-500' : 'text-gray-400'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>

        {selectedFile ? (
          <>
            <p className="font-semibold text-gray-800">{selectedFile.name}</p>
            <p className="text-sm text-gray-400 mt-1">
              {(selectedFile.size / 1024).toFixed(1)} KB · Klik untuk ganti file
            </p>
          </>
        ) : (
          <>
            <p className="font-semibold text-gray-700">
              {isDragging ? 'Lepaskan file di sini' : 'Drag & drop file Excel'}
            </p>
            <p className="text-sm text-gray-400 mt-1">
              atau klik untuk browse · .xlsx / .xls · maks 5MB
            </p>
          </>
        )}
      </div>

      {/* ── Format reminder ───────────────────────────────── */}
      <div className="border border-gray-200 rounded-xl bg-white px-6 py-5">
        <p className="text-sm font-bold text-gray-700 mb-3">Format kolom yang diperlukan</p>
        <div className="grid grid-cols-2 gap-x-8 gap-y-1.5">
          {COLUMNS.map(([col, note]) => (
            <div key={col} className="flex items-baseline gap-2">
              <span className="font-mono text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded shrink-0">
                {col}
              </span>
              <span className="text-xs text-gray-400">{note}</span>
            </div>
          ))}
        </div>
      </div>

      {/* ── Action buttons ────────────────────────────────── */}
      {selectedFile && uploadState !== 'uploading' && (
        <div className="flex gap-3">
          <button onClick={handleUpload} className="btn-primary flex items-center gap-2">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload Sekarang
          </button>
          <button onClick={handleReset} className="btn-outline">Batal</button>
        </div>
      )}

      {/* ── Uploading ─────────────────────────────────────── */}
      {uploadState === 'uploading' && (
        <div className="border border-gray-200 rounded-xl bg-white px-6 py-5 flex items-center gap-4">
          <div className="w-7 h-7 border-[3px] border-blue-600 border-t-transparent rounded-full animate-spin shrink-0" />
          <div>
            <p className="font-semibold text-gray-800 text-sm">Mengupload & memproses data...</p>
            <p className="text-xs text-gray-400 mt-0.5">KPI akan dihitung otomatis</p>
          </div>
        </div>
      )}

      {/* ── Success ───────────────────────────────────────── */}
      {uploadState === 'success' && result && (
        <div className="border border-gray-200 rounded-xl bg-white px-6 py-5 space-y-4">
          {/* Header */}
          <div className="flex items-center gap-2">
            <div className="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center shrink-0">
              <svg className="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd"
                  d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                  clipRule="evenodd" />
              </svg>
            </div>
            <p className="font-semibold text-gray-800 text-sm">{result.message}</p>
          </div>

          {/* Stats */}
          <div className="flex gap-6">
            <div>
              <p className="text-2xl font-bold text-green-600">{result.imported}</p>
              <p className="text-xs text-gray-400 mt-0.5">Berhasil diimport</p>
            </div>
            {result.skipped > 0 && (
              <div>
                <p className="text-2xl font-bold text-yellow-500">{result.skipped}</p>
                <p className="text-xs text-gray-400 mt-0.5">Dilewati</p>
              </div>
            )}
          </div>

          {/* Row errors */}
          {result.errors.length > 0 && (
            <>
              <div className="border-t border-gray-100" />
              <div>
                <p className="text-sm font-semibold text-gray-700 mb-2">Detail error baris</p>
                <ul className="space-y-1">
                  {result.errors.map((err, i) => (
                    <li key={i} className="flex items-start gap-2 text-sm text-gray-600">
                      <span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-yellow-400 shrink-0" />
                      {err}
                    </li>
                  ))}
                </ul>
              </div>
            </>
          )}

          <button onClick={handleReset} className="btn-outline text-sm">Upload File Lain</button>
        </div>
      )}

      {/* ── Error ─────────────────────────────────────────── */}
      {uploadState === 'error' && errorMsg && (
        <div className="border border-gray-200 rounded-xl bg-white px-6 py-5 space-y-3">
          <div className="flex items-center gap-2">
            <div className="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center shrink-0">
              <svg className="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clipRule="evenodd" />
              </svg>
            </div>
            <p className="font-semibold text-gray-800 text-sm">Upload gagal</p>
          </div>
          <p className="text-sm text-gray-500">{errorMsg}</p>
          <button onClick={handleReset} className="btn-outline text-sm">Coba Lagi</button>
        </div>
      )}

    </div>
    </div>
  );

}