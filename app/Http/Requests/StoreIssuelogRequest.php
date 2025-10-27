<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssuelogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'issuegroup_id' => 'required|exists:issuegroups,id',
            'issuetype_id' => 'required|exists:issuetypes,id',
            'regnumber' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'issuegroup_id.required' => 'Please select an issue group',
            'issuegroup_id.exists' => 'The selected issue group is invalid',
            'issuetype_id.required' => 'Please select an issue type',
            'issuetype_id.exists' => 'The selected issue type is invalid',
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'title.required' => 'Issue title is required',
            'description.required' => 'Issue description is required',
            'priority.required' => 'Please select a priority level',
            'priority.in' => 'Priority must be Low, Medium, or High',
        ];
    }
}
