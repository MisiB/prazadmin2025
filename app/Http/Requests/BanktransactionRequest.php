<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BanktransactionRequest extends FormRequest
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
            'authcode'=>['required'],
            'description'=>['required'],
            'trans_date'=>['required'],
            'referencenumber'=>['required'],
            'source_reference'=>['required'],
            'statement_reference'=>['required'],
            'amount'=>['required'],
            'accountnumber'=>['required'],
            'currency'=>['required']
        ];
    }
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
        return response()->json(['errors' => $validator->errors()], 422);
    }
}
