<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'prnumber' => 'required|string|max:255',
            'oldname' => 'required|string|max:255',
            'newname' => 'required|string|max:255'
        ];
    }
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
        return response()->json(['errors' => $validator->errors()], 422);
    }
}
