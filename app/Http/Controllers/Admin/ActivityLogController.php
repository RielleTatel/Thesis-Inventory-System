<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ActivityLogRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administrator-only audit trail viewer (FR-7.4). Thin: reads filters, delegates
 * the query to ActivityLogRepository. Authorization is the role:administrator
 * route middleware.
 */
class ActivityLogController extends Controller
{
    public function __construct(private readonly ActivityLogRepository $log) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'action', 'from', 'to']);

        return view('admin.activity-log.index', [
            'activities' => $this->log->filter($filters),
            'filters' => $filters,
            'actionTypes' => $this->log->actionTypes(),
        ]);
    }
}
