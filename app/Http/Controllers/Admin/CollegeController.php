<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollegeController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function index(): View
    {
        $items = College::query()->orderBy('name_en')->paginate(25);

        return view('admin.colleges.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.colleges.form', ['item' => new College]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $college = College::query()->create($data);
        $this->auditLogger->log($request->user(), 'college.created', $college);

        return redirect()->route('admin.colleges.index')->with('ok', __('messages.saved'));
    }

    public function edit(College $college): View
    {
        return view('admin.colleges.form', ['item' => $college]);
    }

    public function update(Request $request, College $college): RedirectResponse
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $college->update($data);
        $this->auditLogger->log($request->user(), 'college.updated', $college);

        return redirect()->route('admin.colleges.index')->with('ok', __('messages.saved'));
    }

    public function destroy(Request $request, College $college): RedirectResponse
    {
        $college->delete();
        $this->auditLogger->log($request->user(), 'college.deleted', $college);

        return redirect()->route('admin.colleges.index')->with('ok', __('messages.deleted'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $college = College::onlyTrashed()->findOrFail($id);
        $college->restore();
        $this->auditLogger->log($request->user(), 'college.restored', $college);

        return redirect()->route('admin.colleges.index')->with('ok', __('messages.restored'));
    }
}
