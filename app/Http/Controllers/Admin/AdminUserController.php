<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function index(): View
    {
        $items = Admin::query()->orderBy('email')->paginate(25);

        return view('admin.admins.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.admins.form', ['item' => new Admin]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $admin = Admin::query()->create($data);
        $this->auditLogger->log($request->user(), 'admin_whitelist.created', $admin);

        return redirect()->route('admin.admins.index')->with('ok', __('messages.saved'));
    }

    public function edit(Admin $admin): View
    {
        return view('admin.admins.form', ['item' => $admin]);
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $data = $this->validated($request, $admin->id);
        $admin->update($data);
        $this->auditLogger->log($request->user(), 'admin_whitelist.updated', $admin);

        return redirect()->route('admin.admins.index')->with('ok', __('messages.saved'));
    }

    public function destroy(Request $request, Admin $admin): RedirectResponse
    {
        $admin->delete();
        $this->auditLogger->log($request->user(), 'admin_whitelist.deleted', $admin);

        return redirect()->route('admin.admins.index')->with('ok', __('messages.deleted'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $admin = Admin::onlyTrashed()->findOrFail($id);
        $admin->restore();
        $this->auditLogger->log($request->user(), 'admin_whitelist.restored', $admin);

        return redirect()->route('admin.admins.index')->with('ok', __('messages.restored'));
    }

    protected function validated(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'email' => [
                'required',
                'email',
                'regex:/^[^@\s]+@uoz\.edu\.krd$/i',
                Rule::unique('admins', 'email')->ignore($exceptId),
            ],
            'english_name' => ['required', 'string', 'max:255'],
            'kurdish_name' => ['nullable', 'string', 'max:255'],
            'arabic_name' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
