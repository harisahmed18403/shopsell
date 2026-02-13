<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin()) abort(403);
        
        $users = User::where('organization_id', Auth::user()->organization_id)
            ->latest()
            ->paginate(10);
            
        return view('organization.users.index', compact('users'));
    }

    public function create()
    {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('organization.users.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,admin'], // Can create admins or regular users
        ]);

        // organization_id set by trait
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    // ... edit, update, destroy ...
}
