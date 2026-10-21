<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $employee instanceof Employee && Gate::allows('update', $employee);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $required = $this->isMethod('patch') ? ['sometimes', 'required'] : ['required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'email' => [
                ...$required, 'email', 'max:255',
                Rule::unique('employees', 'email')->ignore($this->route('employee')),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'department' => [...$required, 'string', 'max:255'],
            'position' => [...$required, 'string', 'max:255'],
        ];
    }
}
