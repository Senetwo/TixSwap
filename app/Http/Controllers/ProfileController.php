<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
{
    $user = $request->user();
    $tickets = $user->tickets()->with('event')->latest()->get();

    return view('profile.edit', [
        'user' => $user,
        'tickets' => $tickets,
    ]);
}

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null; // Ensure 'email_verified_at' is nullable in your migration and User model
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Force the current user's email to be verified.
     * This is a helper for development.
     */
    public function forceVerify(Request $request): RedirectResponse
    {
        // Security Improvement: Ensure this can only be run in a local/development environment.
        if (! app()->isLocal()) {
            abort(403, 'This action is only available in the local environment.');
        }

        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->markEmailAsVerified();
        }

        // Redirect to dashboard with verified flag, similar to standard verification flow.
        return redirect()->intended(route('dashboard', [], false).'?verified=1');
    }
}
