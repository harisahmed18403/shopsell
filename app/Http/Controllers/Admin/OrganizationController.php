<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);
        $organizations = Organization::latest()->paginate(10);
        return view('admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'details' => 'nullable|string',
            'subscription_status' => 'required|string',
        ]);

        Organization::create($validated);

        return redirect()->route('organizations.index')->with('success', 'Organization created.');
    }

    // ... other methods (show, edit, update, destroy) ...
    // Skipping for brevity as pattern is established.
}
