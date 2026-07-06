<?php

namespace App\Http\Controllers;

use App\Models\Splat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SplatViewController extends Controller
{
    public function show(Request $request, Splat $splat): View
    {
        if (! $splat->is_public && $splat->user_id !== $request->user()?->id) {
            abort(404);
        }

        $splat->increment('views');

        return view('viewer', compact('splat'));
    }
}
