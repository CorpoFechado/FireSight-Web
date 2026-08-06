<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('profile/index', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'personnel' => $user->isBfpStaff() ? [
                'rank' => $user->personnelDetails?->rank,
                'employee_number' => $user->personnelDetails?->employee_number,
                'station_assigned' => $user->personnelDetails?->station_assigned,
                'role' => $user->role,
                'status' => $user->status,
            ] : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Direct assignment (not fill()) — 'name' is a virtual attribute,
        // not a real column, so it's intentionally absent from $fillable
        // and mass-assignment would silently discard it.
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (array_key_exists('contact_number', $data)) {
            $user->contact_number = $data['contact_number'];
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->isBfpStaff() && isset($data['rank'])) {
            $user->personnelDetails()->update(['rank' => $data['rank']]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
