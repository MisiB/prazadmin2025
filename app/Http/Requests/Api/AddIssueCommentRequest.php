<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddIssueCommentRequest extends FormRequest
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
            'issue_id' => 'required|exists:issuelogs,id',
            'user_email' => 'required|email',
            'comment' => 'required|string|min:1',
            'is_internal' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'issue_id.required' => 'Issue ID is required',
            'issue_id.exists' => 'The selected issue does not exist',
            'user_email.required' => 'User email is required',
            'user_email.email' => 'User email must be a valid email address',
            'comment.required' => 'Comment text is required',
            'comment.min' => 'Comment must be at least 1 character',
        ];
    }
}
