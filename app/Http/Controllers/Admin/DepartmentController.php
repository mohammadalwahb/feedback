<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function index(): View
    {
        $items = Department::query()->with('college')->orderBy('name_en')->paginate(25);

        return view('admin.departments.index', compact('items'));
    }

    public function create(): View
    {
        $colleges = College::query()->orderBy('name_en')->get();

        return view('admin.departments.form', ['item' => new Department, 'colleges' => $colleges]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $dept = Department::query()->create($data);
        $this->auditLogger->log($request->user(), 'department.created', $dept);

        return redirect()->route('admin.departments.index')->with('ok', __('messages.saved'));
    }

    public function edit(Department $department): View
    {
        $colleges = College::query()->orderBy('name_en')->get();

        return view('admin.departments.form', ['item' => $department, 'colleges' => $colleges]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $department->update($data);
        $this->auditLogger->log($request->user(), 'department.updated', $department);

        return redirect()->route('admin.departments.index')->with('ok', __('messages.saved'));
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $department->delete();
        $this->auditLogger->log($request->user(), 'department.deleted', $department);

        return redirect()->route('admin.departments.index')->with('ok', __('messages.deleted'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();
        $this->auditLogger->log($request->user(), 'department.restored', $department);

        return redirect()->route('admin.departments.index')->with('ok', __('messages.restored'));
    }
}
