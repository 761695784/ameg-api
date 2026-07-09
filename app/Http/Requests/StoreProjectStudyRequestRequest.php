<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectStudyRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'establishment_type' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'estimated_budget' => ['nullable', 'string', 'max:255'],
            'desired_deadline' => ['nullable', 'string', 'max:255'],
            // documents joints (facultatif, plusieurs fichiers possibles)
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'], // 10 Mo max
        ];
    }
}
