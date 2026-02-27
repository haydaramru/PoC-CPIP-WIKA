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
            // Max 5MB, hanya .xlsx dan .xls
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes'    => 'Format file harus .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ];
    }
}