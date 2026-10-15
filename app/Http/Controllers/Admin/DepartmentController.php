<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->get();

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'actionUrl' => route('admin.departments.index'),
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $department = Department::create($request->validated());

        return response()->json([
            'department' => $department,
            'message' => 'Department created successfully!',
        ], 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return response()->json([
            'department' => $department,
            'message' => 'Department updated successfully!',
        ]);
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return response()->json([
                'message' => 'This department has employees and cannot be deleted.',
            ], 409);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully!',
        ]);
    }
}
