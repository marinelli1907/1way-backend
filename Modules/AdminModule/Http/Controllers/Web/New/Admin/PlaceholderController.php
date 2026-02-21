<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PlaceholderController
 *
 * Handles every sidebar route that does not yet have a real implementation.
 * Returns a consistent "Coming Soon" page under the admin layout so the
 * content area never goes blank.
 */
class PlaceholderController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request): View
    {
        // Derive a human-readable title from the URL segment
        $segment = $request->segment(3) ?? $request->segment(2) ?? 'page';
        $title   = ucwords(str_replace(['-', '_'], ' ', $segment));

        return view('adminmodule::placeholder', compact('title'));
    }
}
