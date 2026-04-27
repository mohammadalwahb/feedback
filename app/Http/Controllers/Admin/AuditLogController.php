<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(): View
    {
        $items = AdminAuditLog::query()->with('user')->orderByDesc('id')->paginate(40);

        return view('admin.audit.index', compact('items'));
    }
}
