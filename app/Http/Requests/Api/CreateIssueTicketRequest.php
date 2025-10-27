<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateIssueTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Sanctum middleware
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'regnumber' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'attachments' => 'nullable|array',
            'attachments.*.name' => 'required_with:attachments|string|max:255',
            'attachments.*.data' => 'required_with:attachments|string',
            'attachments.*.mime_type' => 'required_with:attachments|string|max:100',
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'issuegroup_id.required' => 'Issue group is required',
            'issuegroup_id.exists' => 'Invalid issue group',
            'issuetype_id.required' => 'Issue type is required',
            'issuetype_id.exists' => 'Invalid issue type',
            'title.required' => 'Title is required',
            'description.required' => 'Description is required',
            'priority.required' => 'Priority is required',
            'priority.in' => 'Priority must be Low, Medium, or High',
            'email.email' => 'Valid email address is required',
            'attachments.*.name.required_with' => 'Attachment name is required',
            'attachments.*.data.required_with' => 'Attachment data is required',
            'attachments.*.mime_type.required_with' => 'Attachment MIME type is required',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }
}
