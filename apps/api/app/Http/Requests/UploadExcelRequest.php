<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files'   => 'required|array|min:1|max:10',
            'files.*' => 'required|file|mimes:xlsx,xls|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'files.required'   => 'Minimal satu file Excel harus diupload.',
            'files.array'      => 'Format upload tidak valid.',
            'files.max'        => 'Maksimal 10 file sekaligus.',
            'files.*.required' => 'File tidak boleh kosong.',
            'files.*.mimes'    => 'Format file harus .xlsx atau .xls.',
            'files.*.max'      => 'Ukuran file maksimal 5MB per file.',
        ];
    }
}