<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteDepartmentResponsesRequest;
use App\Http\Requests\Admin\DeleteDepartmentStudentsRequest;
use App\Models\College;
use App\Models\Department;
use App\Services\AuditLogger;
use App\Services\DepartmentAdminService;
use App\Services\DirectoryExcelExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepartmentController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger,
        protected DirectoryExcelExportService $directoryExports,
        protected DepartmentAdminService $departmentAdmin,
    ) {}

    public function index(): View
    {
        $items = Department::query()
            ->with('college')
            ->withCount('students')
            ->orderBy('name_en')
            ->get();

        return view('admin.departments.index', compact('items'));
    }

    public function exportExcel(): StreamedResponse
    {
        return $this->directoryExports->streamDepartmentsWorkbook();
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

    public function destroyResponses(DeleteDepartmentResponsesRequest $request, Department $department): RedirectResponse
    {
        $deleted = $this->departmentAdmin->deleteFeedbackForDepartment($department, $request->user());

        return redirect()->route('admin.departments.index')
            ->with('ok', __('admin.delete_department_responses_done', ['count' => $deleted]));
    }

    public function destroyStudents(DeleteDepartmentStudentsRequest $request, Department $department): RedirectResponse
    {
        $count = $this->departmentAdmin->permanentlyDeleteStudentsForDepartment($department, $request->user());

        return redirect()->route('admin.departments.index')
            ->with('ok', __('admin.delete_department_students_done', ['count' => $count]));
    }
}
