<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function index(): View
    {
        $items = Semester::query()->orderBy('name_en')->paginate(25);

        return view('admin.semesters.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.semesters.form', ['item' => new Semester]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $semester = Semester::query()->create($data);
        $this->auditLogger->log($request->user(), 'semester.created', $semester);

        return redirect()->route('admin.semesters.index')->with('ok', __('messages.saved'));
    }

    public function edit(Semester $semester): View
    {
        return view('admin.semesters.form', ['item' => $semester]);
    }

    public function update(Request $request, Semester $semester): RedirectResponse
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $semester->update($data);
        $this->auditLogger->log($request->user(), 'semester.updated', $semester);

        return redirect()->route('admin.semesters.index')->with('ok', __('messages.saved'));
    }

    public function destroy(Request $request, Semester $semester): RedirectResponse
    {
        $semester->delete();
        $this->auditLogger->log($request->user(), 'semester.deleted', $semester);

        return redirect()->route('admin.semesters.index')->with('ok', __('messages.deleted'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $semester = Semester::onlyTrashed()->findOrFail($id);
        $semester->restore();
        $this->auditLogger->log($request->user(), 'semester.restored', $semester);

        return redirect()->route('admin.semesters.index')->with('ok', __('messages.restored'));
    }
}
