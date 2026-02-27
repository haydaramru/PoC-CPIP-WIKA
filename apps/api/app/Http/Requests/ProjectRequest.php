<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            'project_code'     => 'required|string|max:20|unique:projects,project_code,' . $projectId,
            'project_name'     => 'required|string|max:255',
            'division'         => 'required|in:Infrastructure,Building',
            'owner'            => 'nullable|string|max:100',
            'contract_value'   => 'required|numeric|min:0',
            'planned_cost'     => 'required|numeric|min:0',
            'actual_cost'      => 'required|numeric|min:0',
            'planned_duration' => 'required|integer|min:1',
            'actual_duration'  => 'required|integer|min:1',
            'progress_pct'     => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'division.in' => 'Division harus Infrastructure atau Building.',
        ];
    }
}