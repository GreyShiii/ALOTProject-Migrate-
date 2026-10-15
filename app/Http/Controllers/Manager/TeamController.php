<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    /** Display the manager's team. */
    public function index(): Response
    {
        $manager = Auth::user()->employee;

        $employees = $manager->subordinates()
            ->with([
                'user:id,first_name,last_name,email,status',
                'department:id,name',
            ])
            ->get(['id', 'user_id', 'department_id', 'position', 'hire_date'])
            ->map(fn ($employee) => [
                'id' => $employee->id,
                'position' => $employee->position,
                'hire_date' => $employee->hire_date,
                'user' => $employee->user,
                'department' => $employee->department,
            ])
            ->values();

        return Inertia::render('Manager/Team/Index', [
            'employees' => $employees,
        ]);
    }
}
