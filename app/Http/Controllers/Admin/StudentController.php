<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\Semester;
use App\Http\Requests\Admin\DeleteAllStudentsRequest;
use App\Models\Student;
use App\Services\AdminRosterService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger,
        protected AdminRosterService $roster,
    ) {}

    public function index(): View
    {
        $items = Student::query()->with(['college', 'department', 'semester'])->orderBy('email')->paginate(25);

        return view('admin.students.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.students.form', [
            'item' => new Student,
            'colleges' => College::query()->orderBy('name_en')->get(),
            'departments' => Department::query()->orderBy('name_en')->get(),
            'semesters' => Semester::query()->orderBy('name_en')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $student = Student::query()->create($data);
        $this->auditLogger->log($request->user(), 'student.created', $student);

        return redirect()->route('admin.students.index')->with('ok', __('messages.saved'));
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', [
            'item' => $student,
            'colleges' => College::query()->orderBy('name_en')->get(),
            'departments' => Department::query()->orderBy('name_en')->get(),
            'semesters' => Semester::query()->orderBy('name_en')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $this->validated($request, $student->id);
        $student->update($data);
        $this->auditLogger->log($request->user(), 'student.updated', $student);

        return redirect()->route('admin.students.index')->with('ok', __('messages.saved'));
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $student->delete();
        $this->auditLogger->log($request->user(), 'student.deleted', $student);

        return redirect()->route('admin.students.index')->with('ok', __('messages.deleted'));
    }

    public function destroyAll(DeleteAllStudentsRequest $request): RedirectResponse
    {
        $count = $this->roster->softDeleteAllStudents();
        $this->auditLogger->log($request->user(), 'students.delete_all', null, ['count' => $count]);

        return redirect()->route('admin.students.index')->with('ok', __('admin.delete_all_students_done', ['count' => $count]));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();
        $this->auditLogger->log($request->user(), 'student.restored', $student);

        return redirect()->route('admin.students.index')->with('ok', __('messages.restored'));
    }

    protected function validated(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'email' => [
                'required',
                'email',
                'regex:/^[^@\s]+@stud\.uoz\.edu\.krd$/i',
                Rule::unique('students', 'email')->ignore($exceptId),
            ],
            'english_name' => ['required', 'string', 'max:255'],
            'kurdish_name' => ['nullable', 'string', 'max:255'],
            'arabic_name' => ['nullable', 'string', 'max:255'],
            'college_id' => ['required', 'exists:colleges,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
        ]);
    }
}
