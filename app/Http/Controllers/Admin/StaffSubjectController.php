<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\Semester;
use App\Http\Requests\Admin\DeleteAllStaffSubjectsRequest;
use App\Models\StaffSubject;
use App\Services\AdminRosterService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffSubjectController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger,
        protected AdminRosterService $roster,
    ) {}

    public function index(): View
    {
        $items = StaffSubject::query()
            ->with(['college', 'department', 'semester'])
            ->orderBy('instructor_name')
            ->paginate(25);

        return view('admin.staff.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.staff.form', [
            'item' => new StaffSubject,
            'colleges' => College::query()->orderBy('name_en')->get(),
            'departments' => Department::query()->orderBy('name_en')->get(),
            'semesters' => Semester::query()->orderBy('name_en')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $staff = StaffSubject::query()->create($data);
        $this->auditLogger->log($request->user(), 'staff_subject.created', $staff);

        return redirect()->route('admin.staff.index')->with('ok', __('messages.saved'));
    }

    public function edit(StaffSubject $staff): View
    {
        return view('admin.staff.form', [
            'item' => $staff,
            'colleges' => College::query()->orderBy('name_en')->get(),
            'departments' => Department::query()->orderBy('name_en')->get(),
            'semesters' => Semester::query()->orderBy('name_en')->get(),
        ]);
    }

    public function update(Request $request, StaffSubject $staff): RedirectResponse
    {
        $data = $this->validated($request, $staff->id);
        $staff->update($data);
        $this->auditLogger->log($request->user(), 'staff_subject.updated', $staff);

        return redirect()->route('admin.staff.index')->with('ok', __('messages.saved'));
    }

    public function destroy(Request $request, StaffSubject $staff): RedirectResponse
    {
        $staff->delete();
        $this->auditLogger->log($request->user(), 'staff_subject.deleted', $staff);

        return redirect()->route('admin.staff.index')->with('ok', __('messages.deleted'));
    }

    public function destroyAll(DeleteAllStaffSubjectsRequest $request): RedirectResponse
    {
        $count = $this->roster->softDeleteAllStaffSubjects();
        $this->auditLogger->log($request->user(), 'staff_subjects.delete_all', null, ['count' => $count]);

        return redirect()->route('admin.staff.index')->with('ok', __('admin.delete_all_staff_done', ['count' => $count]));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $staff = StaffSubject::onlyTrashed()->findOrFail($id);
        $staff->restore();
        $this->auditLogger->log($request->user(), 'staff_subject.restored', $staff);

        return redirect()->route('admin.staff.index')->with('ok', __('messages.restored'));
    }

    protected function validated(Request $request, ?int $exceptId = null): array
    {
        $unique = Rule::unique('staff_subjects', 'staff_employee_id')
            ->where(fn ($q) => $q
                ->where('subject_name', $request->input('subject_name'))
                ->where('college_id', $request->input('college_id'))
                ->where('department_id', $request->input('department_id'))
                ->where('semester_id', $request->input('semester_id')));

        if ($exceptId) {
            $unique->ignore($exceptId);
        }

        return $request->validate([
            'staff_employee_id' => ['required', 'string', 'max:64', $unique],
            'instructor_name' => ['required', 'string', 'max:255'],
            'subject_name' => ['required', 'string', 'max:255'],
            'college_id' => ['required', 'exists:colleges,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
        ]);
    }
}
