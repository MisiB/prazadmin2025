<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimBanktransactionRequest extends FormRequest
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
            'regnumber' => 'required|string',
            'SourceReference' => 'required|string',
            'token' => 'required|string',
        ];
    }
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
        return response()->json(['errors' => $validator->errors()], 422);
    }
}
