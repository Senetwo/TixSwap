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

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account from here.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Admin accounts cannot be deleted from this screen.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
