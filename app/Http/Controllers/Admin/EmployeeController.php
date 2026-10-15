<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with([
            'user.roles',
            'department',
            'manager.user',
        ])->get();

        $departments = Department::all();

        $managers = Employee::with('user')
            ->whereHas('user', function ($query) {
                $query->role('manager');
            })
            ->get();

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees->map(fn ($employee) => $this->employeePayload($employee)),
            'departments' => $departments,
            'managers' => $managers,
            'actionUrl' => route('admin.employees.index'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $employee = DB::transaction(function () use ($data) {

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            $user->assignRole($data['role']);

            return Employee::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'manager_id' => $data['manager_id'] ?? null,
                'position' => $data['position'],
                'hire_date' => $data['hire_date'] ?? null,
            ]);
        });

        $employee->load([
            'user',
            'department',
            'manager.user',
        ]);

        return response()->json([
            'message' => 'Employee created successfully.',
            'employee' => $this->employeePayload($employee),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'user',
            'department',
            'manager.user',
        ]);

        return response()->json([
            'employee' => $this->employeePayload($employee),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee
    ) {
        $data = $request->validated();

        $wasManager = $employee->user->hasRole('manager');
        $willBeManager = $data['role'] === 'manager';

        DB::transaction(function () use (
            $data,
            $employee,
            $wasManager,
            $willBeManager
        ) {

            $employee->user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
            ]);
            $employee->user->syncRoles($data['role']);

            if (!empty($data['password'])) {
                $employee->user->update([
                    'password' => $data['password'],
                ]);
            }

            /*
             * If this employee used to be a Manager
             * and is no longer a Manager, remove them
             * from all subordinates.
             */
            if ($wasManager && !$willBeManager) {
                Employee::where('manager_id', $employee->id)
                    ->update([
                        'manager_id' => null,
                    ]);
            }

            $employee->update([
                'department_id' => $data['department_id'],
                'manager_id' => $data['manager_id'] ?? null,
                'position' => $data['position'],
                'hire_date' => $data['hire_date'] ?? null,
            ]);
        });

        $employee->load([
            'user',
            'department',
            'manager.user',
        ]);

        return response()->json([
            'message' => 'Employee updated successfully.',
            'employee' => $this->employeePayload($employee),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {

            /*
             * Remove this employee as the manager
             * of any subordinates before deleting.
             */
            Employee::where('manager_id', $employee->id)
                ->update([
                    'manager_id' => null,
                ]);

            $user = $employee->user;

            $employee->delete();

            if ($user) {
                $user->delete();
            }
        });

        return response()->json([
            'message' => 'Employee deleted successfully.',
            'employee_id' => $employee->id,
        ]);
    }

    private function employeePayload(Employee $employee): array
    {
        $payload = $employee->toArray();
        // Keep calendar dates stable in both page props and JSON form responses.
        $payload['hire_date'] = $employee->hire_date?->format('Y-m-d');

        if ($employee->user) {
            $payload['user']['role'] = $employee->user->primaryRoleName();
        }

        return $payload;
    }
}
