<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->paginate(15);

        return view('admin.users.index', ['users' => $users]);
    }

    /**
     * Switch a non-admin between buyer, seller and gate staff.
     * (Admins can't be created from the UI.)
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['role' => 'required|in:buyer,seller,scanner']);

        if ($user->isAdmin()) {
            return back()->with('error', 'Admin roles can\'t be changed here.');
        }

        $from = $user->role;
        $user->forceFill(['role' => $validated['role']])->save();
        \App\Models\AuditLog::record('user.role_changed', $user, ['from' => $from, 'to' => $validated['role']]);

        return back()->with('success', $user->name.' is now '.($validated['role'] === 'scanner' ? 'gate staff' : 'a '.$validated['role']).'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account from here.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Admin accounts cannot be deleted from this screen.');
        }

        \App\Models\AuditLog::record('user.deleted', $user, ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]);
        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
