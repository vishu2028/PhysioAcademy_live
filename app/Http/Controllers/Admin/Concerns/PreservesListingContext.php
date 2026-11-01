<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait PreservesListingContext
{
    protected function listingFilters(Request $request): array
    {
        $filters = $request->input('_listing', []);

        return is_array($filters) ? $filters : [];
    }

    protected function redirectToListing(Request $request, string $route): RedirectResponse
    {
        return redirect()->route($route, $this->listingFilters($request));
    }
}
