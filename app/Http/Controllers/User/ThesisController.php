<?php

namespace App\Http\Controllers\User;

use App\Actions\CreateThesisAction;
use App\Actions\DeleteThesisAction;
use App\Actions\ToggleThesisStatusAction;
use App\Actions\UpdateThesisAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchThesisRequest;
use App\Http\Requests\StoreThesisRequest;
use App\Http\Requests\UpdateThesisRequest;
use App\Models\Thesis;
use App\Repositories\ThesisRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Department-role thesis management (User namespace), scoped to the logged-in
 * department's own records. Thin: validate via Form Request, call an Action,
 * return a view/redirect (coding standard #1). Ownership is enforced by
 * ThesisPolicy (FR-3.4/3.6).
 */
class ThesisController extends Controller
{
    public function __construct(private readonly ThesisRepository $theses) {}

    /**
     * Searchable list of the department's own theses.
     */
    public function index(SearchThesisRequest $request): View
    {
        $user = $request->user() ?? abort(403);
        $departmentId = (int) $user->department_id;

        return view('user.thesis.index', [
            'theses' => $this->theses->forDepartment($departmentId, $request->filters()),
            'stats' => $this->theses->statsForDepartment($departmentId),
            'filters' => $request->filters(),
            'department' => $user->department,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Thesis::class);

        return view('user.thesis.create');
    }

    public function store(StoreThesisRequest $request, CreateThesisAction $action): RedirectResponse
    {
        $this->authorize('create', Thesis::class);

        $user = $request->user() ?? abort(403);
        $department = $user->department ?? abort(403);
        $action->execute($department, $request->validated());

        return redirect()
            ->route('department.theses.index')
            ->with('success', 'Thesis added.');
    }

    public function edit(Thesis $thesis): View
    {
        $this->authorize('update', $thesis);

        $thesis->load(['authors', 'advisers', 'panelists', 'keywords']);

        return view('user.thesis.edit', ['thesis' => $thesis]);
    }

    public function update(UpdateThesisRequest $request, Thesis $thesis, UpdateThesisAction $action): RedirectResponse
    {
        $this->authorize('update', $thesis);

        $action->execute($thesis, $request->validated());

        return redirect()
            ->route('department.theses.index')
            ->with('success', 'Thesis updated.');
    }

    public function toggleStatus(Thesis $thesis, ToggleThesisStatusAction $action): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $thesis);

        $action->execute($thesis);

        if (request()->expectsJson()) {
            return response()->json(['status' => $thesis->status]);
        }

        $label = $thesis->isPublished() ? 'published' : 'saved as draft';

        return redirect()
            ->route('department.theses.index')
            ->with('success', "Thesis {$label}.");
    }

    public function destroy(Thesis $thesis, DeleteThesisAction $action): RedirectResponse
    {
        $this->authorize('delete', $thesis);

        $action->execute($thesis);

        return redirect()
            ->route('department.theses.index')
            ->with('success', 'Thesis deleted.');
    }
}
