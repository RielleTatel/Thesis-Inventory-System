<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSearchThesisRequest;
use App\Models\Department;
use App\Models\Thesis;
use App\Repositories\ThesisRepository;
use Illuminate\View\View;

/**
 * Admin read-only thesis overview: see all records across all departments,
 * including drafts. No create/edit/delete — those belong to the owning department.
 */
class ThesisController extends Controller
{
    public function __construct(private readonly ThesisRepository $theses) {}

    public function index(AdminSearchThesisRequest $request): View
    {
        return view('admin.thesis.index', [
            'theses' => $this->theses->allForAdmin($request->filters()),
            'filters' => $request->filters(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Thesis $thesis): View
    {
        $thesis->load(['department', 'authors', 'advisers', 'panelists', 'proofreaders', 'keywords']);

        return view('admin.thesis.show', ['thesis' => $thesis]);
    }
}
