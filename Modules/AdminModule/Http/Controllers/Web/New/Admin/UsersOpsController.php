<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Real Users ops pages: Roles & Permissions, Reviews & Ratings.
 */
class UsersOpsController extends Controller
{
    public function roles(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Roles', 'value' => 0, 'icon' => 'bi-person-badge'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.users.roles', compact('items', 'kpis'));
    }

    public function reviews(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Total', 'value' => 0, 'icon' => 'bi-star'], ['label' => 'Avg rating', 'value' => '—', 'icon' => 'bi-graph-up'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.users.reviews', compact('items', 'kpis'));
    }
}
