<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ReportRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportRepository $reports
    ) {}

    public function __invoke(): View
    {
        $participation = $this->reports->participationRatio();

        return view('admin.dashboard', compact('participation'));
    }
}
