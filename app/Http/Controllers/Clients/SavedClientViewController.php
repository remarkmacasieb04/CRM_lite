<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreSavedClientViewRequest;
use App\Models\SavedClientView;
use App\Support\ClientFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedClientViewController extends Controller
{
    public function store(StoreSavedClientViewRequest $request): RedirectResponse
    {
        $request->user()->savedClientViews()->create([
            'name' => $request->validated('name'),
            'filters' => ClientFilters::active($request->validated()),
        ]);

        return back()->with('success', 'Saved view created successfully.');
    }

    public function destroy(Request $request, SavedClientView $savedClientView): RedirectResponse
    {
        abort_unless($savedClientView->user_id === $request->user()?->id, 403);

        $savedClientView->delete();

        return back()->with('success', 'Saved view removed successfully.');
    }
}
