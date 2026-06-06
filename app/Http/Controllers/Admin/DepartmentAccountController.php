<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateDepartmentAccountAction;
use App\Actions\DeleteDepartmentAccountAction;
use App\Actions\ToggleDepartmentAccountStatusAction;
use App\Actions\UpdateDepartmentAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteDepartmentAccountRequest;
use App\Http\Requests\StoreDepartmentAccountRequest;
use App\Http\Requests\UpdateDepartmentAccountRequest;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administrator: department account management (FR-2.x). Thin — validates via
 * Form Requests, delegates to Actions. Authorization is the role:administrator
 * route middleware.
 */
class DepartmentAccountController extends Controller
{
    /**
     * List department accounts (department + its login + record count).
     */
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        $query = Department::query()->with('users')->withCount('theses');

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhereHas('users', fn (Builder $u) => $u->where('email', 'like', $like));
            });
        }

        $accounts = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.departments.index', [
            'accounts' => $accounts,
            'search' => $term,
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(StoreDepartmentAccountRequest $request, CreateDepartmentAccountAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', 'Department account created.');
    }

    public function edit(Department $account): View
    {
        $account->load('users');

        return view('admin.departments.edit', ['account' => $account]);
    }

    public function update(UpdateDepartmentAccountRequest $request, Department $account, UpdateDepartmentAccountAction $action): RedirectResponse
    {
        $action->execute($account, $request->validated());

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', 'Department account updated.');
    }

    public function toggle(Department $account, ToggleDepartmentAccountStatusAction $action): RedirectResponse
    {
        $active = $action->execute($account);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', $active ? 'Account activated.' : 'Account deactivated.');
    }

    public function destroy(DeleteDepartmentAccountRequest $request, Department $account, DeleteDepartmentAccountAction $action): RedirectResponse
    {
        $mode = (string) $request->validated()['mode'];
        $action->execute($account, $mode);

        return redirect()
            ->route('admin.accounts.index')
            ->with('status', $mode === DeleteDepartmentAccountAction::MODE_DELETE
                ? 'Account and its records deleted.'
                : 'Account deleted; records kept in the catalog.');
    }
}
