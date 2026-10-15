<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateEmployeeRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:employee,manager,admin',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => [
                'nullable',
                Rule::exists('employees', 'id')
                    ->whereIn('user_id', User::role('manager')->select('id'))
                    ->where('id', '!=', $employee->id),
            ],
            'position' => 'required|string|max:255',
            'hire_date' => 'nullable|date',
        ];
    }
}
