<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchThesisRequest;
use App\Models\Thesis;
use App\Repositories\ThesisRepository;
use Illuminate\View\View;

/**
 * Public, unauthenticated viewer (SRS viewer class): browse/search + detail.
 * Read-only — no create/update/delete here.
 */
class PublicThesisController extends Controller
{
    public function __construct(private readonly ThesisRepository $theses) {}

    /**
     * Browse + search across every department's records (FR-6.x).
     */
    public function index(SearchThesisRequest $request): View
    {
        $filters = $request->filters();

        return view('public.thesis.index', [
            'theses' => $this->theses->search($filters),
            'filters' => $filters,
            'programs' => $this->theses->programs(),
            'keywords' => $this->theses->keywords(),
            'years' => $this->theses->years(),
        ]);
    }

    /**
     * Public detail page for a single thesis.
     */
    public function show(Thesis $thesis): View
    {
        $thesis->load(['department', 'authors', 'advisers', 'panelists', 'keywords']);

        return view('public.thesis.show', ['thesis' => $thesis]);
    }
}
