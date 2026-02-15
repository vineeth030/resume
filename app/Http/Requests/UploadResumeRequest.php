<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadResumeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'resume.required' => 'Please upload a resume file.',
            'resume.file' => 'The uploaded file is invalid.',
            'resume.mimes' => 'The resume must be a PDF file.',
            'resume.max' => 'The resume file size must not exceed 10MB.',
        ];
    }
}
