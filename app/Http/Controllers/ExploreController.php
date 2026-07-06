<?php

namespace App\Http\Controllers;

use App\Models\Splat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'trending');

        $splats = Splat::publik()
            ->search($q)
            ->when($sort === 'newest',
                fn ($query) => $query->latest(),
                fn ($query) => $query->orderByDesc('views')->latest())
            ->paginate(24)
            ->withQueryString();

        return view('explore', compact('splats', 'q', 'sort'));
    }
}
